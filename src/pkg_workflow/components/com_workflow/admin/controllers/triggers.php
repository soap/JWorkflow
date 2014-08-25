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
	
	public function remove()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
	
		$eid   = $this->input->get('cid', array(), 'array');
		$model = $this->getModel('trigger');
	
		JArrayHelper::toInteger($eid, array());
		$model->remove($eid);
		$this->setRedirect(JRoute::_('index.php?option=com_workflow&view=triggers', false));
	}	
}