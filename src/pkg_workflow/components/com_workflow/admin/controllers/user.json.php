<?php
defined('_JEXEC') or die;

jimport('workflow.controller.form.json');

class WorkflowControllerUser extends WFControllerFormJson
{
	/**
	 * Overide getModel for different name than Controller Name
	 * @see JControllerForm::getModel()
	 */
	public function getModel($name = 'User', $prefix = 'WorkflowModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
	
	public function getUsers()
	{
		$input = JFactory::getApplication()->input;
		
		$search = $input->get('search');
		$limit = $input->getInt('limit', 10);
		$ids = $input->get('ids', array(), 'array');
		
		$userModel = $this->getModel();
		$users = $userModel->getUsers($ids, $search, $limit);
		
		$data = array();
		foreach($users as $user) {
			$obj = new StdClass();
			$obj->value = $user['id'];
			$obj->text 	= $user['name']; 	
			$data[] = $obj;
		}
		
		$this->sendResponse($data);
	}
	
	public function getGroups()
	{
		$search = $input->get('search');
		$limit = $input->getInt('limit', 10);
		$ids = $input->get('ids', array(), 'array');
		
		$userModel = $this->getModel();
		$groups = $userModel->getGroups($ids, $search, $limit);
		
		$rdata = array();
		$rdata['success']  = "true";
		$rdata['messages'] = array();
		$rdata['data']     = $groups;

		$this->sendResponse($rdata);
	}
	
}