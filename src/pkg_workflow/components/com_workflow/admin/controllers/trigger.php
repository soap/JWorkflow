<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.controllerform');

/**
 * Guard Subcontroller.
 *
 * @package     Workflow
 * @subpackage  com_workflow
 * @since       1.0
 */
class WorkflowControllerTrigger extends JControllerForm
{
	
	protected function getRedirectToItemAppend($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);
		if ($this->getTask()=='add') 
		{
			$transition_id = JFactory::getApplication()->input->get('transition_id', '', 'int');
			$append .= '&transition_id='.$transition_id;	
		}
		
		return $append;
	}
}