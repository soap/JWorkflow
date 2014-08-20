<?php
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * State Subcontroller.
 *
 * @package     Workflow
 * @subpackage  com_workflow
 * @since       1.0
 */
class WorkflowControllerTransition extends JControllerForm
{
	
	public function triggers($key = null, $urlVar = null) 
	{
		$recordId = JFactory::getApplication()->input->get('id', null, 'int');
		// Redirect to the edit screen.
		$this->setRedirect(
			JRoute::_(
				'index.php?option=' . $this->option . '&view=triggers'
				. $this->getRedirectToTriggersAppend($recordId), false
			)
		);

		return true;		
	}
	
	protected function getRedirectToTriggersAppend($recordId=null, $urlVar='filter_transition_id') 
	{
		$tmpl   = JRequest::getCmd('tmpl');
		$layout = JRequest::getCmd('layout', 'default');
		$append = '';

		$type = JRequest::getCmd('filter_type', 'guard');
		
		// Setup redirect info.
		if ($tmpl)
		{
			$append .= '&tmpl=' . $tmpl;
		}

		if ($layout)
		{
			$append .= '&layout=' . $layout;
		}

		if ($recordId)
		{
			$append .= '&' . $urlVar . '=' . $recordId;
		}
		
		$append .= '&filter_type='.$type;
		
		return $append;		
	}
}