<?php
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Guards model.
 *
 * @package     Workflow
 * @subpackage  com_workflow
 * @since       1.0
 */
class WorkflowModelTriggers extends JModelList
{
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
	protected function populateState($ordering = 'namespace', $direction = 'asc')
	{
		// Initialise variables.
		$app = JFactory::getApplication();

		$value = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $value);

		$value = $app->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $value);

		$value = $app->getUserStateFromRequest($this->context.'.filter.type', 'filter_type', '');
		$this->setState('filter.type', $value);
		
		$value = $app->getUserStateFromRequest($this->context.'.filter.transition_id', 'filter_transition_id');
		$this->setState('filter.transition_id', $value);

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

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'a.id, a.title, a.namespace, a.checked_out, a.checked_out_time, a.transition_id,' .
				'a.published, a.created, a.ordering'
			)
		);
		$query->from('#__wf_triggers AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the categories.
		$query->select('t.title AS transition_title');
		$query->join('LEFT', '#__wf_transitions AS t ON t.id = a.transition_id');

		// Join over the users for the author.
		$query->select('ua.name AS author_name');
		$query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');
		
		$query->select('p.group AS trigger_type');
		$query->join('LEFT', '#__wf_plugins AS p ON p.id = a.plugin_id');

		// Filter by search in title
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
				$query->where('(a.namespace LIKE '.$search);
			}
		}

		// Filter by published state
		$published = $this->getState('filter.published');
		if (is_numeric($published)) {
			$query->where('a.published = ' . (int) $published);
		} else if ($published === '') {
			$query->where('(a.published = 0 OR a.published = 1)');
		}

		// Filter by type
		$type = $this->getState('filter.type');
		if(is_numeric($type)) {
			if ($type == '1') {
				$query->where('p.group = ' . $db->quote('guard'));
			}
			if ($type == '2') {
				$query->where('p.group = ' . $db->quote('action'));
			}			
		}
		
		// Filter by a single or group of categories.
		$transitionId = $this->getState('filter.transition_id');
		if (is_numeric($transitionId)) {
			$query->where('a.transition_id = '.(int) $transitionId);
		}
		else if (is_array($transitionId)) {
			JArrayHelper::toInteger($transitionId);
			$transitionId = implode(',', $transitionId);
			$query->where('a.transition_id IN ('.$transitionId.')');
		}

		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
		if ($orderCol == 'a.ordering' || $orderCol == 'transition_title') {
			$orderCol = 'transition_title '.$orderDirn.', a.ordering';
		}
		$query->order($db->escape($orderCol.' '.$orderDirn));

		return $query;
	}
	
	public function getItems()
	{	
		$items = parent::getItems();
		
		$triggers = WFApplicationHelper::getTriggersForTransition($this->getTransition());
		
		if (count($items)) {
			for ($i = 0; $i <count($items); $i++) {
				$items[$i]->trigger = null;
				for($j=0; $j < count($triggers); $j++) {
					if ($items[$i]->namespace == $triggers[$j]->getNamespace()) {
						$items[$i]->trigger = $triggers[$j];
						break;
					}
				}					
			}		
		}
		
		return $items;	
	}
	
	public function getTransition() 
	{
		$transition = (int) $this->getState('filter.transition_id');

		if (!$transition) return false;

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		$query->select('*')
			->from('#__wf_transitions')
			->where('id = '.$transition);
		$db->setQuery($query);
		
		return $db->loadObject();
	}
	
	public function getWorkflow()
	{
		$transition = (int)$this->getState('filter.transition_id');
		if (!$transition) return false;

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		
		$query->select('a.*')
			->from('#__wf_workflows AS a')
			->join('LEFT', '#__wf_transitions AS t ON t.workflow_id = a.id')
			->where('t.id = '.$transition);
			
		$db->setQuery($query);
		
		return $db->loadObject();		
				
	}
}