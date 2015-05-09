<?php
defined('_JEXEC') or die;
jimport('joomla.database.table');

/**
 * TriggerInstance table.
 *
 * @package     Workflow
 * @subpackage  com_workflow
 * @since       1.0
 */
class WorkflowTableTriggerinstance extends JTable
{
	
	/**
	 * Constructor.
	 *
	 * @param   JDatabase  $db  A database connector object.
	 *
	 * @return  WorkflowTableTringgerInstance
	 * @since   1.0
	 */
	public function __construct($db)
	{
		parent::__construct('#__wf_trigger_instances', 'id', $db);
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
		if (isset($array['params']) && is_array($array['params'])) {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}
					
		if (isset($array['trigger_id']) && !empty($array['trigger_id'])) {
			$query = $this->_db->getQuery(true);
			$query->select('namespace')
				->from('#__wf_triggers AS a')
				->where('a.id ='.(int)$array['trigger_id']);
			$this->_db->setQuery($query);
			
			$array['namespace'] = $this->_db->loadResult();
		}

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
		// Check for valid plugin
		if (empty($this->trigger_id)) {
			$this->setError(JText::_('COM_WORKFLOW_ERROR_TRIGGERINSTANCE_TRIGGER'));
			return false;
		}
		
		if (empty($this->transition_id)) {
			$this->setError(JText::_('COM_WORKFLOW_ERROR_TRIGGERINSTANCE_TRANSITION'));
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
		$date	= JFactory::getDate()->toSQL();
		$userId	= JFactory::getUser()->get('id');

		if (empty($this->id)) {
			// New record.
			$this->created		= $date;
			$this->created_by	= $userId;
		} 
		else {
			// Existing record.
			$this->modified	= $date;
			$this->modified_by	= $userId;
		}

		// Attempt to store the data.
		return parent::store($updateNulls);
	}
    
    function getNameSpace()
    {
        return $this->namespace;    
    } 
    
}