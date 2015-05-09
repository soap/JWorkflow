<?php
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

/**
 * Workflow Component Controller
 *
 * @package     Workflow
 * @subpackage  com_workflow
 * @since       1.0
 */
class WorkflowController extends JControllerLegacy
{
	/**
	 * Override the display method for the controller.
	 *
	 * @return  void
	 * @since   1.0
	 */
		
	protected $default_view = 'dashboard';
	
	public function display($cachable=false, $urlparams=null)
	{
		// Load the component helper.
		require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/workflow.php';
		// Load the submenu.
		$view = JRequest::getCmd('view', $this->default_view);
		WorkflowHelper::addSubmenu($view);
		$this->checkDependencies();
		// Display the view.
		parent::display($cachable, $urlparams);
	}
	
	private function checkDependencies()
	{
		//$jv      = new JVersion();
		if (version_compare(JVERSION, '3.0.0', 'lt')) {
			$this->checkJBoostrap();
		}
		
		$this->checkPlugins();
	}
	
	private function checkJBoostrap()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		$query->select('extension_id, element, client_id, enabled, access, protected')
			->from('#__extensions')
			->where($db->qn('type') . ' = ' . $db->quote('plugin'))
			->where($db->qn('element') . ' = '  . $db->quote('JBootstrap'))
			->where($db->qn('folder') . ' = '  . $db->quote('system'));
		$db->setQuery($query);
		$plugin = $db->loadObject('JObject');
		
		if ((int)$plugin->extension_id > 0) {
			if ($plugin->enabled == 0) {
				$message = JText::_('COM_WORKFLOW_WANRING_JBOOTSTRAP_NOT_ENABLED');
				JFactory::getApplication()->enqueueMessage($message, 'warning');
			}
		}else{
			$message = JText::_('COM_WORKFLOW_WARNING_JBOOTSTRAP_NOT_INSTALLED');
			JFactory::getApplication()->enqueueMessage($message, 'warning');
		}		
	}
	
	private function checkPlugins()
	{
		$doc = JFactory::getDocument();
		if ($doc->getType() != 'html' ) return;
		
 		$app = JFactory::getApplication();
		$params = JComponentHelper::getParams('com_workflow');
		if ($params->get('disable_dependency_warning', false)) return;
		
		$layout = $app->input->getCmd('layout');
		$format = $app->input->getCmd('format', 'html');
		if ($layout == 'edit' || $layout == 'modal') return;
		
		$workflowContent = JPluginHelper::isEnabled('workflow', 'content');
		if (!$workflowContent) {
			$app->enqueueMessage(JText::_('COM_WORKFLOW_PLUGIN_DISABLED_WORKFLOW_CONTENT'), 'error');
		}
		
		$workflowNotification = JPluginHelper::isEnabled('workflow', 'notification');
		if (!$workflowNotification) {
			$app->enqueueMessage(JText::_('COM_WORKFLOW_PLUGIN_DISABLED_WORKFLOW_NOTIFICATION'), 'warning');
		}
		
		$contentWorkflow = JPluginHelper::isEnabled('content', 'workflow');
		if (!$workflowNotification) {
			$app->enqueueMessage(JText::_('COM_WORKFLOW_PLUGIN_DISABLED_CONTENT_WORKLOW'), 'error');
		}
		
	}
}