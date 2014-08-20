<?php
defined('_JEXEC') or die();

jimport('joomla.application.component.helper');

/**
 * Utility class for Workflow style sheets
 *
 */
abstract class WFhtmlStyles
{
    /**
     * Array containing information for loaded files
     *
     * @var    array    $loaded
     */
    protected static $loaded = array();

    /**
     * Method to load Projectfork CSS
     *
     * @return    void
     */
    public static function workflow()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        $params = JComponentHelper::getParams('com_workflow');

        // Load only if doc type is HTML
        if (JFactory::getDocument()->getType() == 'html' && $params->get('workflow_css', '1') == '1') {
            $dispatcher	= JDispatcher::getInstance();
            $dispatcher->register('onBeforeCompileHead', 'triggerWorkflowStyleCore');
        }

        self::$loaded[__METHOD__] = true;
    }
}

/**
 * Stupid but necessary way of adding workflow CSS to the document head.
 * This function is called by the "onCompileHead" system event and makes sure that the CSS is loader after bootstrap
 *
 */
function triggerWorkflowStyleCore()
{
    JHtml::_('stylesheet', 'com_workflow/workflow/site.css', false, true, false, false, false);
}
