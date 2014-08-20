<?php
defined('_JEXEC') or die;

JLoader::register('WorkflowModelTransition', JPATH_ADMINISTRATOR . '/components/com_workflow/models/transition.php');

class WorkflowModelTransitionForm extends WorklowModelTransition 
{

	public function validate($data = array()) 
	{
		if (empty($data)) return true;
		
		if ($data['id'] > 0) {

			$table = $this->getTable();
			$table->load($data['id']);

			if ($table->params->get('comment_required')) {
				if (trim($data['commnet']) == '') {
					$this->setError(JText::sprintf('COM_WORKFLOW_FIELD_IS_REQUIRED', 'comment'));
					return false;
				}
			}
			
			return true;
		}
		else {
			$this->setError(JTex::_('COM_WORKFLOW_TRANSITION_CANNOT_LOAD'));
			return false;	
		}
		
		return true;
		
	}
}
