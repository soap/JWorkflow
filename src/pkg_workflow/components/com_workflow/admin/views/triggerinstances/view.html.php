<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.view');

/**
 * Workflow view.
 *
 * @package     JWorkflow
 * @subpackage  com_workflow
 * @since       1.0
 */
class WorkflowViewTriggerinstances extends JViewLegacy
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

	protected $workflow;
	protected $transition;
	/**
	 * Prepare and display the Guards view.
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
		$this->workflow		= $this->get('Workflow');
		$this->transition	= $this->get('Transition');

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

		JToolBarHelper::title(JText::_('COM_WORKFLOW_TRIGGER_INSTANCES_TITLE'));

		if ($canDo->get('core.create') && $state->get('filter.transition_id')) {
			JToolBarHelper::addNew('triggerinstance.add', 'JTOOLBAR_NEW');
		}

		if ($canDo->get('core.edit')) {
			JToolBarHelper::editList('triggerinstance.edit', 'JTOOLBAR_EDIT');
		}

		if ($canDo->get('core.edit.state')) {
			JToolBarHelper::publishList('triggersinstances.publish', 'JTOOLBAR_PUBLISH');
			JToolBarHelper::unpublishList('triggerinstances.unpublish', 'JTOOLBAR_UNPUBLISH');
		}

		if ($canDo->get('core.delete')) {
			JToolBarHelper::deleteList('', 'triggerinstances.delete','JTOOLBAR_DELETE');
		} 

		JToolBarHelper::cancel('triggerinstances.cancel', 'JTOOLBAR_BACK');
	}
}