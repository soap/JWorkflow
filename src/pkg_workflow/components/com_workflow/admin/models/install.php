<?php
defined('_JEXEC') or die;

class WorkflowModelInstall extends JModelLegacy
{
	protected $_table = null;
	protected $_context = 'com_workflow.install';
	
	protected function populateState()
	{
		$app = JFactory::getApplication('administrator');
		
		$this->setState('message', $app->getUserState('com_workflow.message'));
		$this->setState('extension_message', $app->getUserState('com_workflow.plugin_message'));
		$app->setUserState('com_workflow.message', '');
		$app->setUserState('com_workflow.plugin_message', '');
				
		// Recall the 'Install from Directory' path.
		$path = $app->getUserStateFromRequest($this->_context . '.install_directory', 'install_directory', $app->get('tmp_path'));
		$this->setState('install.directory', $path);
		
		parent::populateState();		
	}
	
	public function install()
	{
		$this->setState('action', 'install');
		
		// Set FTP credentials, if given.
		JClientHelper::setCredentialsFromRequest('ftp');
		$app = JFactory::getApplication();
		
		// Load installer plugins for assistance if required:
		JPluginHelper::importPlugin('installer');
		$dispatcher = JEventDispatcher::getInstance();
		
		$package = null;

		// This event allows an input pre-treatment, a custom pre-packing or custom installation
		// (e.g. from a JSON description)
		$results = $dispatcher->trigger('onInstallerBeforeInstallation', array($this, &$package));
		
		if (in_array(true, $results, true))
		{
			return true;
		}
		elseif (in_array(false, $results, true))
		{
			return false;
		}
		
		$installType = $app->input->getWord('installtype');

		if ($package === null)
		{
			switch ($installType)
			{
				case 'folder':
					// Remember the 'Install from Directory' path.
					$app->getUserStateFromRequest($this->_context . '.install_directory', 'install_directory');
					$package = $this->_getPackageFromFolder();
					break;
		
				case 'upload':
					$package = $this->_getPackageFromUpload();
					break;
		
				case 'url':
					$package = $this->_getPackageFromUrl();
					break;
		
				default:
					$app->setUserState('com_workflow.message', JText::_('COM_WORKFLOW_NO_INSTALL_TYPE_FOUND'));
		
					return false;
					break;
			}
		}
		// This event allows a custom installation of the package or a customization of the package:
		$results = $dispatcher->trigger('onInstallerBeforeInstaller', array($this, &$package));
		
		if (in_array(true, $results, true))
		{
			return true;
		}
		elseif (in_array(false, $results, true))
		{
			if (in_array($installType, array('upload', 'url')))
			{
				JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
			}
		
			return false;
		}
		
		// Was the package unpacked?
		if (!$package || !$package['type'])
		{
			if (in_array($installType, array('upload', 'url')))
			{
				JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
			}
		
			$app->setUserState('com_workflow.message', JText::_('COM_WORKFLOW_UNABLE_TO_FIND_INSTALL_PACKAGE'));
			return false;
		}
		
		// Get an installer instance
		$installer = JInstaller::getInstance();
		
		$adapter = new JInstallerAdapterTrigger($installer, $this->getDbo());
		$installer->setAdapter('trigger', $adapter);
		
		// Install the package
		if (!$installer->install($package['dir']))
		{
			// There was an error installing the package
			$msg = JText::sprintf('COM_WORKFLOW_INSTALL_ERROR', JText::_('COM_WORKFLOW_TYPE_TYPE_' . strtoupper($package['type'])));
			$result = false;
		}
		else
		{
			// Package installed sucessfully
			$msg = JText::sprintf('COM_WORKFLOW_INSTALL_SUCCESS', JText::_('COM_WORKFLOW_TYPE_TYPE_' . strtoupper($package['type'])));
			$result = true;
		}
		
		// This event allows a custom a post-flight:
		$dispatcher->trigger('onInstallerAfterInstaller', array($this, &$package, $installer, &$result, &$msg));
		
		// Set some model state values
		$app	= JFactory::getApplication();
		$app->enqueueMessage($msg);
		$this->setState('name', $installer->get('name'));
		$this->setState('result', $result);
		$app->setUserState('com_workflow.message', $installer->message);
		$app->setUserState('com_workflow.extension_message', $installer->get('plugin_message'));
		$app->setUserState('com_workflow.redirect_url', $installer->get('redirect_url'));
		
		// Cleanup the install files
		if (!is_file($package['packagefile']))
		{
			$config = JFactory::getConfig();
			$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
		}
		
		JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);
		
		return $result;		
	}

	/**
	 * Works out an installation package from a HTTP upload
	 *
	 * @return package definition or false on failure
	 */
	protected function _getPackageFromUpload()
	{
		// Get the uploaded file information
		$userfile = JRequest::getVar('install_package', null, 'files', 'array');
	
		// Make sure that file uploads are enabled in php
		if (!(bool) ini_get('file_uploads'))
		{
			JError::raiseWarning('', JText::_('COM_WORKFLOW_MSG_INSTALL_WARNINSTALLFILE'));
			return false;
		}
	
		// Make sure that zlib is loaded so that the package can be unpacked
		if (!extension_loaded('zlib'))
		{
			JError::raiseWarning('', JText::_('COM_WORKFLOW_MSG_INSTALL_WARNINSTALLZLIB'));
			return false;
		}
	
		// If there is no uploaded file, we have a problem...
		if (!is_array($userfile))
		{
			JError::raiseWarning('', JText::_('COM_WORKFLOW_MSG_INSTALL_NO_FILE_SELECTED'));
			return false;
		}
	
		// Check if there was a problem uploading the file.
		if ($userfile['error'] || $userfile['size'] < 1)
		{
			JError::raiseWarning('', JText::_('COM_WORKFLOW_MSG_INSTALL_WARNINSTALLUPLOADERROR'));
			return false;
		}
	
		// Build the appropriate paths
		$config		= JFactory::getConfig();
		$tmp_dest	= $config->get('tmp_path') . '/' . $userfile['name'];
		$tmp_src	= $userfile['tmp_name'];
	
		// Move uploaded file
		jimport('joomla.filesystem.file');
		JFile::upload($tmp_src, $tmp_dest);
	
		// Unpack the downloaded package file
		$package = JInstallerHelper::unpack($tmp_dest, true);
	
		return $package;
	}
	
	/**
	 * Install an extension from a directory
	 *
	 * @return  array  Package details or false on failure
	 *
	 * @since   1.5
	 */
	protected function _getPackageFromFolder()
	{
		$input = JFactory::getApplication()->input;
	
		// Get the path to the package to install
		$p_dir = $input->getString('install_directory');
		$p_dir = JPath::clean($p_dir);
	
		// Did you give us a valid directory?
		if (!is_dir($p_dir))
		{
			JError::raiseWarning('', JText::_('COM_WORKFLOW_MSG_INSTALL_PLEASE_ENTER_A_PACKAGE_DIRECTORY'));
			return false;
		}
	
		// Detect the package type
		$type = JInstallerHelper::detectType($p_dir);
	
		// Did you give us a valid package?
		if (!$type)
		{
			JError::raiseWarning('', JText::_('COM_WORKFLOW_MSG_INSTALL_PATH_DOES_NOT_HAVE_A_VALID_PACKAGE'));
		}
	
		$package['packagefile'] = null;
		$package['extractdir'] = null;
		$package['dir'] = $p_dir;
		$package['type'] = $type;
	
		return $package;
	}	
}