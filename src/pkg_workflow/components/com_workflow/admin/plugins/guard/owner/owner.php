<?php
defined('_JEXEC') or die;

jimport('workflow.plugin.workflowtrigger');

// class name = plg<Group><Name>
class plgGuardOwner extends plgAbstractTrigger 
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
    public function allowTransition($oDocument, $oUser) 
    {
    	if (!$this->isLoaded()) return true;

    	$fields = explode("\r\n", trim($this->params->get('owner_fields')));
    	$allowSuperAdmin = $this->params->get('allow_superadmin', false);
    	$allowed = (bool) $this->params->get('allow_mode', true);
    	
    	if ($allowSuperAdmin && $oUser->get('isRoot')) return true;
    	
   		$uid = $oUser->get('id');
   		foreach($fields as $field) {
   			if (isset($oDocument->$field)) {
   				if ($oDocument->$field == $uid){
   					return $allowed;
   				}  				
   			}	
   		}
    	    	
    	return false;
    }
    
    public function getConfigSummary()
    {
    	$allowSuperAdmin = (bool) $this->params->get('allow_superadmin', false);
    	$allowed = (bool) $this->params->get('allow_mode', true);

    	if ($allowed && $allowSuperAdmin) {
    		$result = JText::_('PLG_GUARD_ALLOW_OWNER_OR_SUPERADMIN');	
    	}else if (!$allowed && $allowSuperAdmin) {
    		$result = JText::_('PLG_GUARD_NOT_ALLOW_OWNER_BUT_SUPERADMIN');	
    	}else if ($allowed && !$allowSuperAdmin) {
    		$result = JText::_('PLG_GUARD_ALLOW_OWNER_BUT_SUPERADMIN');
    	}else{
    		$result = JText::_('PLG_GUARD_NOT_ALLOW_OWNER_AND_SUPERADMIN');
    	}
    	
    	return $result;
    }
    
    public function getExplain()
    {
    	return JText::_('PLG_GUARD_OWNER_ITEM_NOT_BELOGNGS');
    }
}