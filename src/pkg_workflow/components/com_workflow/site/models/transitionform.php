<?php
defined('_JEXEC') or die;

JLoader::register('WorkflowModelTransition', JPATH_ADMINISTRATOR . '/components/com_workflow/models/transition.php');

class WorkflowModelTransitionForm extends WorkflowModelTransition
{

	/**
	 * 
	 * Validate workflow transition contraints (not content item)
	 * @param unknown_type $data
	 * @return boolean
	 */
	public function validateTransition($data = array()) 
	{
		if (empty($data)) return true;
		
		if ((int)$data['id'] > 0) {

			$item = $this->getItem($data['id']);
			// Check if we have anything to vaildate
			if (!isset($item->params) || !is_array($item->params)) return true;
			
			if (array_key_exists('comment_required', $item->params) && $item->params['comment_required']=='1') {
				if (trim($data['comment']) == '') {
					$msg = JText::sprintf("COM_WORKFLOW_FIELD_IS_REQUIRED", "comment", $item->title);
					
					$this->setError($msg);
					return false;
				}
			}
			
			return true;
		}
		else {
			$this->setError(JText::_('COM_WORKFLOW_ERROR_TRANSITION_CANNOT_LOAD'));
			return false;	
		}
		
		return true;
		
	}
}
