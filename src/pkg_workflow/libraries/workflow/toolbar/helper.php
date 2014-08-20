<?php
defined('_JEXEC') or die;
jimport('workflow.application.helper');
jimport('workflow.toolbar.toolbar');

class WFToolbarHelper 
{
	/**
	 * 
	 * Get available workflow toolbar buttons ...
	 * @param object $oDoc
	 * @param string task  provide this to operate with existing <form></form>
	 * @param string url   provide this to use full link click, form is not needed
	 * @param boolean enableGuard enable guards to block transition or not, 
	 * 				if enabled both block and unblock transitions will be returned
	 */
	public static function getToolbarButtons($oDoc, $task, $url=null, $includeBlocked = false) 
	{
		JHtml::_('wfhtml.scripts.transition');
		JHtml::_('wfhtml.styles.workflow');
		
		if (empty($oDoc->workflow_id) && empty($oDoc->workflow_state_id)) {
			return array();
		}

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('id')->from('#__wf_workflows')
			->where('published = 1')
			->where('id = '.$oDoc->workflow_id);
		
		$db->setQuery($query);
		$r = $db->loadResult();
		
		if (empty($r)) return array();
		
		$oUser = JFactory::getUser();
		$transitions = WFApplicationHelper::getTransitionsForDocumentUser($oDoc, $oUser, $includeBlocked);

		if ($task) {
			
			foreach($transitions as $transition) 
			{
				if ($includeBlocked && $transition->blocked) { 
					$disabled = true;	
				}else{
					$disabled = false;
				}				
				// This mode button has only task assigned to it, like Joomla toolbar usage 
				WFToolbar::workflowButton(
					$transition->title, 
					$task, 
					$transition->id,
					array(
						'disabled' => $disabled,
                		//'href' => '' //don't set href if we have task
                		'tip' => $transition->explain,
            		) );		
			}
		}else{
			foreach($transitions as $transition) 
			{
				if ($includeBlocked && $transition->blocked) { 
					$disabled = true;	
				}else{
					$disabled = false;
				}
				
				// This mode button has a full link assigned to it, form is not needed
				WFToolbar::workflowButton(
					$transition->title, 
					'', 
					$transition->id,
					array(
						'disabled' => $disabled,
                		'href' => $url
            		) );		
			}			
		}
		return WFToolbar::render();
	}	
}