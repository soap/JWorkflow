<?php
defined( '_JEXEC' ) or die;
jimport('workflow.plugin.workflowtrigger');

// class name = plg<Group><Name>
class trgActionJoomlacontent extends trgAbstractTrigger 
{
	protected $_type = 'action';
   	protected $_namespace = 'Workflow.Transition.Action.Joomlacontent';
   
    public function __construct($params = array()) 
    {
		parent::__construct($params);
    }
    
    public function afterTransition($oInstance, $oDocument, $oUser)
    {
    	$publishing = $this->params->get('publishing_state', 'unchange'); 
    	$featured = $this->params->get('featured_state', 'unchange');
    	
    	$config = array('ignore_request' => true);
    	$model = JModelLegacy::getInstance('Content', 'J', $config);
    	if ($publishing !== 'unchange' && in_array($publishing, array('0', '1', '-1', '-2') )) {
    		$model->publish(array($oDocument->id), $publishing);	
    	}
    	
    	if ($publishing == '1' && $featured == '1') {
    		$model->featured(array($oDocument->id), 1);	
    	}
    	
    	if ( in_array($publishing, array('0', '-1', '-2')) ) {
    		$model->featured($array($oDocument->id), 0);
    	}
  		
    	return true;	
    }
}