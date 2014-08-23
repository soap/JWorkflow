<?php
defined('_JEXEC') or die;
jimport('joomla.plugin.plugin');

/**
 * Workflow Notification Plugin.
 *
 * @package     Workflow
 * @subpackage  
 * @since       1.0
 */
class plgWorkflowNotification extends JPlugin
{
	
	protected $states = array('assigned', 'acked', 'resoloved');
	/**
	 * Notification related users on after transition occured
	 *
	 * @return  boolean  True if successful, false if not and a plugin error is set.
	 * @since   1.0
	 */
	public function onWorkflowAfterTransition($context, $oDocument, $oUser, $comment, $oTransition, $oSourceState, $oTargetState)
	{
		// Adjust error condition as required.
		$state = strtolower($oTargetState->title);
		
		if (!in_array($state, $this->states)) {
		 	//do nothing
	 		return true;
	 	}

	 	$receivers = $this->_getObservers($state, $oDocument);
	 	
		return true;
	}
	
	/**
	 * 
	 * Get list of users to send e-mail to them
	 */
	protected function _getObservers($state, $item)
	{
		
		if ($state == 'assigned') {
			//	
		}
		else if ($state == 'acked') {
			
		}
		else if ($state == 'resolved') {
			
		}	
	}
}