<?php
/**
 * Workflow utility class to be used by component developer
 * @author Prasit Gebsaap
 * @version 1.5.1 RC1 Dated: 2014-08-31
 * @copyright 2010-2015 by Prasit Gebsaap
 * @internal workflow state stored in JWorkflow table instead of working since version 1.5.0
 */
// no direct access
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.database.table');

JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_workflow/tables');

class WFApplicationHelper {
    
	protected static $components;

	protected static $workflow;
	
	protected static $transitions;
	
	protected static $states;
	protected static $fromstates;
	
    /**
     * Method to get all workflow related components and plugins
     *
     * @return    array
     */
    public static function getComponents()
    {
        if (is_array(self::$components)) {
            return self::$components;
        }

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('extension_id, element, client_id, enabled, access, protected')
              ->from('#__extensions')
              ->where($db->qn('type') . ' = ' . $db->quote('component'))
              ->where('(' . $db->qn('element') . ' = '  . $db->quote('com_workflow')
                . ' OR ' . $db->qn('element') . ' LIKE ' . $db->quote('com_wf%')
                . ')'
              )
              ->order('extension_id ASC');

        $db->setQuery($query);
        $items = (array) $db->loadObjectList();
        $com   = array();

        foreach ($items AS $item)
        {
            $el = $item->element;

            $com[$el] = $item;
        }

        self::$components = $com;

        return self::$components;
    }
    	
    /**
     * Method to check if a component exists or not
     *
     * @param     string     $name    The name of the component
     *
     * @return    boolean
     */
    public static function exists($name)
    {
        $components = self::getComponents();

        if (!array_key_exists($name, $components)) {
            return false;
        }

        return true;
    }


    /**
     * Sets the currently active workflow for the user.
     * The active workflow serves as a global data filter.
     *
     * @param     int        $id    The workflow id
     *
     * @return    boolean           True on success, False on error
     **/
    public static function setActiveWorkflow($id = 0)
    {
    	static $model = null;
    
    	if ($id == self::getActiveWorkflowId()) return true;
    
    	if (is_null($model)) {
    		// We did not use in Site yet
    		$name   = (JFactory::getApplication()->isSite() ? 'Form' : 'Workflow');
    		$config = array('ignore_request' => true);
    		$model  = JModelLegacy::getInstance($name, 'WorkflowModel', $config);
    	}
    
    	$result = $model->setActive(array('id' => (int) $id));
    
    	if (!$result) {
    		JFactory::getApplication()->enqueueMessage($model->getError(), 'error');
    	}
    	else {
    		if ($id) {
    			$title = self::getActiveWorkflowTitle();
    			$msg   = JText::sprintf('COM_WORLFLOW_INFO_NEW_ACTIVE_WORKFLOW', '"' . $title . '"');
    			JFactory::getApplication()->enqueueMessage($msg);
    		}
    	}
    
    	return $result;
    }
    
    
    /**
     * Returns the currently active workflow ID of the user.
     *
     * @param     string    $request    The name of the variable passed in a request.
     *
     * @return    int                   The workflow id
     **/
    public static function getActiveWorkflowId($request = null)
    {
    	$key     = 'com_workflow.workflow.active.id';
    	$current = JFactory::getApplication()->getUserState($key);
    	$current = (is_null($current) ? '' : $current);
    
    	if (empty($request)) return $current;
		
    	$request = JFactory::getApplication()->input->get($request, null);
		
    	if (!is_null($request) && self::setActiveWorkflow((int) $request)) {
    		$current = is_numeric($request) ? $request : (int) $request;
    	}
    
    	return $current;
    }
    
    
    /**
     * Returns the currently active workflow title of the user.
     *
     * @param     string    $alt    Alternative value of no workflow is set
     *
     * @return    string            The workflow title
     **/
    public static function getActiveWorkflowTitle($alt = '')
    {
    	if ($alt) $alt = JText::_($alt);
    
    	$title = JFactory::getApplication()->getUserState('com_workflow.workflow.active.title', $alt);
    
    	return $title;
    }
    /**
     * 
     * @param unknown $oDocument
     * @param unknown $oUser
     * @param boolean $includeBlocked
     * @deprecated use getTransitionsForInstanceUser
     */
    public static function getTransitionsForDocumentUser($oDocument, $oUser, $includeBlocked = false)
    {
    	self::getTransitionsForInstanceUser($oDocument, $oUser, $includeBlocked);
    }
    /**
     * Gets the transitions that are available for a document by virtue
     * of its workflow state, and also by virtue of the user that wishes
     * to perform the transition.
     *
     * In other words, ensures that the guard permission, role, group,
     * and/or user are met for the given user.
     * @internal tested on 2010-03-19
     */
    public static function getTransitionsForInstanceUser($oInstance, $oUser, $includeBlocked = false) 
    {
        $oState = self::getWorkflowStateForInstance($oInstance);
        if (is_null($oState) || JError::isError($oState)) {
        	
            return $oState;
        }
        
        $aTransitions = self::getTransitionsFrom($oState);    
        $oDocument = self::getDocument($oInstance);
        
        $aEnabledTransitions = array();
        foreach ($aTransitions as $oTransition) 
        {
        	// validate against Joomla ACL first
        	$allowed = self::allowUser($oTransition->id, $oUser->get('id'));
        	if (!$allowed) 
        	{
        		if ($includeBlocked) {
        			$oTransition->blocked = true;
        			$oTransition->explain = JText::_('COM_WORKFLOW_WARNING_NO_PERMISSION');
        			$aEnabledTransitions[] = $oTransition;
        		}
        		continue;
        	}
        	
        	// Continue check on guards
            $aGuardTriggers = self::getGuardTriggersForTransition($oTransition);
            if (JError::isError($aGuardTriggers)) 
            {
                return $aGuardTriggers; // error out?
            }
            

            if ( is_array($aGuardTriggers) && (count($aGuardTriggers)> 0) ) {
            	$blocked = false;
            	foreach ($aGuardTriggers as $oTrigger) {
            		$explain = '';
                	if (!$oTrigger->allowTransition($oInstance, $oDocument, $oUser)) 
                	{
                    	//if only one guard not allow, guards this transition
                    	$blocked = true;
                    	$explain = $oTrigger->getExplain();
                    	break;
                	}
            	}
            	
            	if ($blocked) {
            		if ($includeBlocked) {
            			$oTransition->blocked = true;
            			$oTransition->explain = $explain;
            			$aEnabledTransitions[] = $oTransition;				
            		}
            		// Continue checking for next transition 
            		continue; 
            	}
            }
            $oTransition->blocked = false;
            $oTransition->explain = '';
            $aEnabledTransitions[] = $oTransition;
        }
        
        return $aEnabledTransitions;
    }
    
