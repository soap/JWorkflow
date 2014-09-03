<?php
defined('_JEXEC') or die;


class WorkflowControllerInstance extends WFControllerFormJson
{
	protected $_result = array();
	protected $_success = false;
	protected $_messages = array();
	protected $_data = null;	
	
	/**
	 * Get state of the workflow instance
	 */
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
		
		$model = $this->getModel();

		if ($state = $model->getWorkflowState($context, $itemId)) {
			$this->_success = true;
			$this->_data = $state;	
			$this->_messages[] = array();	
		}

		$this->sendResponse();		
	}
	
	/**
	 * Get list of available transitions for instance/user
	 */
	public function transitions()
	{
		$jinput = JFactory::getApplication()->input;
		$context = $jinput->getCmd('context', null);
		$itemId = $jinput->getCmd('id', null);
		
		if (empty($context) || empty($itemId)) {
			$this->_success = false;
			$this->_messages[] = 'Incomplete input';	
			
			$this->sendResponse();
		}
		
		$model = $this->getModel();
		if ($items = $model->getWorkflowTransitions($context, $itemId)) {
			
			$this->_success = true;
			$this->_messages[] =	array();
			$this->_data = $items;
			$this->sendResponse();
		}
		
		$this->sendResponse();
	}

	/**
	 * Perform the selected transition on instance by user
	 */
	public function transition()
	{
		$jinput = JFactory::getApplication()->input;
		$context = $jinput->getCmd('context', null);
		$itemId = $jinput->getCmd('item_id', null);
		$transitionId = $jinput->getCmd('transition_id', null);
		$comment = $jinput->getString('comment', '');
		$user = JFactory::getUser();
		
		$input = array('context'=>$context, 'item_id'=>$itemId, 'user'=>$user, 
					'transition_id'=>$transitionId, 'comment'=>$comment);
		
		if (!$this->allowTransition($input, 'item_id')) {
			$this->_messages[] = JText::_('COM_WORKFLOW_APPLICATION_ERROR_TRANSITION_NOT_PERMITTED');
			$this->sendResponse();	
		}
		
		$model = $this->getModel();
		if (!$model->makeTransition($input)) {
			$this->_messages[] = $model->getError();
			$this->sendResponse();		
		}
		
		$this->_success = true;
		$this->_data['title'] = 'Seccessfully notification';
		$this->_data['text'] = $model->getTransitionLogMessage($context, $itemId, $transitionId );
		$this->sendResponse();
	}
	
	/**
	 *
	 * validate if transtion if requirement match before perform transition on work item
	 */
	public function validate()
	{
		$jinput = JFactory::getApplication()->input;
	
		$transition_id   = $jinput->get('transition_id', 0, 'int');
		$context = $jinput->getCmd('context', null);
		$comment = $jinput->get('comment', '', 'string');
	
		$sdata = array();
		$sdata['transition_id'] = $transition_id;
		$sdata['comment'] = $comment;
	
		$model = $this->getModel();
	
		$data = array();
		$data['success']	= true;
		$data['messages']	= array();
		$data['data'] 		= array();
	
		if (!$model->validateTransition($sdata)) {
			$data['success'] = false;
			$data['messages'][] = $model->getError();
			 
			$this->sendResponse($data);
		}
	
		$data['messages'][] = '';
	
		$this->sendResponse($data);
	
	}
	protected function allowTransition($data = array(), $key = 'id')
	{
		$id     = (int) isset($data[$key]) ? $data[$key] : 0;
		$transition_id = (int) isset($data['transition_id']) ? $data['transition_id'] : JFactory::getApplication()->input->get('transition_id', 0, 'int');
		$user   = isset($data['userr']) ? $data['user'] : JFactory::getUser();
		$instance	= isset($data['table']) ? $data['table'] : $this->getModel()->getTable();
		
		if (!$instance->load(array('context'=>$data['context'], 'item_id'=>$id)))
		{
			return false;
		}
	
		jimport('workflow.application.helper');
		$transitions = WFApplicationHelper::getTransitionsForInstanceUser($instance, $user);
		
		if (empty($transitions) || $transitions === false)
		{
			return false;
		}
		
		$allowed = false;
		foreach ($transitions as $transition )
		{
			if ($transition->id == $transition_id)
			{
				$allowed = true;
				break;
			}
		}
		return $allowed;
	}
	
	protected function sendResponse($data = null)
	{
		if (empty($data)) {
			$this->_result['data'] = $this->_data;
			$this->_result['success'] = $this->_success;
			$this->_result['messages'] = $this->_messages;
			
			parent::sendResponse($this->_result);
		}
		
		parent::sendResponse($data);
	}
}