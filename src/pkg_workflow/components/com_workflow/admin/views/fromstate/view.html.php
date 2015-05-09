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
class WorkflowViewFromState extends JViewLegacy
{
	/**
	 * @var    JObject	The data for the record being displayed.
	 * @since  1.0
	 */
	protected $item;

	/**
	 * @var    JForm  The form object for this record.
	 * @since  1.0
	 */
	protected $form;

	/**
	 * @var    JObject  The model state.
	 * @since  1.0
	 */
	protected $state;

	/**
	 * Prepare and display the FromState view.
	 *
	 * @return  void
	 * @since   1.0
	 */
	public function display()
	{
		// Intialiase variables.
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');
		$this->state	= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$this->addToolbar();
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
		JRequest::setVar('hidemainmenu', true);

		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);
		$checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
		$canDo		= WorkflowHelper::getActions();

		JToolBarHelper::title(
			JText::_(
				'COM_WORKFLOW_'.
				($checkedOut
					? 'VIEW_FROMSTATE'
					: ($isNew ? 'ADD_FROMSTATE' : 'EDIT_FROMSTATE')).'_TITLE'
			)
		);

		// If not checked out, can save the item.
		if (!$checkedOut && $canDo->get('core.edit')) {
			JToolBarHelper::apply('fromstate.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('fromstate.save', 'JTOOLBAR_SAVE');
		}

		if (empty($this->item->id))  {
			JToolBarHelper::cancel('fromstate.cancel', 'JTOOLBAR_CANCEL');
		}
		else {
			JToolBarHelper::cancel('fromstate.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}