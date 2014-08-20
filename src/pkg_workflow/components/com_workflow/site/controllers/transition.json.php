<?php
defined('_JEXEC') or die;

jimport('workflow.controller.json');

class WorkflowControllerTransition extends WFControllerFormJson
{
	
	/**
	 * Overide getModel for different name than Controller Name
	 * @see JControllerForm::getModel()
	 */
	public function getModel($name = 'TransitionForm', $prefix = 'WorkflowModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);		
	}
	
	/**
	 * 
	 * validate if transtion if requirement match before perform transition on work item
	 */
	public function validate()
	{   
		$jinput = JFactory::getApplication()->input;

        $transition_id   = $jinput->get('transition_id', 0, 'int');
        $comment = $jinput->get('comment', '', 'string');
        
        $sdata = array();
        $sdata['id'] = $transition_id;
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
}