    /**
     * 
     * @param unknown $oDocument
     * @param unknown $aOptions
     * @deprecated version 1.5.0 use getWorkflowStateForInstance instead
     */
    public static function getWorkflowStateForDocument($oDocument, $aOptions = array())
    {
    	self::getWorkflowStateForInstance($oDocument, $aOptions);	
    }
    
    /**
     * Gets the workflow state that applies to the given instance,
     * returning null if there is no workflow assigned.
     * if options contains ids then only id will be returned.
     * @sinc 1.5.0
     * @internal verified on 2013-07-16, not test yet 
     */
    public static function getWorkflowStateForInstance ($oInstance, $aOptions = array()) 
    {
    	$type = 'object';
        if (array_key_exists('type', $aOptions)) {
        	$type = $aOptions['type'];
        }
		if (!isset($oInstance->workflow_state_id)) {
			return false;
		}
        $iWorkflowStateId = $oInstance->workflow_state_id;

        if (JError::isError($iWorkflowStateId)) {
            return $iWorkflowStateId;
        }

        if (is_null($iWorkflowStateId)) {
            return $iWorkflowStateId;
        }

        if ($type !== 'object') {
            return $iWorkflowStateId;
        }

        JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_workflow/tables');
        $oWorkflowState = JTable::getInstance('State', 'WorkflowTable');

        if ($oWorkflowState === false) {
        	JError::raiseWarning(0, 'LIB_WORKFLOW_ERROR_WORKFLOW_SATE_TABLE_LOADING');
        	return false;
        }
        
        $oWorkflowState->load($iWorkflowStateId);
        
        return $oWorkflowState;
    }
    
    /**
     * Gets which workflow transitions are available to be chosen from
     * this workflow state.
     *
     * Workflow transitions have only destination workflow states, and
     * it is up to the workflow state to decide which workflow
     * transitions it wants to allow to leave its state.
     *
     * This function optionally will return the database id numbers of
     * the workflow transitions using the 'ids' option.
     * @internal tested on 2010-03-19
     */
    protected static function getTransitionsFrom($oState, $aOptions = array()) 
    {
    	$type = 'object';
        if (array_key_exists( 'type', $aOptions)) {
        	$type = $aOptions['type'];
        }
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $query->select('transition_id')
        		->from('#__wf_state_transitions')
        		->where('state_id = '.$oState->id);
        	
        $db->setQuery($query);
        $aTransitionIds = $db->loadColumn();
        
        if (JError::isError($aTransitionIds)) {
            return $aTransitionIds;
        }
        if ($type == 'id') {
            return $aTransitionIds;
        }
        
        $aRet = array();
        
        $oTransition = JTable::getInstance('Transition', 'WorkflowTable');
        foreach ($aTransitionIds as $iId) {
            $oTransition->load($iId);
            $aRet[] = clone $oTransition;
            
        }
        return $aRet;
    }    
    
    public static function getTransition($transitionId) 
    {
    	$transition = JTable::getInstance('Transition', 'WorkflowTable');
    	$transition->load($transitionId);

    	return $transition;
    }
    
    /** 
     * Retrieves the guard triggers for a given transition in their WorkflowTrigger form.
     * @internal tested on 2010-03-19
     */
    public static function getTriggersForTransition($oTransition) {
        $oTriggerRegistry =  WFTriggerRegistry::getInstance();
        $oTriggerRegistry->loadWorkflowTriggers();
        
        /* -------------------------------------*/
        $aTriggers = array(); 
        $aGuards= self::getGuardsByTransition($oTransition->id); 
        foreach ($aGuards as $oGuard) {
            $oTrigger = $oTriggerRegistry->getWorkflowTrigger($oGuard->get('namespace'));

            if (empty($oTrigger) || $oTrigger === false) {
                continue;
            }
            $oTrigger->loadConfig($oGuard->trigger_config);
            $aTriggers[] = $oTrigger;
        }
        
        return $aTriggers;
    }
    
