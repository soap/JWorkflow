<?php
defined('_JEXEC') or die;

class WorkflowModelInstance extends JModelItem 
{
	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name.
	 * @param   array   $config  Configuration array for model.
	 *
	 * @return  JTable  A database object
	 * @since   ${SINCE}
	 */
	public function getTable($type = 'Instance', $prefix = 'WorkflowTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}
	
	public function getItem($context, $id)
	{
		if (empty($context) || empty($id)) {
			$this->setError('Incomplete input provided');
			return false;
		}
		
		$instance = $this->getTable();
		$pks = array('context'=>$context, 'item_id'=>(int)$id);
		
		if (!$instance->load($pks)) {
			return false;		
		}
		
		return $instance;
				
	}
	
	public function getWorkflowTransitions($context, $id)
	{
		$instance = $this->getItem($context, $id);

		$oUser = JFactory::getUser();
		$transitions = WFApplicationHelper::getTransitionsForInstanceUser($instance, $oUser);
		
		return $transitions;

	}
	
	public function getWorkflowState($context, $id) 
	{
		$instance = $this->getItem($context, $id);
		
		$oUser = JFactory::getUser();
		$state = WFApplicationHelper::getWorkflowStateForInstance($instance, array('type'=>'object'));
		
		return $state;		
	}
	
	public function makeTransition($data)
	{
		$transitionId = $data['transition_id'];
		$transition = WFApplicationHelper::getTransition($transitionId);
		
		$instance = $this->getTable();
		$instance->load(array('context'=> $data['context'], 'item_id'=>$data['item_id']));
		$user = isset($data['user']) ? $data['user'] : JFactory::getUser();
		$context = $data['context'];
		$comment = $data['comment'];
		
		if (!WFApplicationHelper::performTransitionOnInstance($transition, $instance, $user, $context, $comment))
		{
			$data['messages'][] =
			$this->setError(JText::_('COM_WORKFLOW_APPLICATION_ERROR_TRANSITION_FAILED'));
			
			return false;
		}
		
		return true;
	}
	
	public function getTransitionLogMessage($context, $item_id, $transition_id, $from_state_id=null) 
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		
		$query->select('title')->from('#__wf_transition_logs')
			->where('context='.$db->quote($context))
			->where('transition_id='.(int)$transition_id)
			//->where('from_state_id='.(int)$from_state_id)
			->where('item_id='.(int)$item_id)
			->order('created DESC');
		$db->setQuery($query);
		
		return $db->loadResult();
		
	} 
}