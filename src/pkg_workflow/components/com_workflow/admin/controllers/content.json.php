<?php
defined('_JEXEC') or die;

class WorkflowControllerContent extends WFControllerFormJson
{
	protected $_result = array();
	protected $_success = false;
	protected $_messages = array();
	protected $_data = null;
	
	public function state()
	{
		$jinput = JFactory::getApplication()->input;
		$context = $jinput->getCmd('context', null);
		$itemId = $jinput->getCmd('id', null);
	
		if (empty($context) || empty($itemId)) {
			$this->_success = false;
			$this->_messages[] = 'Incomplete input';
	
			$this->sendResponse();
		}
	
		$model = $this->getModel('Article','ContentModel', array('ignore_request'=>true));
		$content = $model->getItem($itemId);
		
		if (is_object($content)) {
			$this->_success = true;
			
			$state = new StdClass();
			$state->state = $state->state;
			$state->featured = $state->featured;
			
			$this->_data = $state; 
			$this->_messages[] = array();
		}
	
		$this->sendResponse();
	}
	
}