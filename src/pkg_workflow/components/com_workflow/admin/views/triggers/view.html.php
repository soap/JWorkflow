<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * Workflow view.
 *
 * @package     Workflow
 * @subpackage  com_workflow
 * @since       1.0
 */
class WorkflowViewTriggers extends JViewLegacy
{
	protected $items;

	protected $pagination;

	protected $state;

	public function display($tp = NULL)
	{
		// Initialise variables.
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->filterForm    	= $this->get('FilterForm');
		$this->activeFilters 	= $this->get('ActiveFilters');
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Add the toolbar if it is not in modal
		if ($this->getLayout() !== 'modal') {
			$this->addToolbar();
			$this->sidebar = JHtmlSidebar::render();
		}
		
		// Display the view layout.
		parent::display();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 * @since   1.0
	 */
	protected function addToolbar()
	{
		// Initialise variables.
		$state	= $this->get('State');
		$canDo	= WorkflowHelper::getActions();

		JToolBarHelper::title(JText::_('COM_WORKFLOW_TRIGGERS_TITLE'));

		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('trigger.edit', 'JTOOLBAR_EDIT');
		}
		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::publishList('triggers.publish', 'JTOOLBAR_PUBLISH');
			JToolBarHelper::unpublishList('triggers.unpublish', 'JTOOLBAR_UNPUBLISH');
		}

		if ($canDo->get('core.delete')) {
			JToolbarHelper::deleteList('', 'triggers.remove', 'JTOOLBAR_UNINSTALL');
		} 

	}
}