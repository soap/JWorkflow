<?php
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Controller class provide simple workflow operation
 * @author Prasit Gebsaap
 */
class WFControllerForm extends JControllerForm 
{
	/**
	 * 
	 * Validate if request transition can be allowed to perform for the content item
	 * @param unknown_type $data
	 * @param unknown_type $key
	 */
	protected function allowTransition($data = array(), $key = 'id')
    {
    	$id     = (int) isset($data[$key]) ? $data[$key] : 0;
    	$transition_id = (int) isset($dta['transition_id']) ? $data['transition_id'] : JFactory::getApplication()->input->get('transition_id', 0, 'int');
        $user   = JFactory::getUser();
        $table	= isset($data['table']) ? $data['table'] : $this->getModel()->getTable();

        if (!$table->load($id)) 
        {
        	return false;	
        }

        jimport('workflow.application.helper');
        $transitions = WFApplicationHelper::getTransitionsForDocumentUser($table, $user);
		if (empty($transitions) || $transitions === false) 
		{
			return false;
		}
		
        $allowed = false;
        foreach ($transitions as $transition )
        {
        	if ($transition->id == $transition_id) 
        	{
        		$allowed = true;
        		break;
        	} 
        }
    	return $allowed;
    }
    
    /**
     * 
     * perform transition on the document item
     * @param unknown_type $key
     * @param unknown_type $url_var
     */
  	public function transition($key = null, $url_var = 'id')
   	{
   		// Initialise variables.
		$app = JFactory::getApplication();
		$model = $this->getModel();
		$table = $model->getTable();
		$cid = $app->input->get('cid', array(), 'array');
		$context = "$this->option.$this->context";

		// Determine the name of the primary key for the data.
		if (empty($key))
		{
			$key = $table->getKeyName();
		}

		// To avoid data collisions the urlVar may be different from the primary key.
		if (empty($urlVar))
		{
			$urlVar = $key;
		}

		// Get the previous record id (if any) and the current record id.
		$recordId = (int) (count($cid) ? $cid[0] : $app->input->get($urlVar, null, 'int'));
		$transitionId = (int) $app->input->get('transition_id', null, 'int');
		
		$data = array($key => $recordId, 'transition_id'=>$transitionId, 'table'=>$table);
		if (!$this->allowTransition($data, $key))
		{
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_TRANSITION_NOT_PERMITTED'));
			$this->setMessage($this->getError(), 'error');

			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);

			return false;
		}
		
		jimport('workflow.application.helper');
		
		$table->load($recordId);
		$user = JFactory::getUser();
		$comment = JFactory::getApplication()->input->get('comment', '', 'string');
		$transition = WFApplicationHelper::getTransition($transitionId);
		
		if (!WFApplicationHelper::performTransitionOnDocument($transition, $table, $user, $this->context, $comment))
		{
			$this->setRedirect(
				JRoute::_(
					'index.php?option='.$this->option.'&view='.$this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);	
		}
		
		$this->setRedirect(
			JRoute::_(
				'index.php?option='.$this->option.'&view='.$this->view_list
				. $this->getRedirectToListAppend(), false
			)
		);	
		
		return true;
	
   	}		
}