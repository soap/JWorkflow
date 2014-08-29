<?php
/**
 * @copyright	Copyright (C) 2014 Prasit Gebsaap. All rights reserved.
* @license		GNU General Public License version 2 or later; see LICENSE.txt
*/

// no direct access
defined('_JEXEC') or die;

/**
 * Workflow System Plugin
 *
 * @package		JWorkflow
 * @subpackage	plg_system_workflow
 */
class plgSystemWorkflow extends JPlugin
{
	public function onAfterRender()
	{
		$app = JFactory::getApplication();
		$component = $app->input->getCmd('option', null);
		$view = $app->input->getCmd('view', null);
		if ( ($component !== 'com_content')) {
			return true;
		}
		
		if (($app->getName() == 'administrator') && (($view == 'articles') || empty($view))) {
			$this->disableBackendButtons();
			return true;
		}		
	}
	
	protected function disableBackendButtons()
	{
		$buffer = JResponse::getBody();
		
		$regex = '/(<div class="btn-wrapper"  id="toolbar-publish">)(.*?)(<\/div>)/s';
		$buffer = preg_replace($regex, '$1<button disabled="disabled"><span class="icon-publish"></span>Publish</button>$3', $buffer);
		
		$regex = '/(<div class="btn-wrapper"  id="toolbar-unpublish">)(.*?)(<\/div>)/s';
		$buffer = preg_replace($regex, '$1<button disabled="disabled"><span class="icon-unpublish"></span>Unpublish</button>$3', $buffer);		

		$this->checkBuffer($buffer);
		JResponse::setBody($buffer);
		
		return true;
	}

	private function checkBuffer($buffer) {
		if ($buffer === null) {
			switch (preg_last_error()) {
				case PREG_BACKTRACK_LIMIT_ERROR:
					$message = "PHP regular expression limit reached (pcre.backtrack_limit)";
					break;
				case PREG_RECURSION_LIMIT_ERROR:
					$message = "PHP regular expression limit reached (pcre.recursion_limit)";
					break;
				case PREG_BAD_UTF8_ERROR:
					$message = "Bad UTF8 passed to PCRE function";
					break;
				default:
					$message = "Unknown PCRE error calling PCRE function";
			}
			JError::raiseError(500, $message);
		}
	}
}