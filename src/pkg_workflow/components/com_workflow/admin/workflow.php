<?php
defined('_JEXEC') or die;

// Include dependencies
jimport('joomla.application.component.controller');
jimport('workflow.framework');

// Access check, need to allow some task for all users.
$input = JFactory::getApplication()->input;
$task = $input->get('task');

$allowedTasks = array('instance.state', 'instance.validate', 
		'instance.transition', 'instance.transitions');
if (!in_array($task, $allowedTasks)) {
	if (!JFactory::getUser()->authorise('core.manage', 'com_workflow')) {
		return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
	}
}

//@todo Fix this CONSTANT not defined in library
// define guard/action plugin path
if (!defined('WFPATH_TRIGGERS')) {
	define('WFPATH_TRIGGERS', JPATH_ADMINISTRATOR.'/components/com_workflow/plugins');
}

// Register our JHtmlRegonline class to Joomla libraries
JLoader::register('JHtmlWorkflow', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/html/workflow.php');

$controller = JControllerLegacy::getInstance('workflow');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();