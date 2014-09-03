<?php
/**
 * @package      lib_workflow
 *
 * @author       Prasit Gebsaap (mrs.siam)
 * @copyright    Copyright (C) 2007-2013 Prasit Gebsaap. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();


// Make sure the cms libraries are loaded
if (!defined('JPATH_PLATFORM')) {
    require_once dirname(__FILE__) . '/../cms.php';
}

// define guard/action plugin path
if (!defined('WFPATH_TRIGGERS')) {
	define('WFPATH_TRIGGERS', JPATH_ADMINISTRATOR.'/components/com_workflow/triggers');
}
// Register the Workflow library
JLoader::registerPrefix('WF', JPATH_PLATFORM . '/workflow');
JLoader::register('JInstallerAdapterTrigger', JPATH_PLATFORM . '/workflow/installer/adapter/trigger.php');

// Add include paths
JHtml::addIncludePath(JPATH_PLATFORM . '/workflow/html');
JModelLegacy::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_workflow/models', 'WorkflowModel');
JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_workflow/tables');
JForm::addFieldPath(JPATH_PLATFORM . '/workflow/form/fields');
JForm::addRulePath(JPATH_PLATFORM . '/workflow/form/rules');

// Load com_workflow language file
JFactory::getLanguage()->load('com_workflow', JPATH_SITE.'/components/com_workflow');
JFactory::getLanguage()->load('com_workflow', JPATH_SITE.'/administrator/components/com_workflow');

JLog::addLogger(
	array(
		// Sets file name
		'text_file' => 'jworkflow.log.php'
		),
		// Sets messages of all log levels to be sent to the file
		JLog::ALL,
		// The log category/categories which should be recorded in this file
		// In this case, it's just the one category from our extension, still
		// we need to put it inside an array
		array('jworkflow')
);

// Define version
if (!defined('WFVERSION')) {
    $wfversion = new WFVersion();
    define('WFVERSION', $wfversion->getShortVersion());
}
