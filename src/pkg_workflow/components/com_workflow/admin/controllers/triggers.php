<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.controlleradmin');

/**
 * Guards Subcontroller.
 *
 * @package     Workflow
 * @subpackage  com_workflow
 * @since       1.0
 */
class WorkflowControllerTriggers extends JControllerAdmin
{
	/**
	 * Proxy for getModel.
	 * 
	 * @param   string  $name    The name of the model.
	 * @param   string  $prefix  The prefix for the model class name.
	 * @param   string  $config  The model configuration array.
	 *
	 * @return  WorkflowModelGuards	The model for the controller set to ignore the request.
	 * @since   1.6
	 */
	public function getModel($name = 'Trigger', $prefix = 'WorkflowModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
}