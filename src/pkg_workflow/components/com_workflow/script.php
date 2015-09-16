<?php
defined('_JEXEC') or die();


class com_workflowInstallerScript
{

	/**
     * Called before any type of action
     *
     * @param     string              $route      Which action is happening (install|uninstall|discover_install)
     * @param     jadapterinstance    $adapter    The object responsible for running this script
     *
     * @return    boolean                         True on success
     */
    public function preflight($route, JAdapterInstance $adapter)
    {
    	return true;
    }
    
        /**
     * Called after any type of action
     *
     * @param     string              $route      Which action is happening (install|uninstall|discover_install)
     * @param     jadapterinstance    $adapter    The object responsible for running this script
     *
     * @return    boolean                         True on success
     */
    public function postflight($route, JAdapterInstance $adapter)
    {
    	$msgError = '';
    	// END MESSAGE
    	if ($msgError != '') {
    		$msg = '<span style="font-weight: bold;color:#ff0000;">'.JText::_('COM_WORKFLOW_ERROR_INSTALL').'</span>: ' . $msgSuccess . $msgError;
    		JFactory::getApplication()->enqueueMessage($msg, 'error');
    	} else {
    		$msg = '<span style="font-weight: bold;color:#00cc00;">'.JText::_('COM_WORKFLOW_SUCCESS_INSTALL').'</span>: ' . $msgSuccess;
    			
    		JFactory::getApplication()->enqueueMessage($msg, 'message');
    	}
    	return true;
    }

    /**
     * Called on installation
     *
     * @param     jadapterinstance    $adapter    The object responsible for running this script
     *
     * @return    boolean                         True on success
     */
    public function install(JAdapterInstance $adapter)
    {
    	return true;
    }    
    
    /**
     * Called on uninstallation
     *
     * @param    jadapterinstance    $adapter    The object responsible for running this script
     */
    public function uninstall(JAdapterInstance $adapter)
    {
    	return true;
    }    
}
