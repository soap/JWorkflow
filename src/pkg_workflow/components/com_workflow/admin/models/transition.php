<?php
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Transition model.
 *
 * @package     Workflow
 * @subpackage  com_workflow
 * @since       1.0
 */
class WorkflowModelTransition extends JModelAdmin
{
	/**
	 * Method to get the Transition form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  JForm  A JForm object on success, false on failure
	 * @since   1.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm(
			$this->option.'.'.$this->name,
			$this->getName(),
			array('control' => 'jform', 'load_data' => $loadData)
		);

		if (empty($form)) {
			return false;
		}

		return $form;
	}

	/**
	 * Method to get a Transition.
	 *
	 * @param   integer  $pk  An optional id of the object to get, otherwise the id from the model state is used.
	 *
	 * @return  mixed    Category data object on success, false on failure.
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		if ($result = parent::getItem($pk)) {

			if (empty($pk)) {
				$workflowId = WFApplicationHelper::getActiveWorkflowId();
				if (!empty($workflowId)) {
					$result->workflow_id = $workflowId;
				}
			}
			
			// Convert the created and modified dates to local user time for display in the form.
			jimport('joomla.utilities.date');
			$tz	= new DateTimeZone(JFactory::getApplication()->getCfg('offset'));

			if (intval($result->created)) {
				$date = new JDate($result->created);
				$date->setTimezone($tz);
				$result->created = $date->toSql(true);
			}
			else {
				$result->created = null;
			}

			if (intval($result->modified)) {
				$date = new JDate($result->modified);
				$date->setTimezone($tz);
				$result->modified = $date->toSql(true);
			}
			else {
				$result->modified = null;
			}
			
		}

		return $result;
	}

	/**
	 * A protected method to get a set of ordering conditions.
	 *
	 * @param   JTable  $table  A record object.
	 *
	 * @return  array  An array of conditions to add to add to ordering queries.
	 * @since   1.0
	 */
	protected function getReorderConditions($table = null)
	{
		$condition = array(
			'workflow_id = '.(int) $table->workflow_id
		);

		return $condition;
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   type    $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name.
	 * @param   array   $config  Configuration array for model.
	 *
	 * @return  JTable  A database object
	 * @since   1.0
	 */
	public function getTable($type = 'Transition', $prefix = 'WorkflowTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 * @since   1.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState($this->option.'.edit.'.$this->getName().'.data', array());

		if (empty($data)) {
			$data = $this->getItem();
			$data->fromstates = $this->loadFromStates($data->id);
			
			$data->allowed_groups = $this->loadPermissions($data->id, 'joomla.usergroup');
			$data->allowed_users = $this->loadPermissions($data->id, 'joomla.user');
		}
		
		return $data;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   JTable  $table  The table object for the record.
	 *
	 * @return  boolean  True if successful, otherwise false and the error is set.
	 * @since   1.0
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		// Prepare the alias.
		$table->alias = JApplication::stringURLSafe($table->alias);

		// If the alias is empty, prepare from the value of the title.
		if (empty($table->alias)) {
			$table->alias = JApplication::stringURLSafe($table->title);
		}

		if (empty($table->id)) {
			// For a new record.

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db		= JFactory::getDbo();
				$query	= $db->getQuery(true);
				$query->select('MAX(ordering)');
				$query->from('#__wf_transitions');
				$query->where('workflow_id = '.(int) $table->workflow_id);
				
				$max = (int) $db->setQuery($query)->loadResult();
				
				if ($error = $db->getErrorMsg()) {
					$this->setError($error);
					return false;
				}

				$table->ordering = $max + 1;
			}
		}

		// Clean up keywords -- eliminate extra spaces between phrases
		// and cr (\r) and lf (\n) characters from string
		if (!empty($this->metakey)) {
			// Only process if not empty.

			// array of characters to remove.
			$strip = array("\n", "\r", '"', '<', '>');
			
			// Remove bad characters.
			$clean = JString::str_ireplace($strip, ' ', $this->metakey); 

			// Create array using commas as delimiter.
			$oldKeys = explode(',', $clean);
			$newKeys = array();
			
			foreach ($oldKeys as $key)
			{
				// Ignore blank keywords
				if (trim($key)) {
					$newKeys[] = trim($key);
				}
			}

 			// Put array back together, comma delimited.
 			$this->metakey = implode(', ', $newKeys);
		}
	}
	
	function save($data) 
	{
        $table  = $this->getTable();
        $key    = $table->getKeyName();
        $pk     = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
        $is_new = true;

        // Include the content plugins for the on save events.
        JPluginHelper::importPlugin('content');
        $dispatcher = JDispatcher::getInstance();

        try {
            if ($pk > 0) {
                if ($table->load($pk)) {
                    $is_new = false;
                }
            }


            // Make sure the title and alias are always unique
            $data['alias'] = '';
            //list($title, $alias) = $this->generateNewTitle($data['title'], $data['project_id'], $data['milestone_id'], $data['list_id'], $data['alias'], $pk);

            //$data['title'] = $title;
            //$data['alias'] = $alias;

 
            // Bind the data.
            if (!$table->bind($data)) {
                $this->setError($table->getError());
                return false;
            }

            // Prepare the row for saving
            $this->prepareTable($table);

            // Check the data.
            if (!$table->check()) {
                $this->setError($table->getError());
                return false;
            }

            // Trigger the onContentBeforeSave event.
            $result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $is_new));

