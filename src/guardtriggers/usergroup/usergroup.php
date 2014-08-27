<?php
defined( '_JEXEC' ) or die;
jimport('workflow.plugin.workflowtrigger');

// class name = plg<Group><Name>
class trgGuardUsergroup extends trgAbstractTrigger 
{
	protected $_type = 'guard';
   	protected $_namespace = 'Workflow.Transition.Guard.Usergroup';
   	protected $_messages = array();
   
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
    	
    	$userGroups = $oUser->getAuthorisedGroups();
    	$groups = $this->params->get('group');
    	JArrayHelper::toInteger($groups);
    	$allowOwner = (int)$this->params->get('allowowner');
		$fieldName = (string)$this->params->get('ownerfiled');
		
    	if ($allowOwner === 1 && isset($oDocument->$fieldName)) {
    		if ($oUser->id == $oDocument->$fieldName) { 
    			return true;
    		}else{
    			$this->_messages['owner_blocked'] = JText::_('TRG_GUARD_USERGROUP_MSG_NOT_OWNER'); 
    		}
    	}

    	if (!is_array($groups)) {
    		$groups = array($groups);
    	}
		if (count(array_intersect($groups, $userGroups)) > 0) {
			return true;
		}else{
			$groupNames = $this->getGroupName($groups);
			$this->_messages['group_blocked'] = JText::sprintf('TRG_GUARD_USERGROUP_MSG_NOT_IN_ALLOWED_GROUP', implode(',', $groupNames));
			return false;	
		}
    	return true;	
    }
    
    public function getConfigSummary()
    {
    	$groups = $this->params->get('group');
    	JArrayHelper::toInteger($groups);
    	$groupNames = $this->getGroupName($groups);
    	
    	return JText::sprintf('TRG_GUARD_USERGROUP_USER_IN_GROUPS', implode(',', $groupNames));	
    }
    
    public function getExplain()
    {
    	return (count($this->_messages) ? implode("\n", $this->_messages) : '');
    }
    
    private function getGroupName($groups)
    {
    	$dbo = JFactory::getDbo();
    	$query = $dbo->getQuery(true);
    	
    	$query->select('title')->from('#__usergroups')->where('id IN ('.implode(',', $groups).')');
    	$dbo->setQuery($query);
    	$names = $dbo->loadColumn();
    	
    	return $names;
    }
}