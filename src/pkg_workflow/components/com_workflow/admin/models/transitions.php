<?php
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Transitions model.
 *
 * @package     Workflow
 * @subpackage  com_workflow
 * @since       1.0
 */
class WorkflowModelTransitions extends JModelList
{
	/**
	 * Constructor override.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @return  WorkflowModelTransitions
	 * @since   1.0
	 * @see     JModelList
	 */

	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'a.id',
				'title', 'a.title',
				'alias', 'a.alias',
				'checked_out', 'a.checked_out',
				'checked_out_time', 'a.checked_out_time',
				'workflow_id', 'a.workflow_id', 'workflow_title',
				'a.target_state_id', 'target_state_title',
				'published', 'a.published',
				'access', 'a.access', 'access_level',
				'ordering', 'a.ordering',
				'language', 'a.language',
				'created', 'a.created',
				'created_by', 'a.created_by',
				'modified', 'a.modified',
				'modified_by', 'a.modified_by',
			);
		}
		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 * @since   1.0
	 */
	protected function populateState($ordering = 'title', $direction = 'asc')
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		$value = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $value);

		$value = $app->getUserStateFromRequest($this->context.'.filter.access', 'filter_access', 0, 'int');
		$this->setState('filter.access', $value);

		$value = $app->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $value);

		// Filter - Workflow
		$workflow = WFApplicationHelper::getActiveWorkflowId('filter_workflow_id');
		$this->setState('filter.workflow_id', $workflow);

		// Set list state ordering defaults.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 * @since   1.0
	 */
	protected function getListQuery()
	{
		// Initialise variables.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		$sub_query = $db->getQuery(true);
		
		$sub_query->select('COUNT(*)')
			->from('#__wf_trigger_instances AS g')
			->join('LEFT', '#__wf_triggers AS pl ON pl.id=g.trigger_id')
			->where('g.transition_id = a.id')
			->where('pl.folder = '.$db->quote('guard'));
			
		$sub_query2 = $db->getQuery(true);
		$sub_query2->select('COUNT(*)')
			->from('#__wf_trigger_instances AS g2')
			->join('LEFT', '#__wf_triggers AS pl2 ON pl2.id=g2.trigger_id')
			->where('g2.transition_id = a.id')
			->where('pl2.folder = '.$db->quote('action'));			
		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.alias, a.checked_out, a.checked_out_time, a.workflow_id, ' .
				'a.target_state_id, a.published, a.access, a.created, a.ordering, ' .
				'('.$sub_query.') AS guard_count, ' .
				'('.$sub_query2.') AS action_count ' 
			)
		);
		$query->from('#__wf_transitions AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

		// Join over the workflows.
		$query->select('wf.title AS workflow_title');
		$query->join('LEFT', '#__wf_workflows AS wf ON wf.id = a.workflow_id');

		// Join over the target_state.
		$query->select('st.title AS target_state_title');
		$query->join('LEFT', '#__wf_states AS st ON st.id = a.target_state_id');
				
		// Join over the users for the author.
		$query->select('ua.name AS author_name');
		$query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
				$query->where('(a.title LIKE '.$search.' OR a.alias LIKE '.$search.')');
			}
		}

		// Filter by access level.
		if ($access = $this->getState('filter.access')) {
			$query->where('a.access = ' . (int) $access);
		}

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.published = ' . (int) $published);
		} else if ($published === '') {
			$query->where('(a.published = 0 OR a.published = 1)');
		}

		// Filter by a single or group of categories.
		$workflowId = $this->getState('filter.workflow_id');
		if (JFactory::getApplication()->input->getCmd('layout')=='fromstate') {
			//We need workflow id if we are in fromstate layout
			if ($workflowId <= 0) {
				$workflowId = $this->getDefaultWorkflowId();
				WFApplicationHelper::setActiveWorkflow($workflowId);
				$this->setState('filter.workflow_id', $workflowId);				
			}
		}
		if (is_numeric($workflowId) && $workflowId != 0) {
			$query->where('a.workflow_id = '.(int) $workflowId);
		}
		else if (is_array($workflowId)) {
			JArrayHelper::toInteger($workflowId);
			$workflowId = implode(',', $categoryId);
			$query->where('a.workflow_id IN ('.$workflowId.')');
		}


		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering', 'a.title');
		$orderDirn	= $this->state->get('list.direction', 'asc');
		if ($orderCol == 'a.ordering' || $orderCol == 'workflow_title') {
			$orderCol = 'workflow_title '.$orderDirn.', a.ordering';
		}
		$query->order($db->escape($orderCol.' '.$orderDirn));

		return $query;
	}
	
	public function getItems() {
		
		$items = parent::getItems();
		
		$count = count($items);
		
		for($x = 0; $x < $count; $x++) 
		{
			$items[$x]->fromstates = $this->getFromStateTitles($items[$x]->id); 		
		}
		
		return $items;
	}
	
	protected function getFromStateTitles($pk) 
	{
		if (empty($pk)) return array();
		
		$query = $this->_db->getQuery(true);
		$query->select('title')	
			->from('#__wf_state_transitions AS a')
			->join('LEFT', '#__wf_states AS s ON s.id = a.state_id')
			->where('transition_id = '.$pk);
		
		$this->_db->setQuery($query);
		$list = (array)$this->_db->loadColumn();
		
		return implode(',', $list);
		
	}
	
	public function getFromStates($workflowId = null)
	{
		if ($workflowId === null) {
			$workflowId = (int)$this->getState('filter.workflow_id');
		}
		
		$dbo = $this->getDbo();
		$query = $dbo->getQuery(true);
		
		$query->select('transition_id, state_id')
			->from('#__wf_state_transitions')
			->where('transition_id IN (SELECT id FROM #__wf_transitions WHERE workflow_id ='.$workflowId.')');
		$dbo->setQuery($query);
		
		$rows = $dbo->loadObjectList();
		
		$results = array();
		foreach($rows as $row) {
			$results[$row->transition_id][] = $row->state_id;	
		}
		
		return $results;
	}
	
	public function getStates($workflowId = null) 
	{
		if ($workflowId === null) {
			$workflowId = (int)$this->getState('filter.workflow_id');
		}
		
		$dbo = $this->getDbo();
		$query = $dbo->getQuery(true);
		
		$query->select('id, title')->from('#__wf_states');
		if ($workflowId) {
			$query->where('workflow_id='.$workflowId);
		}
		
		$query->order('title ASC');
		
		$dbo->setQuery($query);
		
		return $dbo->loadObjectList();	
	}
	
	protected function getDefaultWorkflowId()
	{
		$query = $this->_db->getQuery(true);
		$query->select('id')
			->from('#__wf_workflows AS a')
			->order('title ASC');
		
		$this->_db->setQuery($query);
		
		return $this->_db->loadResult();
	}
}