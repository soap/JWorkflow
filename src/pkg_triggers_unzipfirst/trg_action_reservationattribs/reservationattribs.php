<?php
defined( '_JEXEC' ) or die;
jimport('workflow.plugin.workflowtrigger');

// class name = trg<Group><Name>
class trgActionReservationattribs extends trgAbstractTrigger 
{
	protected $_type = 'action';
   	protected $_namespace = 'Workflow.Transition.Action.ReservationAttribs';
   
    public function __construct($params = array()) 
    {
		parent::__construct($params);
    }
    
    public function afterTransition($oInstance, $oDocument, $oUser)
    {
    	JLog::add(__METHOD__.' get called', JLog::INFO, 'jworkflow');
    	
    	$action = $this->params->get('action');
    	$attributeName = $this->params->get('attribute_name');

    	if (!in_array($action, array('set', 'reset'))) {
    		JLog::add('Unrecognize action ;'.$action, JLog::WARNING, 'jworkflow');
    		return true;
    	}
    	if (!in_array($attributeName, array('approved_by', 'acked_by'))) {
    		JLog::add('Unrecognize attribute name ;'.$attributeName, JLog::WARNING, 'jworkflow');
    		return true;
    	}
    	
    	if (!($oDocument instanceof JTable)) {
    		JLog::add('Document is not instance of JTable, can not go further', JLog::ERROR, 'jworkflow');
    		return true;
    	}
    	
    	if (isset($oDocument->attribs)) {
    		if (!($oDocument->attribs instanceof JRegistry)) {
    			$oDocument->attribs = new JRegistry($oDocument->attribs);
    		}
    	}else{
    		$oDocument->attribs = new JRegistry();
    	}
    	
    	if ($action == 'set') {
    		/* Set attribute */
			$oDocument->attribs->set($attributeName, $oUser->id);
			/* Set action date */
			if ($attributeName=='approved_by') $oDocument->attribs->set('approved', JDate::getInstance()->toSql());
			if ($attributeName=='acked_by') $oDocument->attribs->set('acked', JDate::getInstance()->toSql());
    	}else if ($action == 'reset') {
    		/* Reset attribute */
    		$oDocument->attribs->set($attributeName, '');
    		if ($attributeName=='approved_by') $oDocument->attribs->set('approved', '');
    		if ($attributeName=='acked_by') $oDocument->attribs->set('acked', '');
    	}
    	
    	$oDocument->attribs = (string) $oDocument->attribs;
  		$oDocument->store();
    	
  		return true;	
    }
}