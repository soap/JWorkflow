<?php
jimport('joomla.plugin.plugin');

/**
 * Workflow Content Plugin.
 *
 * @package     Workflow
 * @subpackage  plg_workflow_content
 * @since       1.0
 */
class plgWorkflowContent extends JPlugin
{
	/**
	 * Inject guard instance form to model 's guard form
	 *
	 * @return  boolean  True if successful, false if not and a plugin error is set.
	 * @since   1.0
	 */
	public function onContentPrepareForm($form, $data)
	{
		if (!($form instanceof JForm)) {
			$this->_subject->setError('JERROR_NOT_A_FORM');
			return false;	
		}
		
		// Check if we are processing a valid form
		$name = $form->getName();
		if ($name != 'com_workflow.trigger') {
			return true;	
		} 
		
		if (empty($data->plugin_id) || empty($data->id)) {
			return true;
		} 
		$plugin = $this->getPlugin($data->plugin_id);
		if ($plugin) {
			$path = WFPATH_PLUGINS . '/' . $plugin->group . '/' .$plugin->name;
			JForm::addFormPath($path . '/forms');
			JFormHelper::addFieldPath($path . '/fields');
			
			// load transition gaurd language 
			$this->loadLanguage('plg_' . $plugin->group . '_'.$plugin->name, $path);
			
			// Load the config form definition, do not reset existing one
			if (!$form->loadFile('triggerconfig', false)) {
				$this->_subject->setError('COM_WORKFLOW_ERROR_TRIGGER_FORM_NOT_LOAD');
				$this->_subject->setError('Path is '.$path);
				return false;
			}
		}

		return true;
	}

	
	public function onContentPrepareData($context, $data)
	{
		if (is_array($data)) $data = JArrayHelper::toObject($data);

		if (isset($data->trigger_config) && empty($data->trigger_config)) {
			if (!empty($data->plugin_id)) {
				$plugin = $this->getPlugin($data->plugin_id);
				if ($plugin) {
					$path = WFPATH_PLUGINS . '/' . $plugin->group . '/' .$plugin->name;
					JForm::addFormPath($path . '/forms');
					JFormHelper::addFieldPath($path . '/fields');
			
					// load transition gaurd language 
					$this->loadLanguage('plg_' . $plugin->group . '_'.$plugin->name, $path);
						
					$form = new JForm('com_workflow.trigger');
					// Load the config form definition, do not reset existing one
					if (!$form->loadFile('triggerconfig', false)) {
						$this->_subject->setError('COM_WORKFLOW_ERROR_FORM_NOT_LOAD');
						return false;
					}
						
					// Merge the default values
            		$data->trigger_config = array();
            		foreach ($form->getFieldset('trigger_config') as $field) {
               			$data->trigger_config[] = array($field->fieldname, $field->value);
            		}
            			
            		return true;
				}
			}	
		}else{
			// load data
			if (is_array($data->trigger_config)) {
				$registry = new JRegistry();
				$registry->loadArray($data->trigger_config);
				$data->trigger_config = (string)$registry;
						
			}else{
				$registry = new JRegistry( $data->trigger_config );
				$data->trigger_config = $registry->toArray();	
			}
				
		}
			
		return true;
	}
	
	/**
	 * 
	 * Was called after model saving data, process to save guard instance config
	 * @param unknown_type $context
	 * @param unknown_type $data
	 */
	public function onContentAfterSave($context, $data) 
	{
		if (isset($data->plugin_id) && $data->plugin_id && isset($data->transition_id) && $data->transition_id) {			
			if (isset($data->trigger_config)) {
				if (!is_array($data->trigger_config)) {
					$data->trigger_config = array($data->trigger_config);
				} 

				JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_workflow/tables');
				$table = JTable::getInstance('Trigger', 'WorkflowTable');
				$table->load($data->id);
				$table->trigger_config = json_encode($data->trigger_config);
				
				if (!$table->store()) {
					$this->_subject->setError('JERROR_TABLE_SAVE_FAILURE');
					return false;
				}
			}		
		}
		return true;	
	}
	
	private function getPlugin($id) 
	{
		if (empty($id)) {
			return false;
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*')
			->from('#__wf_plugins AS a')
			->where('a.id = '.$id);
			
		$db->setQuery($query);
		$result = $db->loadObject();
		return $result;
	}
}