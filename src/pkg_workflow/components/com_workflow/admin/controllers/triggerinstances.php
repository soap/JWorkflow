<?php
defined('_JEXEC') or die;
/**
 * Triggerinstances Subcontroller.
 *
 * @package     JWorkflow
 * @subpackage  com_workflow
 * @since       1.0
 */
class WorkflowControllerTriggerinstances extends JControllerAdmin
{
	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  The prefix for the model class name.
	 * @param   string  $config  The model configuration array.
	 *
	 * @return  WorkflowModelBindings	The model for the controller set to ignore the request.
	 * @since   1.6
	 */
	public function getModel($name = 'Triggerinstance', $prefix = 'WorkflowModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
	
	/**
	 * Redirect user to transitions view
	 * @return boolean
	 */
	public function cancel()
	{
		$this->setRedirect('index.php?option=com_workflow&view=transitions');
		return true;
	}
}