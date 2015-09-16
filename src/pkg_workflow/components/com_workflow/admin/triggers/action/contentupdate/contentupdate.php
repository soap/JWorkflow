<?php
defined( '_JEXEC' ) or die;
jimport('workflow.plugin.workflowtrigger');

// class name = plg<Group><Name>
class trgActionContentupdate extends trgAbstractTrigger 
{
	protected $_type = 'action';
   	protected $_namespace = 'Workflow.Transition.Action.Contentupdate';
   
    public function __construct($params = array()) 
    {
		parent::__construct($params);
    }
    
    public function afterTransition($oInstance, $oDocument, $oUser)
    {
    	JLog::add(__METHOD__.' get called', JLog::INFO, 'jworkflow');
    	$updateItems = explode("\r\n", trim($this->params->get('update_items')));
    	
    	if (!($oDocument instanceof JTable)) {
    		JLog::add('Document is not instance of JTable, can not go further', JLog::ERROR, 'jworkflow');
    		return true;
    	}
    	
    	$count = 0;
  		if (count($updateItems)) {
  			foreach($updateItems as $item) {
  				list($key, $value) = explode("=", trim($item));
  				$key 	= trim($key);
  				$value	= trim($value);
  				if (isset($oDocument->$key)) {
  					switch (strtoupper($value)) {
  						case '{DATETIME}' :
  							$date = JFactory::getDate();
  							$oDocument->$key = $date->toSql();
  							$count += 1; 
  						break;
  						case '{USERID}' :
  							$oDocument->$key = $oUser->id;
  							$count += 1;
  						break;
  						default :
  							$oDocument->$key = $value;
  							$count += 1;
  						break;	
  					} 
  				}
  			}
  		}
    	
  		$oDocument->store();
  		JFactory::getApplication()->enqueueMessage("Updated {$count} content attribute(s)");
  		
    	return true;	
    }
}