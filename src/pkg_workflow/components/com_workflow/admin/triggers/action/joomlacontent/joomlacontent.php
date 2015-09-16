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
    	JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR.'components/com_content/models', 'Content');
    	$model = JModelLegacy::getInstance('Article', 'Content', $config);
    	if ($publishing !== 'unchange' && in_array($publishing, array('0', '1', '2', '-2') )) {
    		$model->publish(array($oInstance->item_id), $publishing);	
    	}
    	
    	if ($publishing == '1' && $featured == '1') {
    		$model->featured(array($oInstance->item_id), 1);	
    	}
    	
    	if ( in_array($publishing, array('0', '2', '-2')) ) {
    		$model->featured($array($Instance->item_id), 0);
    	}
  		
    	return true;	
    }
    
    public function getConfigSummary()
    {
    	$publishing = $this->params->get('publishing_state', 'unchange');
    	$featured = $this->params->get('featured_state', 'unchange');
    	
    	switch($publishing) {
    		case '0' :
    			$summary = JText::sprintf('TRG_ACTION_JOOMLACONTENT_PUBLISHING_SET_TO', JText::_('JUNPUBLISHED') );
    			break;
    		case '1' :
    			$summary = JText::sprintf('TRG_ACTION_JOOMLACONTENT_PUBLISHING_SET_TO', JText::_('JPUBLISHED') );
    			break;
    		case '2' :
    			$summary = JText::sprintf('TRG_ACTION_JOOMLACONTENT_PUBLISHING_SET_TO', JText::_('JARCHIVED') );
    			break;
    		case '-2':
    			$summary = JText::sprintf('TRG_ACTION_JOOMLACONTENT_PUBLISHING_SET_TO', JText::_('JTRASHED') );
    			break;
    		default: 
    			$summary = JText::sprintf('TRG_ACTION_JOOMLACONTENT_PUBLISHING_SET_TO', JText::_('TRG_ACTION_JOOMLACONTENT_OPTION_UNCHANGE') );
    			break; 			 		
    	}
    	switch ($featured) {
    		case '0' : 
    			$summary .=', '.JText::_('TRG_ACTION_JOOMLACONTENT_FEATURED_OFF');
    			break;
    		case '1' :
    			$summary .= ', '.JText::_('TRG_ACTION_JOOMLACONTENT_FEATURED_ON'); 
    			break;
    		default:
    			$summary .= ', '.JText::_('TRG_ACTION_JOOMLACONTENT_FEATURED_UNCHANGE');
    			break;		
    	}
    	
    	return $summary;
 
    }
}