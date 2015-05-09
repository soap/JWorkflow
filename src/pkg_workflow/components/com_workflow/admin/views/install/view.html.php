<?php
defined('_JEXEC') or die;

include_once __DIR__ . '/../default/view.php';

/**
 *
 * @package     JWorkflow
 * @subpackage  com_workflow
 * @since       1.0
 */
class WorkflowViewInstall extends WorkflowViewDefault
{
	protected $items;
	protected $pagination;
	protected $state;


	public function display($tpl = null)
	{
		$paths = new stdClass;
		$paths->first = '';
		$state = $this->get('state');

		$this->paths = &$paths;
		$this->state = &$state;

		JPluginHelper::importPlugin('installer');

		$dispatcher = JEventDispatcher::getInstance();

		// Add the toolbar if it is not in modal
		if ($this->getLayout() !== 'modal') {
			$this->addToolbar();
			$this->sidebar = JHtmlSidebar::render();
		}
		
		// Display the view layout.
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 * @since   1.0
	 */
	protected function addToolbar()
	{
		JToolBarHelper::title(JText::_('COM_WORKFLOW_INSTALL_TITLE'));
		
	}
}

