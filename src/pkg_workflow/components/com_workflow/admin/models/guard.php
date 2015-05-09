<?php
defined('_JEXEC') or die;
jimport('joomla.application.component.modeladmin');

/**
 * Guard model.
 *
 * @package     Workflow
 * @subpackage  com_workflow
 * @since       1.0
 */
class WorkflowModelGuard extends JModelAdmin
{
	/**
	 * Method to get the Guard form.
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
		
		
		/* When saving data, we do not load data while calling getForm()
		 * but we depend on plugin_id to load additional form so we 
		 * have to make sure that we trigger the event with data
		 */
		if (!empty($data)) {
			$tmp = JArrayHelper::toObject($data);
			// Import the appropriate plugin group.
			JPluginHelper::importPlugin('workflow');

			// Get the dispatcher.
			$dispatcher = JDispatcher::getInstance();

			// Trigger the form preparation event.
			$results = $dispatcher->trigger('onContentPrepareForm', array($form, $tmp));

			// Check for errors encountered while preparing the form.
			if (count($results) && in_array(false, $results, true))
			{
				// Get the last error.
				$error = $dispatcher->getError();

				if (!($error instanceof Exception))
				{
					throw new Exception($error);
				}
			}			
		}
		 
		return $form;
	}

	/**
	 * Method to get a Guard.
	 *
	 * @param   integer  $pk  An optional id of the object to get, otherwise the id from the model state is used.
	 *
	 * @return  mixed    Category data object on success, false on failure.
	 * @since   1.6
	 */
	public function getItem($pk = null)
	{
		if ($result = parent::getItem($pk)) {

			// Convert the created and modified dates to local user time for display in the form.
			jimport('joomla.utilities.date');
			$tz	= new DateTimeZone(JFactory::getApplication()->getCfg('offset'));

			if (intval($result->created)) {
				$date = new JDate($result->created);
				$date->setTimezone($tz);
				$result->created = $date->toSQL(true);
			}
			else {
				$result->created = null;
			}

			if (intval($result->modified)) {
				$date = new JDate($result->modified);
				$date->setTimezone($tz);
				$result->modified = $date->toSQL(true);
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
			'transition_id = '.(int) $table->transition_id
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
	public function getTable($type = 'Guard', $prefix = 'WorkflowTable', $config = array())
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
			if (empty($data->id)) {
				$transition_id = JFactory::getApplication()->input->get('transition_id', '', 'int');
				$data->transition_id = $transition_id;	
			}else{
				// Import the appropriate plugin group.
				JPluginHelper::importPlugin('workflow');

				// Get the dispatcher.
				$dispatcher = JDispatcher::getInstance();

				// Trigger the form preparation event.
				$results = $dispatcher->trigger('onContentPrepareData', array('com_workflow.guard', $data));

				// Check for errors encountered while preparing the form.
				if (count($results) && in_array(false, $results, true))
				{
					// Get the last error.
					$error = $dispatcher->getError();

					if (!($error instanceof Exception))
					{
						throw new Exception($error);
					}
				}	
			}		
		}
		
		return $data;
	}
	
	/**
	 * Modify form definition before ready for display
	 * @see JModelForm::preprocessForm()
	 */
	public function preprocessForm(JForm $form, $data, $group='workflow') 
	{	
		// Call parent method to trigger onContentPrepareForm event
		parent::preprocessForm($form, $data, $group);	
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

		if (empty($table->id)) {
			// For a new record.

			// Set ordering to the last item if not set
			if (empty($table->ordering)) {
				$db		= JFactory::getDbo();
				$query	= $db->getQuery(true);
				$query->select('MAX(ordering)');
				$query->from('#__wf_guards');
				$query->where('transition_id = '.(int) $table->transition_id);
				
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
	
	/**
	 * Method to save the form data.
	 *
	 * @param   array  $data  The form data.
	 *
	 * @return  boolean  True on success, False on error.
	 *
	 */
	public function save($data)
	{
		// Initialise variables;
		$dispatcher = JDispatcher::getInstance();
		$table = $this->getTable();
		$key = $table->getKeyName();
		$pk = (!empty($data[$key])) ? $data[$key] : (int) $this->getState($this->getName() . '.id');
		$isNew = true;

		// Include the content plugins for the on save events.
		JPluginHelper::importPlugin('content');
		// Include the content plugins for the on prepare data events.
		JPluginHelper::importPlugin('workflow');
		// Allow an exception to be thrown.
		try
		{
			// Load the row if saving an existing record.
			if ($pk > 0)
			{
				$table->load($pk);
				$isNew = false;
			}

			// Load optional fields so they get bound when present
			$result = $dispatcher->trigger('onContentPrepareData', array($this->option . '.' . $this->name, &$table));
			if (in_array(false, $result, true)) {
				$this->setError($dispatcher->getError());
				return false;
			}
						
			// Bind the data.
			if (!$table->bind($data))
			{
				$this->setError($table->getError());
				return false;
			}

			// Prepare the row for saving
			$this->prepareTable($table);

			// Check the data.
			if (!$table->check())
			{
				$this->setError($table->getError());
				return false;
			}

			// Trigger the onContentBeforeSave event.
			$result = $dispatcher->trigger($this->event_before_save, array($this->option . '.' . $this->name, &$table, $isNew));
			if (in_array(false, $result, true))
			{
				$this->setError($table->getError());
				return false;
			}

			// Store the data.
			if (!$table->store())
			{
				$this->setError($table->getError());
				return false;
			}

			// Clean the cache.
			$this->cleanCache();

			// Trigger the onContentAfterSave event.
			$dispatcher->trigger($this->event_after_save, array($this->option . '.' . $this->name, &$table, $isNew));
		}
		catch (Exception $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		$pkName = $table->getKeyName();

		if (isset($table->$pkName))
		{
			$this->setState($this->getName() . '.id', $table->$pkName);
		}
		$this->setState($this->getName() . '.new', $isNew);

		return true;
	}

}