    protected static function getGuardsByTransition($transitionId)
    {
    	if (empty($transitionId)) return array();
    	
    	$db = JFactory::getDbo();
    	$query = $db->getQuery(true);
    	$query->select('id, namespace')
    		->from('#__wf_trigger_instances')
    		->where('transition_id = '.$transitionId);
    	
    	$app = JFactory::getApplication();
    	$option = $app->input->getCmd('option');
    	if ($app->isAdmin() && $option != 'com_workflow'){
    		$query->where('published = 1');
    	}
     
       	$db->setQuery($query);
       	$aObjects = $db->loadObjectList();
       	
       	$guards = array();
       	if (is_array($aObjects)){   
            foreach ($aObjects as $object){
                $guard = JTable::getInstance('Triggerinstance', 'WorkflowTable');
                $guard->load($object->id);
                $guards[] = clone $guard;
            }
       	}

       	return $guards;     	
       
    }
    /**
     * Gets guard triggers for specified transition object
     */
    protected static function getGuardTriggersForTransition($oTransition) {
        $aTriggers = self::getTriggersForTransition($oTransition);

        if ( empty($aTriggers) || $aTriggers === false ) {
            return $aTriggers;
        }
        $aGuards = array();
        foreach ($aTriggers as $oTrigger) {
            $aInfo = $oTrigger->getInfo();
            if ($aInfo['type'] == 'guard') {
                $aGuards[] = $oTrigger;
            }
        }
        
        return $aGuards;
    }

    /**
     * Gets action triggers for specified transition object
     */
    protected static function getActionTriggersForTransition($oTransition) {
        $aTriggers =  self::getTriggersForTransition($oTransition);
        if (JError::isError($aTriggers)) {
            return $aTriggers;
        }
        $aActions = array();
        foreach ($aTriggers as $oTrigger) {
            $aInfo = $oTrigger->getInfo();
            if ($aInfo['type'] == 'action') {
                $aActions[] = $oTrigger;
            }
        }
        return $aActions;
    }
    
    /** 
     * Retrieves the triggers for a given workflow id in their WorkflowTrigger form.
     */
    static function getTriggersForWorkflow($iWorkflowId) {
        $oCPWorkflowTriggerRegistry = & WorkflowTriggerRegistry::getInstance();
        /* FIXME: Who should call this function */ 
        self::loadWorkflowTriggers();
        /* -------------------------------------*/
        $aTriggers = array();
        $aTriggerInstances = TableTriggerinstance::getByWorkflowId($iWorkflowId);
        
        foreach ($aTriggerInstances as $oTriggerInstance) {
            $oTrigger = $oCPWorkflowTriggerRegistry->getWorkflowTrigger($oTriggerInstance->getNamespace());
            if (JError::isError($oTrigger)) {
                return $oTrigger;
            }
            $oTrigger->loadConfig($oTriggerInstance);
            $aTriggers[] = $oTrigger;
        }
        return $aTriggers;
    }
    
    public static function makeTransition($context, $item_id, $transitionId, $comment)
    {
    	$oTransition = self::getTransition($transitionId);
    	$oInstance = self::getWorkflowInstance($context, $item_id);
    
    	$fromState = $oInstance->workflow_state_id;
    	$user = JFactory::getUser();
    	
    	if (!WFApplicationHelper::performTransitionOnInstance($oTransition, $oInstance, $user, $context, $comment))
    	{
    		$this->setError(JText::_('COM_WORKFLOW_APPLICATION_ERROR_TRANSITION_FAILED'));
    		return false;
    	}
    	
    	return true;
    }
    
    /**
     * 
     * @param string $context
     * @param integer $item_id
     * @return mixed
     */
    protected static function getWorkflowInstance($context, $item_id)
    {
    	$instance = JTable::getInstance('Instance', 'WorkflowTable');
    	$instance->load(array('context'=> $context, 'item_id'=>$item_id));
    	if ($instance === false) {
    		return false;	
    	}
    	
    	return $instance;
    }
    
    
    /**
     * 
     * @param unknown $oTransition
     * @param unknown $oInstance
     * @param unknown $oUser
     * @param string $context
     * @param string $comment
     * @throws Exception
     * @return unknown
     */
    
