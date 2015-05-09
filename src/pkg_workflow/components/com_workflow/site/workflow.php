<?php
defined('_JEXEC') or die;
// Include dependencies
jimport('joomla.application.component.controller');
jimport('joomla.application.component.helper');

jimport('workflow.framework');

$input = JFactory::getApplication()->input;
// Check for array format.
$filter = JFilterInput::getInstance();
$command  = $input->get('task', 'display');
if (is_array($command))
{
	$command = $filter->clean(array_pop(array_keys($command)), 'cmd');
}
else
{
	$command = $filter->clean($command, 'cmd');
}
$config = array();
// Check for a controller.task command.
if (strpos($command, '.') !== false)
{
	// Explode the controller.task command.
	list ($type, $task) = explode('.', $command);
	if ($type == 'instance') $config=array('base_path'=> JPATH_ADMINISTRATOR.'/components/com_workflow/');
}

$controller = JControllerLegacy::getInstance('workflow', $config);
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
