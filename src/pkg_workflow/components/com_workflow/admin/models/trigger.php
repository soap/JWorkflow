<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');

/**
 * Trigger model.
 *
 * @package     JWorkflow
 * @subpackage  com_workflow
 * @since       1.0
 */
class WorkflowModelTrigger extends JModelAdmin
{
	/**
	 * Method to get the Plugin form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 * @since   1.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			$this->option.'.'.$this->name,
			$this->getName(),
			array('control' => 'jform', 'load_data' => $loadData)
		);

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get a Plugin.
	 *
	 * @param   integer  $pk  An optional id of the object to get, otherwise the id from the model state is used.
	 *
	 * @return  mixed    Category data object on success, false on failure.
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		if ($result = parent::getItem($pk)) {

			// Convert the created and modified dates to local user time for display in the form.
			jimport('joomla.utilities.date');
			$tz	= new DateTimeZone(JFactory::getApplication()->getCfg('offset'));

			if (intval($result->created)) {
				$date = new JDate($result->created);
				$date->setTimezone($tz);
				$result->created = $date->toSQL(true);
			}
			else {
				$result->created = null;
			}

			if (intval($result->modified)) {
				$date = new JDate($result->modified);
				$date->setTimezone($tz);
				$result->modified = $date->toSQL(true);
			}
			else {
				$result->modified = null;
			}
		}

		return $result;
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   JTable  $table  A record object.
	 *
	 * @return  array  An array of conditions to add to add to ordering queries.
	 * @since   1.0
	 */
	protected function getReorderConditions($table = null)
	{
		$condition = array(
			'folder = '.$table->folder
		);

		return $condition;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name.
	 * @param   array   $config  Configuration array for model.
	 *
	 * @return  JTable  A database object
	 * @since   1.0
	 */
	public function getTable($type = 'Trigger', $prefix = 'WorkflowTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 * @since   1.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState($this->option.'.edit.'.$this->getName().'.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   JTable  $table  The table object for the record.
	 *
	 * @return  boolean  True if successful, otherwise false and the error is set.
	 * @since   1.0
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id)) {
			// For a new record.

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db		= JFactory::getDbo();
				$query	= $db->getQuery(true);
				$query->select('MAX(ordering)');
				$query->from('#__wf_triggers');
				$query->where('folder = '. $table->folder);
				
				$max = (int) $db->setQuery($query)->loadResult();
				
				if ($error = $db->getErrorMsg()) {
					$this->setError($error);
					return false;
				}

				$table->ordering = $max + 1;
			}
		}

	}
	
	/**
	 * Remove (uninstall) an extension
	 *
	 * @param   array  $eid  An array of identifiers
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.5
	 */
	public function remove($eid = array())
	{
		$user = JFactory::getUser();
	
		if ($user->authorise('core.delete', 'com_workflow'))
		{
			$failed = array();
	
			/*
			 * Ensure eid is an array of extension ids in the form id => client_id
			* TODO: If it isn't an array do we want to set an error and fail?
			*/
			if (!is_array($eid))
			{
				$eid = array($eid => 0);
			}
	
			// Get an installer object for the extension type
			$installer = JInstaller::getInstance();
			$adapter = new JInstallerAdapterTrigger($installer, $this->getDbo());
			$installer->setAdapter('trigger', $adapter);
			
			$row = JTable::getInstance('Trigger', 'WorkflowTable');
	
			// Uninstall the chosen extensions
			$msgs = array();
			$result = false;
			foreach ($eid as $id)
			{
				$id = trim($id);
				$row->load($id);
	
				$langstring = 'COM_WORKFLOW_TYPE_TYPE_' . strtoupper($row->type);
				$rowtype = JText::_($langstring);
				if (strpos($rowtype, $langstring) !== false)
				{
					$rowtype = $row->type;
				}
	
				if ($row->type && $row->type == 'trigger')
				{
					$result = $installer->uninstall($row->type, $id);
	
					// Build an array of extensions that failed to uninstall
					if ($result === false)
					{
						// There was an error in uninstalling the package
						$msgs[] = JText::sprintf('COM_WORKFLOW_UNINSTALL_ERROR', $rowtype);
						$result = false;
					}
					else
					{
						// Package uninstalled sucessfully
						$msgs[] = JText::sprintf('COM_WORKFLOW_UNINSTALL_SUCCESS', $rowtype);
						$result = true;
					}
				}
				else
				{	
						// There was an error in uninstalling the package
						$msgs[] = JText::sprintf('COM_WORKFLOW_UNINSTALL_ERROR', $rowtype);
						$result = false;
				}
			}
			$msg = implode("<br />", $msgs);
			$app = JFactory::getApplication();
			$app->enqueueMessage($msg);
			$this->setState('action', 'remove');
			$this->setState('name', $installer->get('name'));
			$app->setUserState('com_workflow.message', $installer->message);
			$app->setUserState('com_workflow.extension_message', $installer->get('extension_message'));
			return $result;
		}
		else
		{
			JError::raiseWarning(403, JText::_('JERROR_CORE_DELETE_NOT_PERMITTED'));
		}
	}	
}