    public static function performTransitionOnInstance($oTransition, $oInstance, $oUser,  $context='', $comment='')
    {
    	$oWorkflow = JTable::getInstance('Workflow', 'WorkflowTable');
    	$oWorkflow->load($oInstance->workflow_id);
    	if (empty($oWorkflow))
    	{
    		JError::raiseError(500, JText::_('COM_WORKFLOW_ERROR_DOCUMENT_NOTHAVE_WORKFLOW'));
    	}
    	if (JError::isError($oWorkflow))
    	{
    		return $oWorkflow; // return JException ?
    	}
    	
    	$oDocument = self::getDocument($oInstance);
    	if ($oDocument === false) {
    		JLog::add('Cannot load document object', JLog::INFO, 'jworkflow');
    		return false;	
    	}
    	
    	//JLog::add('Document class is '.get_class($oDocument), JLog::INFO, 'jworkflow');
    	
    	// walk the action triggers.
    	$aActionTriggers = self::getActionTriggersForTransition($oTransition);
    	if (JError::isError($aActionTriggers)) {
    		return $aActionTriggers; // error out?
    	}
    	
    	foreach ($aActionTriggers as $oTrigger) {
    		$res = $oTrigger->precheckTransition($oInstance, $oDocument, $oUser);
    		if (JError::isError($res)) {
    			return $res;
    		}
    	}
    	
    	$oSourceState = self::getWorkflowStateForInstance($oInstance);
    	$iStateId = $oTransition->getTargetStateId();
    	
    	$oTargetState = JTable::getInstance('State', 'WorkflowTable');
    	$oTargetState->load($iStateId);
    	
    	// Import workflow plugin
    	JPluginHelper::importPlugin('workflow');
    	// Get the dispatcher.
    	$dispatcher = JDispatcher::getInstance();
    	$results = $dispatcher->trigger('onWorkflowBeforeTransition',
    			array($context, $oInstance, $oUser, $comment, $oTransition, $oSourceState, $oTargetState)
    	);
    	
    	// Check for errors encountered while
    	if (count($results) && in_array(false, $results, true))
    	{
    		// Get the last error.
    		$error = $dispatcher->getError();
    	
    		if (!($error instanceof Exception))
    		{
    			throw new Exception($error);
    		}
    	}
    	
    	$oInstance->workflow_state_id = $iStateId;
    	$res = $oInstance->store(); //save to database
    	if (JError::isError($res))
    	{
    		return $res;
    	}
    	
    	$results = $dispatcher->trigger('onWorkflowAfterTransition',
    			array($context, $oInstance, $oUser, $comment, $oTransition, $oSourceState, $oTargetState)
    	);
    	
    	// Check for errors encountered while
    	if (count($results) && in_array(false, $results, true))
    	{
    		// Get the last error.
    		$error = $dispatcher->getError();
    	
    		if (!($error instanceof Exception))
    		{
    			throw new Exception($error);
    		}
    	}
    	
    	if (!empty($context))
    	{
    		$sSourceName = $oSourceState->get('title');
    		$sTargetName = $oTargetState->get('title');
    		$sTitle = JText::sprintf('COM_WORKFLOW_LOG_TRANSITION_CHANGED', $sSourceName, $sTargetName );
    		$date	= JFactory::getDate()->toSql();
    		$data = array(
    				'id'			=> 	null,
    				'context'		=>	$context,
    				'item_id'		=> 	$oInstance->item_id,
    				'comment'		=> 	$comment,
    				'title'			=>	$sTitle,
    				'from_state_id'	=>	$oSourceState->get('id'),
    				'transition_id'	=>	$oTransition->get('id'),
    				'created_by' 	=>  $oUser->id, 
    				'created' 		=>  $date
    		);
    		
			JLog::add($context.'.'.$oInstance->item_id .', '.$oUser->get('name').', '.$sTitle. ', '.$comment, JLog::DEBUG, 'jworkflow');
    		$oTransitionLog = JTable::getInstance('Transitionlog', 'WorkflowTable');
    		$oTransitionLog->reset();
    		$oTransitionLog->bind($data);
    		if ($oTransitionLog->check()) {
    			$oTransitionLog->store();
    		}else{
    			 
    		}
    		//JLog::add('comment is '.$data['comment'], JLog::DEBUG, 'jworkflow');
    	}
    	
    	self::emailAfterTransition($context, $oInstance, $oTransition, $oSourceState, $oTargetState, $oDocument, $oUser, $comment);
    	// walk through the action triggers.
    	JLog::add(sprintf('Walk through action %d triggers', count($aActionTriggers)), JLog::INFO, 'jworkflow');
    	foreach ($aActionTriggers as $oTrigger) {
    		$res = $oTrigger->afterTransition($oInstance, $oDocument, $oUser);
    		if (JError::isError($res)) {
    			return $res;
    		}
    	}
    	
    	/* Create work item to list waiting object for user */
    	/* Clear existing todo items for groups and users from start state and re-create */
    	self::updateWaitingItems( $context, $oInstance, $oUser );
    	
    	self::performSystemActionOnInstance( $oInstance, $context );
    	return true;    	
    }
    
    /**
     * Peforms a workflow transition on a document, changing it from one workflow state to another state,
     * with possible side effects (user scripts, plugins and so forth)
     * 
     * We assume that user in question is allowed to perform the transition 
     * and that all guard functionality on the transition has passed.
     * @param object Transition to perform
     * @param object Document to work with
     * @param object User who perform the transition
     * @param string His/Her comment when perform the transition
     * @param string context to be insert in log database
     * @deprecated sinc 1.5.0, see performTransitionOnInstance
     */
    public static function performTransitionOnDocument($oTransition, $oDocument, $oUser,  $context='', $comment='')
    {
        $oWorkflow = JTable::getInstance('Workflow', 'WorkflowTable');
        $oWorkflow->load($oDocument->workflow_id);
        if (empty($oWorkflow))
        {
            JError::raiseError(500, JText::_('COM_WORKFLOW_ERROR_DOCUMENT_NOTHAVE_WORKFLOW'));
        }
        if (JError::isError($oWorkflow))
        {
            return $oWorkflow; // return JException ?
        }
        
        // walk the action triggers.
        $aActionTriggers = WFApplicationHelper::getActionTriggersForTransition($oTransition);
        if (JError::isError($aActionTriggers)) {
            return $aActionTriggers; // error out?
        }
        foreach ($aActionTriggers as $oTrigger) {
            $res = $oTrigger->precheckTransition($oDocument, $oUser);
            if (JError::isError($res)) {
                return $res;
            }
        }
        
        $oSourceState = WFApplicationHelper::getWorkflowStateForDocument($oDocument);
        $iStateId = $oTransition->getTargetStateId();
        
        $oTargetState = JTable::getInstance('State', 'WorkflowTable');
        $oTargetState->load($iStateId);
        
        // Import workflow plugin
        JPluginHelper::importPlugin('workflow');
        // Get the dispatcher.
		$dispatcher = JEventDispatcher::getInstance();
        $results = $dispatcher->trigger('onWorkflowBeforeTransition', 
        		array($context, $oDocument, $oUser, $comment, $oTransition, $oSourceState, $oTargetState)
        	);
       	
        // Check for errors encountered while 
		if (count($results) && in_array(false, $results, true))
		{
			// Get the last error.
			$error = $dispatcher->getError();

			if (!($error instanceof Exception))
			{
				throw new Exception($error);
			}
		}	
		
        $oDocument->workflow_state_id = $iStateId;
        $res = $oDocument->store(); //save to database
        if (JError::isError($res))
        {
            return $res;
        }
        
        $results = $dispatcher->trigger('onWorkflowAfterTransition', 
        		array($context, $oDocument, $oUser, $comment, $oTransition, $oSourceState, $oTargetState)
        	);
       	
        // Check for errors encountered while 
		if (count($results) && in_array(false, $results, true))
		{
			// Get the last error.
			$error = $dispatcher->getError();

			if (!($error instanceof Exception))
			{
				throw new Exception($error);
			}
		}	
		
        if (!empty($context))
        {
        	$sSourceName = $oSourceState->get('title');
        	$sTargetName = $oTargetState->get('title');
        	$sTitle = JText::sprintf('COM_WORKFLOW_LOG_TRANSITION_CHANGED', $sSourceName, $sTargetName );
        	
        	$data = array(
        		'id'		=> 	0,
        		'context'	=>	$context,
        		'item_id'	=> 	$oDocument->id,
        		'title'		=>	$sTitle,
        		'comment'	=>	$comment,
        		'from_state_id'	=>	$oSourceState->get('id'),
        		'transition_id'	=>	$oTransition->get('id'),
        		'created_by' => $oUser->id 
        	);
       	    $oTransitionLog = JTable::getInstance('Transitionlog', 'WorkflowTable');
       	    $oTransitionLog->reset();
       	    $oTransitionLog->bind($data);
       	    if ($oTransitionLog->check()) {
       	    	$oTransitionLog->store();
       	    }else{
       	    	
       	    }    	
        }

        // walk the action triggers.
        foreach ($aActionTriggers as $oTrigger) {
            $res = $oTrigger->afterTransition($oDocument, $oUser);
            if (JError::isError($res)) {
                return $res;
            }
        }
        
        /* Create work item to list waiting object for user */ 
        /* Clear existing todo items for groups and users from start state and re-create fro the target state */
        //self::updateWaitingItems( $oDocument );
        
        self::performSystemActionOnDocument( $oDocument, $context );
        return true;
            
    }
    
