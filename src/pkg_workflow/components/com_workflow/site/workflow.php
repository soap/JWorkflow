<?php
defined('_JEXEC') or die;
// Include dependencies
jimport('joomla.application.component.controller');
jimport('joomla.application.component.helper');

jimport('workflow.framework');

$controller = JControllerLegacy::getInstance('workflow');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
