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
class WorkflowViewStates extends JViewLegacy
{
	/**
	 * @var    array  The array of records to display in the list.
	 * @since  1.0
	 */
	protected $items;

	/**
	 * @var    JPagination  The pagination object for the list.
	 * @since  1.0
	 */
	protected $pagination;

	/**
	 * @var    JObject	The model state.
	 * @since  1.0
	 */
	protected $state;

	/**
	 * Prepare and display the States view.
	 *
	 * @return  void
	 * @since   1.0
	 */
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

		JToolBarHelper::title(JText::_('COM_WORKFLOW_STATES_TITLE'));

		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('state.add', 'JTOOLBAR_NEW');
		}

		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('state.edit', 'JTOOLBAR_EDIT');
		}

		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::publishList('states.publish', 'JTOOLBAR_PUBLISH');
			JToolBarHelper::unpublishList('states.unpublish', 'JTOOLBAR_UNPUBLISH');
		}

		if ($state->get('filter.published') == -2 && $canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'states.delete','JTOOLBAR_EMPTY_TRASH');
		} 
		else if ($canDo->get('core.edit.state')) {
			JToolBarHelper::trash('states.trash','JTOOLBAR_TRASH');
		}

	}
}