    /**
     * 
     * @param unknown $oDocument
     * @param unknown $context
     * @deprecated since 1.5 see performSystemActionOnInstance
     */
    static function performSystemActionOnDocument( $oDocument, $context ) 
    {
    	self::performSystemActionOnInstance($oDocument, $context);	
    }
    
    /**
     * Check if document is waiting for system action.
     * If yes, action on the document
     * @param object Document to be processed
     */
    static function performSystemActionOnInstance( $oInstance, $context ) {
        /* Check if document is waiting for system action */
        if (!self::isWaitingForSystem($oInstance)) return true; 
        /* Get available system transitions */
        $transitions = self::getTransitionsForInstanceUser($oInstance, null);
        $selected_transition = null;
        foreach ($transitions as $transition ) {
            if ($transition->system_path == 1) {
                $selected_transition = $transition;
                break;
            }
        }
        
        /* Perform transition on document */
        if (!empty($selected_transition)) {
            return self::performTransitionOnInstance($transition, $oInstance, null, context);
        }
        
        return true;    
    }  
    
    protected static function isWaitingForSystem() 
    {
    	return false;	
    }
    
    protected static function updateWaitingItems( $context, $oInstance, $oUser )
    {
    	$dbo = JFactory::getDbo();
    	$query = $dbo->getQuery(true);

    	$query->delete('#__wf_waiting_items')
    		->where('context='.$dbo->quote($context))
    		->where('item_id='.(int)$oInstance->item_id);
    	
    	$dbo->setQuery($query);
    	@$dbo->execute();
    	
    	$oState = self::getWorkflowStateForInstance($oInstance);
    	$transitions = self::getTransitionsFrom($oState, array('type'=>'id'));
    	
    	if (empty($transitions)) return false;
    	
    	$query->clear();
    	$query->select('permission_context, item_id')
			->from('#__wf_transition_permissions')
			->where('transition_id IN ('.join(',',$transitions).')')
    		->group('permission_context', 'item_id');
    	$dbo->setQuery($query);
    	$rows = $dbo->loadObjectList();
		
    	if (empty($rows)) return false;
		
    	foreach($rows as $row) {
    		$obj = new StdClass();
    		$obj->context = $context;
    		$obj->item_id = $oInstance->item_id;
    		$obj->role_type = $row->permission_context;
    		$obj->role_id = $row->item_id;
    		$obj->created = JDate::getInstance()->toSql();
    		$obj->created_by = $oUser->get('id');

    		$dbo->insertObject('#__wf_waiting_items', $obj);
    	}
    	
    	return true;
    } 
    
