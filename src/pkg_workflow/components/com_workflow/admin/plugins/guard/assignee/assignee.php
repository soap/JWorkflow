<?php
defined( '_JEXEC' ) or die;
jimport('workflow.plugin.workflowtrigger');

// class name = plg<Group><Name>
class plgGuardAssignee extends plgAbstractTrigger 
{
	protected $_type = 'guard';
   	protected $_namespace = 'Workflow.Transition.Guard.Assignee';
   
    public function __construct($params = array()) 
    {
		parent::__construct($params);
    }

    /**
     * Validate if the transition is blocked or not 
     */ 
    public function allowTransition($oDocument, $oUser) 
    {
    	if (!$this->isLoaded()) return true;

    	$item_type = $this->params->get('item_type');
    	$userTable = $this->params->get('assigneduser_table', 'pf_ref_users');
        $gtoupTable = $this->params->get('assignedgroup_table', 'pf_ref_groups');
    	$allowSuperAdmin = $this->params->get('allowsuperadmin', false);
    	
    	if ($allowSupperAdmin && $oUser->get('isRoot')) return true;
    	
    	//if ($oUser->authorise('core.admin', $item_type) && $allowSuperAdmin) return true;
    	
    	$db = JFactory::getDbo();
    	$query = $db->getQuery(true);
    	
    	$table = $this->validateTableName($userTable);
       	if ($table === false) return false;
       	
    	$query->select('COUNT(a.user_id)')
    		->from($table . ' AS a')
    		->where('a.item_type = ' . $db->quote($item_type))
    		->where('a.item_id = ' . $oDocument->id)
    		->where('a.user_id = ' . $oUser->id );
    	$db->setQuery($query);
    	
		// type cast here otherwise logic comparison below did not work
    	$count = (int)$db->loadResult();

    	if ($count > 0) return true;
		
    	$table = $this->validateTableName($groupTable);
        if ($table === false) return false;
        
    	$authorisedGroups = implode(',', $oUser->getAuthorisedGroups());
    	$query->clear();
    	$query->select('COUNT(a.group_id)')
    		->from('#__pf_ref_groups AS a')
    		->where('a.item_type = ' . $db->quote($item_type))
    		->where('a.item_id = ' . $oDocument->id )
    		->where('a.group_id IN ('.$authorisedGroups.')');
    		
    	$db->setQuery($query);
    	
    	// type cast here otherwise logic comparison below did not work
    	$count = (int)$db->loadResult();
    	if ($count > 0) return true;
    	    	
    	return false;
    }
    
    public function getConfigSummary()
    {
    	return JText::_('PLG_GUARD_ASSIGNEE_ITEM_ASSIGNED_TO');	
    }
    
    public function getExplain()
    {
    	return JText::_('PLG_GUARD_ASSIGNEE_ITEM_NOT_ASSIGNED');
    }
    
    private function validateTableName($tableName)
    {
    	$prefixPos = strpos('#__', $tableName); 
    	
    	if ($prefixPos == 0) return $tableName;
    	if ($prefixPos == false) return '#__'.$tableName;
    	    	
    	return false;
    }    
}