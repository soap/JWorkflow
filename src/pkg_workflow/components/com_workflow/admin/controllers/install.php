<?php
defined('_JEXEC') or die;

/**
 * @package     JWorkflow
 * @subpackage  com_workflow
 */
class WorkflowControllerInstall extends JControllerLegacy
{
	/**
	 * Install an extension.
	 *
	 * @return  void
	 * @since   1.5
	 */
	public function install()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$model = $this->getModel('install');

		if ($model->install())
		{
			$cache = JFactory::getCache('mod_menu');
			$cache->clean();
			// TODO: Reset the users acl here as well to kill off any missing bits
		}

		$app = JFactory::getApplication();
		$redirect_url = $app->getUserState('com_workflow.redirect_url');
		if (empty($redirect_url))
		{
			$redirect_url = JRoute::_('index.php?option=com_workflow&view=install', false);
		} else
		{
			// wipe out the user state when we're going to redirect
			$app->setUserState('com_workflow.redirect_url', '');
			$app->setUserState('com_workflow.message', '');
			$app->setUserState('com_workflow.extension_message', '');
		}
		$this->setRedirect($redirect_url);
	}
}
