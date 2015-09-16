<?php
defined('_JEXEC') or die;
jimport('joomla.database.table');

/**
 * State table.
 *
 * @package     Workflow
 * @subpackage  com_workflow
 * @since       1.0
 */
class WorkflowTableState extends JTable
{
	/**
	 * Constructor.
	 *
	 * @param   JDatabase  $db  A database connector object.
	 *
	 * @return  WorkflowTableWorkflow
	 * @since   1.0
	 */
	public function __construct($db)
	{
		parent::__construct('#__wf_states', 'id', $db);
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param   array   $array   The input array to bind.
	 * @param   string  $ignore  A list of fields to ignore in the binding.
	 *
	 * @return  null|string	null is operation was satisfactory, otherwise returns an error
	 * @see     JTable:bind
	 * @since   1.0
	 */
	public function bind($array, $ignore = '')
	{
		if (isset($array['params']) && is_array($array['params'])) {
			$registry = new JRegistry();
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}
		
		// Bind the rules.
		if (isset($array['rules']) && is_array($array['rules'])) {
			$rules = new JRules($array['rules']);
			$this->setRules($rules);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check method to ensure data integrity.
	 *
	 * @return  boolean  True on success.
	 * @since   1.0
	 */
	public function check()
	{
		// Check for valid name.
		if (trim($this->title) === '') {
			$this->setError(JText::_('COM_WORKFLOW_ERROR_STATE_TITLE'));
			return false;
		}

		return true;
	}

	/**
	 * Overload the store method for the Weblinks table.
	 *
	 * @param   boolean  $updateNulls  Toggle whether null values should be updated.
	 *
	 * @return  boolean  True on success, false on failure.
	 * @since   1.0
	 */
	public function store($updateNulls = false)
	{
		// Initialiase variables.
		$date	= JFactory::getDate()->toSql();
		$userId	= JFactory::getUser()->get('id');

		if (empty($this->id)) {
			// New record.
			$this->created		= $date;
			$this->created_by	= $userId;
		} 
		else {
			// Existing record.
			$this->modified	= $date;
			$this->modified_by	= $userId;
		}

		// Attempt to store the data.
		if (parent::store($updateNulls)) {
			if ($this->start_state == 1) {
				$this->resetStartState();	
			}
			return true;		
		}
		
		return false;
	}
	
	/**
	 * Redefined asset name, as we support action control
	 */
	protected function _getAssetName() 
	{
		$k = $this->_tbl_key;
		return 'com_workflow.state.'.(int) $this->$k;
	}
	
	/**
	 * We provide our global ACL as parent
	 * @see JTable::_getAssetParentId()
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		$asset = JTable::getInstance('Asset');
		$asset->loadByName('com_workflow');
		return $asset->id;
	}	
	
	protected function resetStartState() 
	{
		$db = $this->_db;
		$query = $db->getQuery(true);
		
		$query->update($this->_tbl)
			->set('start_state = 0')
			->where('workflow_id = '.$this->workflow_id)
			->where('id <> '.$this->_tbl_key);
		$db->setQuery($query);
		
		return $db->query();
	}
}