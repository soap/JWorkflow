<?php
defined('_JEXEC') or die;
/**
 * Workflow display helper.
 *
 * @package     Workflow
 * @subpackage  com_workflow
 * @since       1.0
 */
class WorkflowHelper
{
	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  JObject
	 * @since   1.6
	 */
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, 'com_workflow'));
		}

		return $result;
	}
	
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.0
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('COM_WORKFLOW_SUBMENU_DASHBOARD'),
			'index.php?option=com_workflow&view=dashboard',
			$vName == 'dashboard'
		);
				
		JSubMenuHelper::addEntry(
			JText::_('COM_WORKFLOW_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_workflow',
			$vName == 'categories'
		);

		JSubMenuHelper::addEntry(
			JText::_('COM_WORKFLOW_SUBMENU_WORKFLOWS'),
			'index.php?option=com_workflow&view=workflows',
			$vName == 'workflows'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('COM_WORKFLOW_SUBMENU_BINDINGS'),
			'index.php?option=com_workflow&view=bindings',
			$vName == 'bindings'
		);
				
		JSubMenuHelper::addEntry(
			JText::_('COM_WORKFLOW_SUBMENU_STATES'),
			'index.php?option=com_workflow&view=states',
			$vName == 'states'
		);
		
		JSubMenuHelper::addEntry(
			JText::_('COM_WORKFLOW_SUBMENU_TRANSITIONS'),
			'index.php?option=com_workflow&view=transitions',
			$vName == 'transitions'
		);
		
		if ($vName == 'triggers') {
			JSubMenuHelper::addEntry(
				JText::_('COM_WORKFLOW_SUBMENU_TRIGGERS'),
				'index.php?option=com_workflow&view=triggers',
				$vName == 'triggers'
			);	
		}
		
		JSubMenuHelper::addEntry(
			JText::_('COM_WORKFLOW_SUBMENU_PLUGINS'),
			'index.php?option=com_workflow&view=plugins',
			$vName == 'plugins'
		);	

		JSubMenuHelper::addEntry(
			JText::_('COM_WORKFLOW_SUBMENU_INSTALL'),
			'index.php?option=com_workflow&view=install',
			$vName == 'install'
		);	
		
		JSubMenuHelper::addEntry(
			JText::_('COM_WORKFLOW_SUBMENU_ROLES'),
			'index.php?option=com_workflow&view=roles',
			$vName == 'roles'
		);	
	}

	
}