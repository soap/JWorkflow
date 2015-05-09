<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.modellist');

/**
 * Triggers model.
 *
 * @package     JWorkflow
 * @subpackage  com_workflow
 * @since       1.0
 */
class WorkflowModelTriggers extends JModelList
{
	/**
	 * Constructor override.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @return  WorkflowModelPlugins
	 * @since   1.0
	 * @see     JModelList
	 */
	
	public function __construct($config = array())
	{
		
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
					'id', 'a.id',
					'namespace', 'a.namespace',
					'name', 'a.name',
					'checked_out', 'a.checked_out',
					'checked_out_time', 'a.checked_out_time',
					'folder', 'a.folder', 'group',
					'published', 'a.published',
					'access', 'a.access', 'access_level',
					'ordering', 'a.ordering',
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
	protected function populateState($ordering = 'namespace', $direction = 'asc')
	{
		// Initialise variables.
		$app = JFactory::getApplication();
		
		$value = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $value);
		
		$value = $app->getUserStateFromRequest($this->context.'.filter.access', 'filter_access', 0, 'int');
		$this->setState('filter.access', $value);
		
		$value = $app->getUserStateFromRequest($this->context.'.filter.published', 'filter_published', '');
		$this->setState('filter.published', $value);
		
		$value = $app->getUserStateFromRequest($this->context.'.filter.folder', 'filter_folder');
		$this->setState('filter.folder', $value);
		
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
				'a.id, a.namespace, a.folder, a.name, a.checked_out, a.checked_out_time,' .
				'a.published, a.access, a.created, a.ordering'
			)
		);
		$query->from('#__wf_triggers AS a');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

		// Join over the asset groups.
		$query->select('ag.title AS access_level');
		$query->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');
		
		// Join over the users for the author.
		$query->select('ua.name AS author_name');
		$query->join('LEFT', '#__users AS ua ON ua.id = a.created_by');
		
		// Filter by search in namespace
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('a.id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
				$query->where('(a.namespace LIKE '.$search.' OR a.alias LIKE '.$search.')');
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
		$folder = $this->getState('filter.folder');
		if ($folder) {
			$query->where('a.folder='.$db->quote($folder));
		}
	    
		// Add the list ordering clause.
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
		if ($orderCol == 'a.ordering' || $orderCol == 'a.folder') {
			$orderCol = 'a.folder '.$orderDirn.', a.ordering';
		}
		$query->order($db->escape($orderCol.' '.$orderDirn));

        return $query;
	}
	
}