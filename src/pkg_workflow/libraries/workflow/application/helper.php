<?php
/**
 * Workflow utility class to be used by component developer
 * @author Prasit Gebsaap
 * @version 1.1.8 RC3 Dated: 2014--28
 * @copyright 2010-2013 by Prasit Gebsaap
 */
// no direct access
defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.database.table');

JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_workflow/tables');


class WFApplicationHelper {
    
	protected static $components;
	
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
     * Gets the transitions that are available for a document by virtue
     * of its workflow state, and also by virtue of the user that wishes
     * to perform the transition.
     *
     * In other words, ensures that the guard permission, role, group,
     * and/or user are met for the given user.
     * @internal tested on 2010-03-19
     */
    public static function getTransitionsForDocumentUser($oDocument, $oUser, $includeBlocked = false) {
        $oState = self::getWorkflowStateForDocument($oDocument);
        if (is_null($oState) || JError::isError($oState)) {
            return $oState;
        }
        $aTransitions = self::getTransitionsFrom($oState);    

        $aEnabledTransitions = array();
        
        foreach ($aTransitions as $oTransition) 
        {
            $aGuardTriggers = self::getGuardTriggersForTransition($oTransition);
            if (JError::isError($aGuardTriggers)) 
            {
                return $aGuardTriggers; // error out?
            }
            
            if ( is_array($aGuardTriggers) && (count($aGuardTriggers)> 0) ) {
            	$blocked = false;
            	foreach ($aGuardTriggers as $oTrigger) {
            		$explain = '';
                	if (!$oTrigger->allowTransition($oDocument, $oUser)) 
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
     * Gets the workflow state that applies to the given document,
     * returning null if there is no workflow assigned.
     * if options contains ids then only id will be returned.
     * @internal verified on 2013-07-16, not test yet 
     */
    protected function getWorkflowStateForDocument ($oDocument, $aOptions = array()) 
    {
    	$type = 'object';
        if (array_key_exists('type', $aOptions)) {
        	$type = $aOptions['type'];
        }
		if (!isset($oDocument->workflow_state_id)) {
			return false;
		}
        $iWorkflowStateId = $oDocument->workflow_state_id;

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
    protected function getTransitionsFrom($oState, $aOptions = array()) 
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
    
    public function getTransition($transitionId) 
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
    		->where('transition_id = '.$transitionId)
    		->where('published = 1'); //unpublished trigger, no effect
     
       	$db->setQuery($query);
       	$aObjects = $db->loadObjectList();
       	
       	$guards = array();
       	if (is_array($aObjects)){   
            foreach ($aObjects as $object){
                $guard = JTable::getInstance('Trigger', 'WorkflowTable');
                $guard->load($object->id);
                $guards[] = clone $guard;
            }
       	}

       	return $guards;     	
       
    }
    /**
     * Gets guard triggers for specified transition object
     */
    function getGuardTriggersForTransition($oTransition) {
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
    function getActionTriggersForTransition($oTransition) {
        $aTriggers =  WFApplicationHelper::getTriggersForTransition($oTransition);
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
    function getTriggersForWorkflow($iWorkflowId) {
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
		$dispatcher = JDispatcher::getInstance();
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
        		'transition_id'	=>	$oTransition->get('id') 
        	);
       	    $oTransactionLog = JTable::getInstance('Transitionlog', 'WorkflowTable');
       	    $oTransactionLog->reset();
       	    $oTransactionLog->bind($data);
        	$oTransactionLog->store();
        
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
        //CpPermission::updatePermissionLookUp( $oDocument );
        
        self::performSystemActionOnDocument( $oDocument, $context );
        return true;
            
    }
    
    /**
     * Check if document is waiting for system action.
     * If yes, action on the document
     * @param object Document to be processed
     */
    static function performSystemActionOnDocument( $oDocument, $context ) {
        /* Check if document is waiting for system action */
        if (!self::isWaitingForSystem($oDocument)) return true; 
        /* Get available system transitions */
        $transitions = self::getTransitionsForDocumentUser($oDocument, null);
        $selected_transition = null;
        foreach ($transitions as $transition ) {
            if ($transition->system_path == 1) {
                $selected_transition = $transition;
                break;
            }
        }
        /* Perform transition on document */
        if (!empty($selected_transition)) {
            return self::performTransitionOnDocument($transition, $oDocument, null, context);
        }
        
        return true;    
    }  
    
    protected function isWaitingForSystem() 
    {
    	return false;	
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
    
    public function getVersion()
    {
    	return 'Workflow 1.1.8 [library 1.1.3, component 1.1.3]';
    }
    
}

class WFTriggerRegistry {
    private static $triggers;
    
	private static $instance;
    
	/* make constructor private, so no one can create this class, 
	 * except self::getInstance() 
	 */ 
    private function __construct() {
    	self::init();		
    }
    
    private function init() {
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
        
    	$query->select('a.namespace, a.group, a.name')
    		->from('#__wf_triggers AS a')
    		->where('published = 1');
    		
    	if (!empty($transitionId)) {
    		$sub_query = $db->getQuery(true);
    		
    		$sub_query->select('plugin_id')
    			->from('#__wf_trigger_instances AS g')
    			->where('g.transition_id ='.$transitionId);
    			
    		$query->where('a.id IN ('.$sub_query.')');	
    	}
        
        $db->setQuery($query);
        $plugins = $db->loadObjectList();
        
        if (empty($plugins) || JError::isError($plugins)) return false;
        
        $registry = self::getInstance();	
        foreach($plugins as $plugin) 
        {
            $sFullPath  = JPATH_ADMINISTRATOR.'/components/com_workflow/plugins/';
            $sFullPath .= trim($plugin->group).'/'.trim($plugin->name).'/';
            $sFullPath .= trim($plugin->name).'.php';
            $className	= 'plg'.ucfirst($plugin->group).ucfirst($plugin->name);
            $registry->registerWorkflowTrigger($plugin->namespace, $className, $sFullPath);
        }
        return true;        
    }    
        
    public function registerWorkflowTrigger($sNamespace, $sClassname, $sPath) {
    	if (!array_key_exists($sNamespace, $this->triggers)) {
        	$this->triggers[$sNamespace] = array('class' => $sClassname, 'path' => $sPath);
    	}
    	return true;
    }

    public function getWorkflowTrigger($sNamespace) {
    	
        if (array_key_exists( $sNamespace, $this->triggers)){
            $aInfo = $this->triggers[$sNamespace];
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
        foreach ($this->triggers as $sNamespace => $aTrigInfo) {
            $oTriggerObj = $this->getWorkflowTrigger($sNamespace);
            $triggerlist[$sNamespace] = $oTriggerObj->getInfo();
        }
        return $triggerlist;
    }
}