            if (in_array(false, $result, true)) {
                $this->setError($table->getError());
                return false;
            }

            // Store the data.
            if (!$table->store()) {
                $this->setError($table->getError());
                return false;
            }

            $pk_name = $table->getKeyName();

            if (isset($table->$pk_name)) {
                $this->setState($this->getName() . '.id', $table->$pk_name);
            }

            $this->setState($this->getName() . '.new', $is_new);

            $id = $this->getState($this->getName() . '.id');

            // Load the just updated row
            $updated = $this->getTable();
            if ($updated->load($id) === false) return false;

            // Store from states
            if (isset($data['fromstates'])) {
                $this->saveFromStates($id, $data['fromstates']);
            }
 
            if (isset($data['allowed_users'])) {
            	$this->savePermissions($id, 'joomla.user', $data['allowed_users']);
            }
            
            if (isset($data['allowed_groups'])) {
            	$this->savePermissions($id, 'joomla.usergroup', $data['allowed_groups']);
            }

            // Clean the cache.
            $this->cleanCache();

            // Trigger the onContentAfterSave event.
            $dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $is_new));
        }
        catch (Exception $e) {
            $this->setError($e->getMessage());
            return false;
        }

        return true;		
		
	}
	
	private function saveFromStates($pk, $data) 
	{
		
		if (!$pk) return true;
		$query = $this->_db->getQuery(true);
		
		$query->select('state_id')
			->from('#__wf_state_transitions')
			->where('transition_id = '.(int)$pk);
		
		$this->_db->setQuery((string) $query);
        $list = (array) $this->_db->loadColumn();
        
        
        $query = $this->_db->getQuery(true);
        // Delete all first
        $query->delete()->from('#__wf_state_transitions')
        	->where('transition_id = '.$pk);
        	
        $this->_db->setQuery($query);
        $this->_db->query();
        
        foreach($data as $value) {
        	$obj = new StdClass();
        	$obj->state_id = (int)$value;
        	$obj->transition_id = (int)$pk;
        	
        	$this->_db->insertObject('#__wf_state_transitions', $obj);
        }
        
        return true;
	}
	
	private function loadFromStates($pk) 
	{
		if (!$pk) return true;
		
		$query = $this->_db->getQuery(true);
		
		$query->select('state_id')
			->from('#__wf_state_transitions')
			->where('transition_id = '.(int)$pk);
		
		$this->_db->setQuery((string) $query);
        $list = (array) $this->_db->loadColumn();
        
        return $list;
	}
	
	private function loadPermissions($transitionId, $permissionType)
	{
		$db = $this->_db;
		$query = $db->getQuery(true);
		$query->select('item_id')->from('#__wf_transition_permissions')
			->where('permission_context='.$db->quote($permissionType))
			->where('transition_id='.(int)$transitionId);
		
		$db->setQuery($query);
		$rows = (array) $db->loadColumn();
		
		if (!$rows) {
			//throw new \Exception($db->getErrorMsg());
		}
		
		return $rows;
		
	}
	
	
	private function savePermissions($transitionId, $permissionType='', $items = array())
	{
		if (!in_array($permissionType, array('joomla.user', 'joomla.usergroup', 'workflow.role'))) {
			throw new Exception(JText::sprintf('COM_WORKFLOW_EXCEPTION_PERMISSION_TYPE_UNSUPPORT', $permissionType), 500);
		}
		
	    $db = $this->_db;
        $query = $db->getQuery(true);

        // Delete existing allowed permission type
        $query
            ->delete('#__wf_transition_permissions')
            ->where('transition_id = ' . $transitionId)
        	->where('permission_context ='.$db->quote($permissionType));

        $db->setQuery($query);

        if (!$db->query()) {
            throw new \Exception($db->getErrorMsg());
        }

        // Add the new allowed permissions
        $permissionType = $db->quote($permissionType);
        
        foreach ($items as $id) {
            $query->clear();
            $query
                ->insert('#__wf_transition_permissions')
                ->columns('transition_id, permission_context, item_id')
                ->values(sprintf('%s,%s,%s', $transitionId, $permissionType, $id));

            $db->setQuery($query);

            if (!$db->query()) {
                throw new \Exception($db->getErrorMsg());
            }
        }
	}
	
	private function deletePermissions($transitionIds)
	{
		if (empty($transitionIds)) {
			return;
		}
		
		$db = $this->_db;
		$query = $db->getQuery(true);
		
		$query
			->delete('#__wf_transition_permissions')
			->where(sprintf('transition_id IN (%s)', implode(',', $transitionIds)));
		
		$db->setQuery($query);
		
		if (!$db->query()) {
			throw new \Exception($db->getErrorMsg());
		}		
	}
}