    /**
     * Get the document object based on parameters provided in binding information
     * @param unknown $oInstance
     * @return boolean| JTable descendant object
     * @since 3.0 (2015-01-01)
     * @internal Tested and verified again on 2015-09-14
     */
    protected static function getDocument($oInstance)
    {
    	$oBinding = JTable::getInstance('Binding', 'WorkflowTable');
    	if (!$oBinding->load($oInstance->binding_id)) {
    		JLog::add('Method: '.__METHOD__.', cannot load binding data from workflow instance', JLog::ERROR, 'jworkflow');
    		return false;
    	}
    		
    	$oResult = false;
    	if ( isset($oBinding->params) && !empty($oBinding->params)) {
    		$oBinding->params = new JRegistry($oBinding->params);
    		$path = $oBinding->params->get('table_path');
    		$prefix = $oBinding->params->get('table_prefix');
    		$name = $oBinding->params->get('table_name');
    		
    		JLog::add('Path: '.$path.' Prefix: '.$prefix.' Name: '.$name, JLog::INFO, 'jworkflow');
    		
    		if (!empty($path)) {
    			$path = JPATH_ADMINISTRATOR.'/'.$path;
    			JLog::add('Path: '.$path.' Prefix: '.$prefix.' Name: '.$name, JLog::INFO, 'jworkflow');
    			JTable::addIncludePath($path);
    		}
    		
    		$oResult = JTable::getInstance($name, $prefix);
    		
    		if ($oResult === false) {
    			// If we were unable to find the class file in the JTable include paths, raise a warning and return false.
    			JLog::add(JText::sprintf('COM_WORKFLOW_ERROR_LOADING_DOCUMENT_CLASS', $prefix.$name), JLog::WARNING, 'jworkflow');
    			return false;
    		}
    		
    		JLog::add('Try to load document object: '.$prefix.$name.$oInstance->item_id, JLog::WARNING, 'jworkflow');
    		// Try to load registry class
    		$success = $oResult->load($oInstance->item_id);
    		
    		if ($success) {

				if (isset($oResult->attribs) &&  !($oResult->attribs instanceof JRegistry)) {
    				$oResult->attribs = new JRegistry($oResult->attribs);
    			}
    		
    			if (isset($oResult->params) && !($oResult->params instanceof JRegistry)) {
    				$oResult->params = new JRegistry($oResult->params);
    			}
    		}else{
    			JLog::add(JText::sprintf('COM_WORKFLOW_ERROR_LOADING_DOCUMENT_OBJECT', $prefix.$name.$oInstance->item_id), JLog::WARNING, 'jworkflow');
    		}
    	}else{
    		JLog::add(JText::_('COM_WORKFLOW_ERROR_NO_BINDING_PARAMS'), JLog::WARNING, 'jworkflow');
    	}

    	return $oResult;
    } 
    
    /**
     * 
     * @param unknown $conetext
     * @param unknown $oTransition
     * @param unknown $oSourceState
     * @param unknown $oTargetState
     * @param unknown $oDocument
     * @param unknown $oUser
     * @param unknown $comment
     */
    protected static function emailAfterTransition($conetext, $oInstance, $oTransition, $oSourceState, $oTargetState, $oDocument, $oUser, $comment)
    {
    	jimport('workflow.mail.mail');
    	
    	$config = JFactory::getConfig();
    	$sender = array(
    			$config->get( 'mailfrom' ),
    			$config->get( 'fromname' )
    	);
    	//get author and owner 's e-mail
    	$oBinding = JTable::getInstance('Binding', 'WorkflowTable');
    	$oBinding->load($oInstance->binding_id);
    	$bindingParams = new JRegistry($oBinding->params);
    	
    	$authorField = $bindingParams->get('author_field');
    	$ownerField = $bindingParams->get('owner_field');
    	
    	$transitionParams = $oTransition->params;
    	if (!($transitionParams instanceof JRegistry)) {
    		$transitionParams = new JRegistry($transitionParams);
    	}
    	
    	$notifyAuthor 		= (bool)$transitionParams->get('notify_author');
    	$notifyOwner 		= (bool)$transitionParams->get('notify_owner');
    	$notifyActor 		= (bool)$transitionParams->get('notify_actor');

    	$reciepts = array();
    	$reciepts['authors'] = array();
    	$reciepts['actor'] = array();
    	$reciepts['next_actors'] = array();
    	
    	if ($notifyAuthor && !empty($authorField) && (isset($oDocument->$authorField))) {
    		$reciepts['authors'][] = JFactory::getUser($oDocument->$authorField)->email;
    	}
    	
    	if ($notifyOwner && !empty($ownerField) && (isset($oDocument->$ownerField))) {
    		$reciepts['authors'][] = JFactory::getUser($oDocument->$ownerField)->email;
    	}
    	
    	if ($notifyActors && $oUser instanceof JUser) {
    		$reciepts['actor'][] = $oUser->email;
    	}
    
  	
   	
    	list($basePath, $context) = explode('.', $oBinding->context);
    	$basePath = JPATH_ROOT.'/components/'.$basePath.'/layouts';

    	// Get data for e-mail template
    	require_once($basePath.'/emails/layoutdata.php');
    	// Pass data to class to prepare variables
    	$layoutData = new RFLayoutData($oInstance, $oTransition, $oSourceState, $oTargetState, $oDocument, $oUser, $comment);
    	if ($notifyAuthor || $notifyOwner) {
    		
    		// E-mail template 
    		$notifyAuthorEmailTemplate = new JLayoutFile('emails.notify_author_html', $basePath);
    		$recieptEmails = $reciepts['authors'];
    		$ccEmails = array();
    		$bccEmails = array();
    		
    		
    		$displayData = $layoutData->getDisplayData('notify_actor');
    		$replacements = $layoutData->getReplacementPairs('notify_actor');
    		
    		$mailer = WFMail::getInstance();
    		$body = $notifyAuthorEmailTemplate->render($displayData);
    		$mailer->setReplacements($replacements);
    		$mailer->setSender($sender);
    		$mailer->addRecipient($reciepts['authors']);
    		$mailer->setSubject(JText::sprintf('COM_WORKFLOW_EMAIL_SUBJECT_NOTIFY_AUTHOR', $oTransition->name, $oUser->name));
    		$mailer->setBody($body);
    		$mailer->IsHtml(true);
    		
    		if (!$mailer->send()) {
    			return false;
    		}
    		
    	}
    	
    	if ($notifyActors && count($reciepts['actor'])) {
    		$notifyActorEmailTemplate = new JLayoutFile('emails.notify_actor', $basePath);
    		
    		$receiptEmails = array($reciepts['actor']);
    		$ccEmails = array();
    		$bccEmails = array();
    		$displayData = array(); 
    		$replacements = array();
    		
    		$mailer = WFMail::getInstance();
    		$body = $notifyActorEmailTemplate->render($displayData);
    		$mailer->setReplacements($replacements);
    		$mailer->setSender($sender);
    		$mailer->addRecipient($reciepts['actor']);
    		$mailer->setSubject(JText::_('COM_JONGMAN_EMAIL_SUBJECT_NOTIFY_ACTOR'));
    		$mailer->setBody($body);
    		$mailer->IsHtml(true);
    		
    		if (!$mailer->send()) {
    			return false;
    		}
    		
    	}

    	if (count($reciepts['next_actors'])) {
    		// We always notify next actors
    		$notifyNextActorsEmailTemplate = new JLayoutFile('emails.notify_next_actors', $basePath);
    	
    		$recieptEmails = array();
    		$ccEmails = array();
    		$bccEmails = array();
    		$displayData = array();
    		$replacements = array();
    	
    		$mailer = WFMail::getInstance();
    		$body = $notifyNextActorsEmailTemplate->render($displayData);
    		$mailer->setReplacements($replacements);
    		$mailer->setSender($sender);
    		$mailer->addRecipient($reciepts['next_actors']);
    		$mailer->setSubject(JText::_('COM_JONGMAN_EMAIL_SUBJECT_NOTIFY_NEXT_ACTORS'));
    		$mailer->setBody($body);
    		$mailer->IsHtml(true);
    	
    		if (!$mailer->send()) {
    			return false;
    		}
    	}
    	return true;
    } 
    
