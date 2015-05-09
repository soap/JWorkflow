<?php
/**
 * @package      Workflow.Framework
 *
 * @author       Prasit Gebsaap
 * @copyright    Copyright (C) 2013-2014 Prasit Gebsaap. All rights reserved.
 * @license      http://www.gnu.org/licenses/gpl.html GNU/GPL, see LICENSE.txt
 */

defined('_JEXEC') or die();

// Make sure the cms libraries are loaded
if (!defined('JPATH_PLATFORM')) {
    require_once dirname(__FILE__) . '/../cms.php';
}

if (!defined('WF_LIBRARY')) {
    define('WF_LIBRARY', 1);

}
else {
    // Make sure we run the code below only once
    return;
}

jimport('joomla.filesystem.folder');

// Include the library
require_once dirname(__FILE__) . '/library.php';

