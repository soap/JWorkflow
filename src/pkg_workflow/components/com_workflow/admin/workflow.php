<?php
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_workflow')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependencies
jimport('joomla.application.component.controller');
jimport('workflow.framework');

//@todo Fix this CONSTANT not defined in library
// define guard/action plugin path
if (!defined('WFPATH_PLUGINS')) {
	define('WFPATH_PLUGINS', JPATH_ADMINISTRATOR.'/components/com_workflow/plugins');
}

// Register our JHtmlRegonline class to Joomla libraries
JLoader::register('JHtmlWorkflow', JPATH_COMPONENT_ADMINISTRATOR.'/helpers/html/workflow.php');

$controller = JControllerLegacy::getInstance('workflow');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();