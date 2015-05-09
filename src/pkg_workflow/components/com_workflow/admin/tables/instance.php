<?php
defined('_JEXEC') or die;

jimport('joomla.database.table');

/**
 * Instance table.
 *
 * @package     Workflow
 * @subpackage  com_workflow
 * @since       1.0
 */
class WorkflowTableInstance extends JTable
{
	/**
	 * Constructor.
	 *
	 * @param   JDatabase  $db  A database connector object.
	 *
	 * @return  WorfklowTableInstance
	 * @since   1.0
	 */
	public function __construct($db)
	{
		parent::__construct('#__wf_instances', 'id', $db);
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param   array   $array   The input array to bind.
	 * @param   string  $ignore  A list of fields to ignore in the binding.
	 *
	 * @return  null|string	null is operation was satisfactory, otherwise returns an error
	 * @see     JTable:bind
	 * @since   1.0
	 */
	public function bind($array, $ignore = '')
	{
		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check method to ensure data integrity.
	 *
	 * @return  boolean  True on success.
	 * @since   1.0
	 */
	public function check()
	{
		// Check for valid name.
		if (trim($this->context) === '') {
			$this->setError(JText::_('COM_WORFKLOW_ERROR_INSTANCE_CONTEXT'));
			return false;
		}
		
		if (empty($this->item_id)) {
			$this->setError(JText::_('COM_WORFKLOW_ERROR_INSTANCE_ITEM_ID'));
			return false;
		}
		
		if (empty($this->workflow_id)) {
			$this->setError(JText::_('COM_WORFKLOW_ERROR_INSTANCE_WORKFLOW_ID'));
			return false;
		}		

		if (empty($this->workflow_state_id)) {
			$this->setError(JText::_('COM_WORFKLOW_ERROR_INSTANCE_WORKFLOW_STATE_ID'));
			return false;
		}		
		return true;
	}

	/**
	 * Overload the store method for the Weblinks table.
	 *
	 * @param   boolean  $updateNulls  Toggle whether null values should be updated.
	 *
	 * @return  boolean  True on success, false on failure.
	 * @since   1.0
	 */
	public function store($updateNulls = false)
	{
		// Initialiase variables.
		$date	= JFactory::getDate()->toSql();

		if (empty($this->id)) {
			// New record.
			$this->created	= $date;
		} 
		else {
			// Existing record.
			$this->modified	= $date;
		}

		// Attempt to store the data.
		return parent::store($updateNulls);
	}
}