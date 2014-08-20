<?php
defined( '_JEXEC' ) or die;
jimport('workflow.plugin.workflowtrigger');

// class name = plg<Group><Name>
class plgGuardRequiredfields extends plgAbstractTrigger 
{
	protected $_type = 'guard';
   	protected $_namespace = 'Workflow.Transition.Guard.Requiredfields';
   	protected $_fields = array();
   	
    public function __construct($params = array()) 
    {
		parent::__construct($params);
    }
    
    public function allowTransition($oDocument, $oUser)
    {
    	$context = trim($this->params->get('context'));
    	if (!empty($context) && isset($oDocument->context)) {
    		if ($oDocunent->context != $context) {
    			return true;
    		}
    	}
    	
    	$fields = explode("\r\n", trim($this->params->get('required_fields')));
    	if (count($fields)) {
    		$blocked = false;
    		foreach($fields as $field) {
    			if (isset($oDocument->$field) && empty($oDocument->$field)) {
    				$this->_fields[] = ucfirst($field);
    				$blocked = true;
    			} 		
    		}

    		return !$blocked;
    	}
    	 
    	return true;	
    }
    public function getConfigSummary()
    {
    	$fields = explode("\r\n", trim($this->params->get('required_fields')));
    	return JText::sprintf('PLG_GUARD_REQUIREDFIELDS_FIELDS_NOT_EMPTY', implode(',', $fields));		
    }
    
    public function getExplain()
    {
 		return (empty($this->_fields)? '': JText::sprintf('PLG_GUARD_REQUIREDFIELDS_FIELDS_EMPTY', implode(',', $this->_fields)));
    }
}