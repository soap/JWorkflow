<?php
defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');

/**
 * Content Workflow Plugin.
 *
 * @package     JWorkflow
 * @subpackage  content_workflow
 * @since       1.0
 */
class plgContentWorkflow extends JPlugin
{

	/**
	 * 
	 * Looking for workflow detail and update content item for saving
	 * @param unknown_type $context
	 * @param unknown_type $table
	 * @param unknown_type $is_new
	 * @deprecated version 1.5 look for onContentAfterSave
	 */
	public function onContentBeforeSave($context, $table, $is_new)
    {	
    	if ( property_exists($table, 'workflow_id') && property_exists($table, 'workflow_state_id') 
    		&& empty($table->workflow_id) ) 
    	{
    		$db = JFactory::getDbo();
    		$query = $db->getQuery(true);
			$query->select('workflow_id, params')
				->from('#__wf_bindings')
				->where('context = '.$db->quote($context))
				->where('published = 1');
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			
			if (count($rows) === 0) {
				JFactory::getApplication()->enqueueMessage('(1) Cannot find workflow mapping for '.$context);
				return true;
			}
			
			if (count($rows) == 1) {
				$workflow_id = $rows[0]->workflow_id;	
			}else{
				$maxScore = 0;
				$workflow_id = 0;
				foreach($rows as $i =>$workflow) {
					$currentScore = 0;
					if (!empty($workflow->params)) {
						$m = new JRegistry($workflow->params);
						$string = $m->get('other_mappings', '');
						if (!empty($string)) {
							$rows[$i]->mappings = explode('\r\n', $string);	
						}else{
							$rows[$i]->mappings = array();
						}
					}
					
					if (count($rows[$i]->mappings)) {
						foreach($rows[$i]->mappings as $map){
							list($field, $value) = explode('=', $map);
							$field = trim($field);
							$value = trim($value);
							if (isset($table->$field) && $table->$field == $value) {
								$currentScore += 1;
							}
						} 
					}
					
					if ($currentScore > $maxScore) {
						$maxScore = $currentScore;
						$workflow_id = $workflow->workflow_id;
					}
				}
			}
    		
			if ($workflow_id === 0) {
				JFactory::getApplication()->enqueueMessage('(2) Cannot find workflow mapping for '.$context);
				return true;
			}
			$query = $db->getQuery(true);
			$query->select('a.id, a.title, a.ordering')
				->from('#__wf_states AS a')
				->where('published = 1')
				->where('start_state = 1')
				->where('workflow_id ='.(int)$workflow_id)
				->order('a.ordering ASC');
			$db->setQuery($query);
			$row = $db->loadObject();
			
			$start_state_id = $row->id;
			
			if (empty($start_state_id)) { 
				JFactory::getApplication()->enqueueMessage('Cannot find workflow start state for '.$context);
				return true;
			}
    		
			$table->workflow_id = $workflow_id;
			$table->workflow_state_id = $start_state_id;
			JFactory::getApplication()->enqueueMessage($context.' item was inserted into '.$row->title .' workflow.' );		
			
			return true;
    	} 		
    }
    
    /**
     *
     * Looking for workflow detail and store workflow instance for content item (use own table)
     * @param unknown_type $context
     * @param unknown_type $table
     * @param unknown_type $is_new
     */
    public function onContentAfterSave($context, $table, $is_new)
    {
    	if ($is_new) {
    		$db = JFactory::getDbo();
    		$query = $db->getQuery(true);
    		$query->select('workflow_id, params')
    		->from('#__wf_bindings')
    		->where('context = '.$db->quote($context))
    		->where('published = 1');
    		$db->setQuery($query);
    		$rows = $db->loadObjectList();
    		
    		//no workflow mapping for this context
    		if (count($rows) == 0) return true;

    		if (count($rows) == 1) {
				$workflow_id = $rows[0]->workflow_id;	
			}else{
				$maxScore = 0;
				$workflow_id = 0;
				foreach($rows as $i =>$workflow) {
					$currentScore = 0;
					if (!empty($workflow->params)) {
						$m = new JRegistry($workflow->params);
						$string = $m->get('other_mappings', '');
						if (!empty($string)) {
							$rows[$i]->mappings = explode('\r\n', $string);	
						}else{
							$rows[$i]->mappings = array();
						}
					}
					
					if (count($rows[$i]->mappings)) {
						foreach($rows[$i]->mappings as $map){
							list($field, $value) = explode('=', $map);
							$field = trim($field);
							$value = trim($value);
							if (isset($table->$field) && $table->$field == $value) {
								$currentScore += 1;
							}
						} 
					}
					
					if ($currentScore > $maxScore) {
						$maxScore = $currentScore;
						$workflow_id = $workflow->workflow_id;
					}
				}
			}
			
			if ($workflow_id === 0) {
				JFactory::getApplication()->enqueueMessage('(2) Cannot find workflow mapping for '.$context);
				return true;
			}
			$query->clear();
			
			$query->select('title as workflow_title')
				->from('#__wf_workflows')
				->where('id = '.(int)$workflow_id);
			
			$db->setQuery($query);
			$workflow_title = $db->loadResult();
			
			$query->clear();
			$query->select('a.id, a.title, a.ordering')
			->from('#__wf_states AS a')
			->where('published = 1')
			->where('start_state = 1')
			->where('workflow_id ='.(int)$workflow_id)
			->order('a.ordering ASC');
			$db->setQuery($query);
			$row = $db->loadObject();
				
			$start_state_id = $row->id;
				
			if (empty($start_state_id)) {
				JFactory::getApplication()->enqueueMessage('Cannot find workflow start state for '.$context);
				return true;
			}
			
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_workflow/tables');
			$instance = JTable::getInstance('Instance', 'WorkflowTable');
			$instance->load(array('context'=>$context, 'item_id'=>$table->id));
			$instance->context = $context;
			$instance->item_id = $table->id;
			$instance->workflow_id = $workflow_id; 
			$instance->workflow_state_id = $start_state_id;
			$instance->check();
			$instance->store();
			JFactory::getApplication()->enqueueMessage('The new item; '.$table->id.' of '.$context.' was inserted into '.$workflow_title.' workflow. Its state is '.$row->title );			
    	}
    	
    	return true;
    }
    
    /**
     * 
     * Remove workflow instance if working item deleteddescription here ...
     * @param string $context
     * @param object $table
     */
    public function onContentAfterDelete($context, $table) 
    {
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_workflow/tables');
		$instance = JTable::getInstance('Instance', 'WorkflowTable');
		
		$instance->load(array('context'=>$context, 'item_id'=>$table->id));
		if ($instance === false) {
			return true;
		}
		//delete workflow instance record
		$instance->delete();
		
		return true;
		
    }
    
    public function onContentPrepare($context, &$article, &$params, $page)
    {

    }
    
    public function onContentPrepareForm($form, $data) 
    {
    	if (is_object($data) && empty($data->id)) {
    		$form->setFieldAttribute('state', 'disabled', 'true');
    	}
    	
    	if (is_array($data) && empty($data['id'])) {
    		$form->setFieldAttribute('state', 'disabled', 'true');
    	}
    }
    
    public function onContentPrepareData($context, $data)
    {
    	if ($context != 'com_content.article') return true;
    	
     	if (is_object($data) && empty($data->id)) {
     		$data->state = 0;	
     	}
    	
     	return true;
    }
    
    protected function compare($m1, $m2) 
    {
    	if (count($m1->mappings) == count($m2->mappings)) return 0;

    	return count($m1->mappings) < count($m2->mappings) ? -1 : 1;
    }
    
}