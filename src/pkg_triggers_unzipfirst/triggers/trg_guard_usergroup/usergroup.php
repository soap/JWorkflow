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
    public function allowTransition($oInstance, $oDocument, $oUser) 
    {
    	if (!$this->isLoaded()) return true;
    	
    	if ((int)$this->params->get('inherited_groups', 0)) {
    		$userGroups = $oUser->getAuthorisedGroups();
    	}else{
    		$userGroups = JAccess::getGroupsByUser($oUser->id, false);
    	}
    	$groups = $this->params->get('groups', array());
    	JArrayHelper::toInteger($groups);
    	$allowOwner = (int)$this->params->get('allowowner', 0);
		$fieldName = (string)$this->params->get('ownerfiled', 'created_by');
		
    	if ($allowOwner === 1 && isset($oDocument->$fieldName)) {
    		if ($oUser->id == $oDocument->$fieldName) { 
    			return true;
    		}else{
    			$this->_messages['owner_blocked'] = JText::_('TRG_GUARD_USERGROUP_MSG_NOT_OWNER'); 
    		}
    	}
		JLog::add('User groups: '.join(',',$userGroups).'; configuration groups: '.join(',', $groups), JLog::DEBUG, 'jworkflow');
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
    	$groups = $this->params->get('groups', array());
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
    	if (empty($groups)) return array();
    	$dbo = JFactory::getDbo();
    	$query = $dbo->getQuery(true);
    	
    	$query->select('title')->from('#__usergroups')->where('id IN ('.implode(',', $groups).')');
    	$dbo->setQuery($query);
    	$names = $dbo->loadColumn();
    	
    	return $names;
    }
}