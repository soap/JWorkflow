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
		if (JFactory::getDocument()->getType() !== 'html') {
			return true;	
		}
		
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
		
		return true;	
	}
	
	public function onAfterDispatch()
	{
		if (JFactory::getDocument()->getType() !== 'html') {
			return true;
		}
		
		$app = JFactory::getApplication();
		$component = $app->input->getCmd('option', null);
		$view = $app->input->getCmd('view', null);
		if ( ($component !== 'com_content')) {
			return true;
		}
		
		if (($app->getName() == 'administrator') && (($view == 'articles') || empty($view))) {
			
			$doc = JFactory::getDocument();
			$doc->addScript(JUri::root(true).'/media/com_workflow/workflow/js/articles.js');
			
			$buf = $doc->getBuffer('component');
			
			$js = '<script type="text/javascript">
					WFArticles.removeButtons(\'adminForm\');
				   </script>';
			$buf = $buf.$js;
			$doc->setBuffer($buf, 'component');				

			return true;
		}
		
		return true;
	}
	
	protected function disableBackendButtons()
	{
		$buffer = JResponse::getBody();
		
		$publish = $this->params->get('publish_button', 'none');
		$regex = '/(<div class="btn-wrapper"  id="toolbar-publish">)(.*?)(<\/div>)/s';
		if ($publish === 'hide') {
			$buffer = preg_replace($regex, '$1$3', $buffer);
		}else if ($publish === 'disable') {
			$label = JText::_('JPUBLISHED');
			$buffer = preg_replace($regex, '$1<button disabled="disabled"><span class="icon-publish"></span>'.$label.'</button>$3', $buffer);				
		}
		$unpublish = $this->params->get('unpublish_button', 'none');
		$regex = '/(<div class="btn-wrapper"  id="toolbar-unpublish">)(.*?)(<\/div>)/s';
		if ($unpublish == 'hide') {
			$buffer = preg_replace($regex, '$1$3', $buffer);
		}else if ($unpublish=='disable') {
			$label = JText::_('JUNPUBLISHED');
			$buffer = preg_replace($regex, '$1<button disabled="disabled"><span class="icon-unpublish"></span>'.$label.'</button>$3', $buffer);
		}
		
		$featured = $this->params->get('featured_button', 'none');
		$regex = '/(<div class="btn-wrapper"  id="toolbar-featured">)(.*?)(<\/div>)/s';
		if ($featured == 'hide') {
			$buffer = preg_replace($regex, '$1$3', $buffer);				
		}else if ($featured=='disable') {
			$label = JText::_('JFEATURED');
			$buffer = preg_replace($regex, '$1<button disabled="disabled"><span class="icon-featured"></span>'.$label.'</button>$3', $buffer);
		}
		
		$archive = $this->params->get('archive_button', 'none');
		$regex = '/(<div class="btn-wrapper"  id="toolbar-archive">)(.*?)(<\/div>)/s';
		if ($archive == 'hide') {
			$buffer = preg_replace($regex, '$1$3', $buffer);
		}else if ($archive=='disable') {
			$label = JText::_('JARCHIVE');
			$buffer = preg_replace($regex, '$1<button disabled="disabled"><span class="icon-archive"></span>'.$label.'</button>$3', $buffer);
		}
		
		$trash = $this->params->get('trash_button', 'none');
		$regex = '/(<div class="btn-wrapper"  id="toolbar-trash">)(.*?)(<\/div>)/s';
		if ($trash == 'hide') {
			$buffer = preg_replace($regex, '$1$3', $buffer);
		}else if ($trash=='disable') {
			$label = JText::_('JTRASH');
			$buffer = preg_replace($regex, '$1<button disabled="disabled"><span class="icon-trash"></span>'.$label.'</button>$3', $buffer);
		}	
		
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