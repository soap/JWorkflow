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
class WorkflowTableGuard extends JTable
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
		parent::__construct('#__wf_guards', 'id', $db);
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
					
		if (isset($array['plugin_id']) && !empty($array['plugin_id'])) {
			$query = $this->_db->getQuery(true);
			$query->select('namespace')
				->from('#__wf_plugins AS a')
				->where('a.id ='.(int)$array['plugin_id']);
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
		if (empty($this->plugin_id)) {
			$this->setError(JText::_('COM_WORKFLOW_ERROR_GUARD_PLUGIN'));
			return false;
		}
		
		if (empty($this->transition_id)) {
			$this->setError(JText::_('COM_WORKFLOW_ERROR_GUARD_TRANSITION'));
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
	
    function getConfig()
    {
        return unserialize($this->_config_array);
    }
    
    function setConfig($aConfig)
    {
        $this->_config_array = $aConfig;    
    }
    
    function updateConfig()
    {
        if(!empty($this->_config_array))
        {
            $aConfig = serialize($this->_config_array);
            $sql = "UPDATE #__wf_triggerinstances SET config_array = ".$this->_db->Quote($aConfig)
            ." WHERE id = ".$this->id;
            $this->_db->setQuery($sql);
            return $this->_db->query();
        }
        return true;    
    }
    
    function getNameSpace()
    {
        return $this->namespace;    
    } 
    
    static function getByWorkflowId($iWorkflowId)
    {
    	$_db = JFactory::getDbo();
    	$query = $_db->_db->getQuery(true);
    	$query->select('id')
    		->from('#__wf_triggerinstances AS a')
           	->where('workflow_transition_id IN (SELECT id FROM #__wf_transitions WHERE workflow_id = '.$iWorkflowId.')');

        $_dbo->setQuery($query);
        $aObjects = $_dbo->loadObjectList();
        
        $triggers = array();
        if (is_array($aObjects)){        
            foreach ($aObjects as $object){
                $trigger = new WorkflowTableTriggerInstance($this->_dbo);
                $trigger->load($object->id);
                $triggers[] = $trigger;
            }
        }
        return $triggers;    
    }	
}