    public static function getStateName($stateId) 
    {
   		if (empty($stateId)) return array();
   		$db = JFactory::getDbo();
   		$query = $db->getQuery(true);
   		
   		$query->select('title')
   			->from('#__wf_states')
   			->where('id = '. (int)stateId);
   		$db->setQuery($query);
   		
   		return $db->loadResult(); 
    }
    
    public static function getTransitionLogs($context, $item_id)
    {
    	if (empty($context) || empty($item_id)) return array();
    	$db = JFactory::getDbo();
    	$query = $db->getQuery(true);

    	$query->select('a.id, a.title, a.comment, a.created')
    		->from('#__wf_transition_logs AS a');
    		
		$query->select('ss.title AS from_state');
    	$query->join('LEFT', '#__wf_states AS ss ON ss.id = a.from_state_id');
    	
    	$query->select('tr.title AS transition_name');
    	$query->join('LEFT', '#__wf_transitions AS tr ON tr.id = a.transition_id');
    	
    	$query->select('ts.title AS to_state');
    	$query->join('LEFT', '#__wf_states AS ts ON ts.id=tr.target_state_id');
    	
    	$query->select('u.name as author_name');
    	$query->join('LEFT', '#__users AS u ON u.id = a.created_by');
    	
    	$query->where('context = '.$db->quote($context));
    	$query->where('item_id = '.(int)$item_id);
    	
    	$query->order('a.created ASC');
    	
    	$db->setQuery($query);
    	
    	return $db->loadObjectList();
    }
    
    /**
     * Get possible states in workflow
     * @param unknown $context
     * @return boolean|mixed
     */
    
    public static function getStatesByContext($context)
    {
    	$app = JFactory::getApplication();
    	if (empty($context)) {
    		$app->enqueueMessage('No context id provided to get all active states', 'warning');
    		return false;
    	}
    	
    	$dbo = JFactory::getDbo();
    	$query = $dbo->getQuery(true);
    	
    	$query->select('workflow_id')
    		->from('#__wf_bindings')
    		->where('context='.$dbo->quote($context));
    	$dbo->setQuery($query);
    	$workflowId = $dbo->loadResult();
    	
    	if (empty($workflowId)) {
    		$app->enqueueMessage('No associate context found', 'warning');
    		return false;    		
    	}
    	
    	$query->clear();
    	$query->select('id, title')->from('#__wf_states')->where('workflow_id='.$workflowId);
    	$dbo->setQuery($query);
    	
    	return $dbo->loadObjectList();
    } 
    /**
     * Check if workflow if exists for provided context
     * @param unknown $context
     */
    public static function workflowExists($context)
    {
    	$db = JFactory::getDbo();
    	$query = $db->getQuery(true);
    	$subquery = $db->getQuery(true);
    	
    	$query->select('count(id)')->from('#__wf_workflows AS a')->where('a.published=1');
    	$subquery->select('workflow_id')->from('#__wf_bindings AS b')->where('b.context='.$db->quote($context));
    	
    	$query->where('a.id IN ('.$subquery.')');
    	$db->setQuery($query);
    		
    	if ($db->loadResult() == 0) return false;
    		
    	return true;
    }
    
 	public static function allowUser($transitionId, $userId = null) 
 	{
 		if ($userId === 0) {
 			//this is system act as user
 		}else if ($userId === null) {
 			$user = JFactory::getUser();
 			$userId = $user->get('id');
 		}else{
 			$userId = (int) $userId;
 			$user = JFactory::getUser($userId);
 		}
 		
 		$db = JFactory::getDbo();
 		$query = $db->getQuery(true);
 		//check for user permission

 		$query->select('count(item_id)')
 			->from('#__wf_transition_permissions')
 			->where('transition_id='.(int)$transitionId)
 			->where('permission_context='.$db->quote('joomla.user'))
 			->where('item_id='.(int)$userId);
 		
 		$db->setQuery($query);
 		if ((bool)$db->loadResult()) {
 			return true;
 		}

 		//check for group permission
 		$query->clear();
 		$query->select('item_id')
 			->from('#__wf_transition_permissions')
 			->where('transition_id='.(int)$transitionId)
 			->where('permission_context='.$db->quote('joomla.usergroup'));
 		$db->setQuery($query);

 		$groups = $db->loadColumn();
 		$userGroups = JAccess::getGroupsByUser($userId, true);
 		
 		$matches = array_intersect($groups, $userGroups);

 		return (count($matches) > 0);
 	}   
    
