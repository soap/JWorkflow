<?php
defined( '_JEXEC' ) or die;
jimport('workflow.plugin.workflowtrigger');

// class name = plg<Group><Name>
class plgGuardAttributes extends plgAbstractTrigger 
{
	protected $_type = 'guard';
   	protected $_namespace = 'Workflow.Transition.Guard.Attributes';
   
    public function __construct($params = array()) 
    {
		parent::__construct($params);
    }
    
    public function allowTransition($oDocument, $oUser)
    {
    	$attribs = explode("\r\n", trim($this->params->get('match_attributes')));
    	$item_type = $this->params->get('item_type');	
    }
    
    public function getExplain()
    {
    	return JText::_('PLG_GUARD_ATTRIBUTES_SPECIFIED_ATTRIBUTES_UNMATCHED');	
    }
}