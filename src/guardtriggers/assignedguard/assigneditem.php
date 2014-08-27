<?php
/**
 * @version 1.1.8 RC3 Dated: 2014--28
 */
// no direct access
defined( '_JEXEC' ) or die;
jimport('workflow.plugin.workflowtrigger');

// class name = plg<Group><Name>
class trgGuardAssignedguard extends trgAbstractTrigger 
{
	protected $_type = 'guard';
   	protected $_namespace = 'Workflow.Transition.Guard.Assigneditem';
   
    public function __construct($params = array()) 
    {
		parent::__construct($params);
    }

    /**
     * Validate if the transition is blocked 
     */ 
    public function allowTransition($oDocument, $oUser) 
    {
        if (!$this->isLoaded()) {
            return true;
        }
        
        $context = $this->params->get('context');
        $userTable = $this->params->get('assigneduser_table', 'pf_ref_users');
        $gtoupTable = $this->params->get('assignedgroup_table', 'pf_ref_groups');
        if(empty($context)){
            // No context in configuration data, return false
            return false;
        }else{
        	$table = $this->validateTableName($userTable);
        	if ($table===false) return false;
        	
        	// Count if the user was assigned for this item
        	$db = JFactory::getDbo();
        	$query = $db->getQuery(true);
        	$query->select('user_id')
        		->from($table)
        		->where('item_type = '.$db->quote($context))
        		->where('item_id = '.(int)$oDocument->id);
        		
        	$db->setQuery($query);
        	$rows = $db->loadObjectList();
        	
        	if (count($rows)) return true;
        	
        	$table = $this->validateTableName($groupTable);
        	if ($table === false) return false;
        	
        	// Continue on group checking
        	$query->clear();
        	$query->select('group_id')
        		->from($table)
        		->where('item_type = '.$db->quote($context))
        		->where('item_id = '.(int)$oDocument->id);
        		
        	$db->setQuery($query);
        	$rows = $db->loadObjectList();

        	return (count($rows) > 0);
        	
        }
        
        return false;
    }
    
    public function getConfigSummary()
    {
    	return JText::_('TRG_GUARD_ASSIGNEDITEM_GUARD_ITEM_MUST_BE_ASSIGNED');	
    }
    
    public function getExplain() 
    {
    	return JText::_('PLG_GUARD_ASSIGNEDITEM_GUARD_ITEM_NOT_ASSIGNED');
    }
    
    private function validateTableName($tableName)
    {
    	$prefixPos = strpos('#__', $tableName); 
    	
    	if ($prefixPos == 0) return $tableName;
    	if ($prefixPos == false) return '#__'.$tableName;
    	    	
    	return false;
    }
 }