    public static function getStateByContext($context, $id=null)
    {
    	if ($id === null) {
    		
    	}
    	
    	$dbo = JFactory::getDbo();
    	$query = $dbo->getQuery(true);
    	$subquery = $dbo->getQuery(true);
    	
    	$query->select('*')->from('#__wf_states AS ws');
    	
    	$subquery->select('workflow_state_id')
    		->from('#__wf_instances AS wi')
    		->where('context='.$dbo->quote($context))
    		->where('item_id='.(int)$id);
    	
    	$query->where('id=('.$subquery.')');
    	$dbo->setQuery($query);
    	
    	return $dbo->loadObject();
    }
    
    public static function isWorkflowEnabled($context, $id) 
    {
    	$dbo = JFactory::getDbo();
    	$query = $dbo->getQuery(true);
    	$query->select('workflow_id, workflow_state_id')
    		->from('#__wf_instances')
    		->where('context='.$dbo->quote($context))
    		->where('item_id='.(int)$id);
    	
    	$dbo->setQuery($query);
    	$data = $dbo->loadObject();
    	if ($data && ($data->workflow_id > 0) && ($data->workflow_state_id > 0) ) return true;

    	return false;
    }
    
    /**
     * Get authorised action based on working item state
     * @param unknown $context
     * @param unknown $item_id
     * @return JObject
     */
    public static function getActions($context, $item_id)
    {
    	$oInstance = self::getWorkflowInstance($context, $item_id);
    	
    	$assetName = 'com_workflow.state.'.$oInstance->get('workflow_state_id');
    	
    	$user	= JFactory::getUser();
    	$result	= new JObject;
    	
    	$actions = array(
    			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete', 'core.edit.own', 'core.delete.own'
    	);
    	
    	foreach ($actions as $action) {
    		$result->set($action, $user->authorise($action, $assetName));
    	}
    	
    	return $result;
    }
    
    public function getVersion()
    {
    	return 'Workflow 1.5.0 [library 1.5.0, component 1.5.0]';
    }
    
}

class WFTriggerRegistry {
    private static $triggers = array();
    
	private static $instance;
    
	/* make constructor private, so no one can create this class, 
	 * except self::getInstance() 
	 */ 
    private function __construct() {
    	self::init();		
    }
    
    private static function init() {
    	//self::triggers = array();
    }
    
    static function getInstance() {
		if (!is_object(self::$instance)) {
			self::$instance = new WFTriggerRegistry();	
		}
		return self::$instance;
    }
    
    /**
     * Load all transition guard classes
     * (Why we have to load all registered guards?)
     */
    static function loadWorkflowTriggers($transitionId = null)
    {
    	$db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
    	$query->select('a.namespace, a.folder, a.element, a.name')
    		->from('#__wf_triggers AS a');
    	
    	$app = JFactory::getApplication();
    	$option = $app->input->getCmd('option');
    	if ($app->isAdmin() && $option != 'com_workflow'){
    		$query->where('published = 1');
    	}
    		
    	if (!empty($transitionId)) {
    		$sub_query = $db->getQuery(true);
    		
    		$sub_query->select('trigger_id')
    			->from('#__wf_trigger_instances AS g')
    			->where('g.transition_id ='.$transitionId);
    			
    		$query->where('a.id IN ('.$sub_query.')');	
    	}
        
        $db->setQuery($query);
        $triggers = $db->loadObjectList();
        
        if (empty($triggers) || JError::isError($triggers)) return false;
        
        $registry = self::getInstance();	
        foreach($triggers as $trigger) 
        {
        	$trigger->group = $trigger->folder;
            $sFullPath  = JPATH_ADMINISTRATOR.'/components/com_workflow/triggers/';
            $sFullPath .= trim($trigger->group).'/'.trim($trigger->element).'/';
            $sFullPath .= trim($trigger->element).'.php';
            $className	= 'trg'.ucfirst($trigger->group).ucfirst($trigger->element);
            $registry->registerWorkflowTrigger($trigger->namespace, $className, $sFullPath);
        }
        return true;        
    }    
        
    public static function registerWorkflowTrigger($sNamespace, $sClassname, $sPath) {
    	if (!array_key_exists($sNamespace, self::$triggers)) {
        	self::$triggers[$sNamespace] = array('class' => $sClassname, 'path' => $sPath);
    	}
    	return true;
    }

    public static function getWorkflowTrigger($sNamespace) {
    	
        if (array_key_exists( $sNamespace, self::$triggers)){
            $aInfo = self::$triggers[$sNamespace];
        }else{
			return null;
        }
        if (!JFile::exists($aInfo['path'])){
        	return false; 
        }else{

            require_once($aInfo['path']);
            return new $aInfo['class'];
        }

    }

    // get a keyed list of workflow triggers

    function listWorkflowTriggers() {
        $triggerlist = array();
        foreach (self::$triggers as $sNamespace => $aTrigInfo) {
            $oTriggerObj = self::getWorkflowTrigger($sNamespace);
            $triggerlist[$sNamespace] = $oTriggerObj->getInfo();
        }
        return $triggerlist;
    }
   
}