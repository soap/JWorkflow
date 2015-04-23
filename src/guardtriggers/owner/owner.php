<?php
defined('_JEXEC') or die;

jimport('workflow.plugin.workflowtrigger');

// class name = plg<Group><Name>
class trgGuardOwner extends trgAbstractTrigger 
{
	protected $_type = 'guard';
   	protected $_namespace = 'Workflow.Transition.Guard.Owner';
   
    public function __construct($params = array()) 
    {
		parent::__construct($params);
    }

    /**
     * Validate if the transition is blocked or not 
     */ 
    public function allowTransition($oInstance, $oDocument, $oUser) 
    {
    	if (!$this->isLoaded()) return true;
    	
    	if (!is_object($oDocument)) {
    		return false;
    	}
    	
    	$fields = explode("\r\n", trim($this->params->get('owner_fields')));
    	
    	$allowSuperAdmin = $this->params->get('allow_superadmin', false);
    	$allowed = (bool) $this->params->get('allow_mode', 1);
		$matched = false;
	
    	if ($allowSuperAdmin && $oUser->get('isRoot')) return true;

   		$uid = $oUser->get('id');
   		foreach($fields as $field) {
   			if (isset($oDocument->$field)) {
   				JLog::add('Field '.$field.' exists, try to validate if it matches');
   				if ($oDocument->$field == $uid){
   					$matched = true;
   				}  				
   			}	
   		}
    	    	
   		if (defined('JDEBUG') && JDEBUG)
   		{
   			JLog::add(
   				sprintf(
   					'Guard %s validates transition on instance %s.%d on fields; %s, result is %s %d',
   					$this->_name,
   					$oInstance->context,
   					(int)$oInstance->item_id,
   					join(',',$fields),
   					($matched ? 'true' : 'false'), $oDocument->owner_id
   				),
   				JLog::INFO,
   				'jworkflow'
   			);
   		}
   		
   		if ($matched) return  $allowed;   		
    	return !$allowed;
    }
    
    public function getConfigSummary()
    {
    	$allowSuperAdmin = (bool) $this->params->get('allow_superadmin', false);
    	$allowed = (bool) $this->params->get('allow_mode', true);

    	if ($allowed && $allowSuperAdmin) {
    		$result = JText::_('TRG_GUARD_OWNER_ALLOW_OWNER_OR_SUPERADMIN');	
    	}else if (!$allowed && $allowSuperAdmin) {
    		$result = JText::_('TRG_GUARD_OWNER_NOT_ALLOW_OWNER_BUT_SUPERADMIN');	
    	}else if ($allowed && !$allowSuperAdmin) {
    		$result = JText::_('TRG_GUARD_OWNER_ALLOW_OWNER_BUT_SUPERADMIN');
    	}else{
    		$result = JText::_('TRG_GUARD_OWNER_NOT_ALLOW_OWNER_AND_SUPERADMIN');
    	}
    	
    	return $result;
    }
    
    public function getExplain()
    {
    	return JText::_('TRG_GUARD_OWNER_ITEM_NOT_BELOGNGS');
    }
}