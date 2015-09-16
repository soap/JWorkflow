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
	 * Configure the sidebarbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	public static function addSubmenu($vName)
	{
		$layout = $app = JFactory::getApplication()->input->getCmd('layout', null);
		JHtmlSidebar::addEntry(
			JText::_('COM_WORKFLOW_SUBMENU_DASHBOARD'),
			'index.php?option=com_workflow&view=dashboard',
			$vName == 'dashboard'
		);
				
		JHtmlSidebar::addEntry(
			JText::_('COM_WORKFLOW_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_workflow',
			$vName == 'categories'
		);
		
		JHtmlSidebar::addEntry(
			JText::_('COM_WORKFLOW_SUBMENU_WORKFLOWS'),
			'index.php?option=com_workflow&view=workflows',
			$vName == 'workflows' || ($vName=='transitions' && $layout == 'fromstate')
		);
		
		JHtmlSidebar::addEntry(
			JText::_('COM_WORKFLOW_SUBMENU_BINDINGS'),
			'index.php?option=com_workflow&view=bindings',
			$vName == 'bindings'
		);
				
		JHtmlSidebar::addEntry(
			JText::_('COM_WORKFLOW_SUBMENU_STATES'),
			'index.php?option=com_workflow&view=states',
			$vName == 'states'
		);
		
		JHtmlSidebar::addEntry(
			JText::_('COM_WORKFLOW_SUBMENU_TRANSITIONS'),
			'index.php?option=com_workflow&view=transitions',
			$vName == 'transitions' && $layout != 'fromstate'
		);
		
		
		if ($vName == 'triggerinstances') {
			JHtmlSidebar::addEntry(
			JText::_('COM_WORKFLOW_SUBMENU_INSTANCES'),
			'index.php?option=com_workflow&view=triggerinstances',
			$vName == 'triggerinstances'
					);
		}		
		/*JHtmlSidebar::addEntry(
			JText::_('COM_WORKFLOW_SUBMENU_ROLES'),
			'index.php?option=com_workflow&view=roles',
			$vName == 'roles'
		);*/

		JHtmlSidebar::addEntry(
			JText::_('COM_WORKFLOW_SUBMENU_TRIGGERS'),
			'index.php?option=com_workflow&view=triggers',
			$vName == 'triggers'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_WORKFLOW_SUBMENU_INSTALL'),
			'index.php?option=com_workflow&view=install',
			$vName == 'install'
		);	
		
	}

	
}