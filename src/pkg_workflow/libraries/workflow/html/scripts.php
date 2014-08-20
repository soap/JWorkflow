<?php
defined('_JEXEC') or die();


jimport('joomla.application.component.helper');


/**
 * Utility class for Workflow javascript behaviors
 * @@internal if change class WFhtmlScript it conflicts with PFhtmlScript
 *
 */
abstract class WFhtmlScripts
{
    /**
     * Array containing information for loaded files
     *
     * @var    array    $loaded
     */
    protected static $loaded = array();


    /**
     * Method to load jQuery JS
     *
     * @return    void
     */
    public static function jQuery()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        $params = JComponentHelper::getParams('com_workflow');

        if (JFactory::getApplication()->isSite()) {
            $load = $params->get('jquery_site', '0');
        }
        else {
            $load = $params->get('jquery_admin', '0');
        }

        // Load only of doc type is HTML
        if (JFactory::getDocument()->getType() == 'html' && $load != '0') {
            $dispatcher	= JDispatcher::getInstance();
            $dispatcher->register('onBeforeCompileHead', 'triggerWorkflowScriptjQuery');
        }

        self::$loaded[__METHOD__] = true;
    }
    
    /**
     * Method to load Workflow transition JS
     *
     * @return    void
     */
    public static function transition()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load dependencies
        if (empty(self::$loaded['jQuery'])) {
            self::jQuery();
        }

        if (empty(self::$loaded['workflow'])) {
            self::workflow();
        }

        // Load only of doc type is HTML
        if (JFactory::getDocument()->getType() == 'html') {
            $dispatcher	= JDispatcher::getInstance();
            $dispatcher->register('onBeforeCompileHead', 'triggerWorkflowScriptTransition');
        }

        self::$loaded[__METHOD__] = true;
    }

    /**
     * Method to load Projectfork base JS
     *
     * @return    void
     */
    public static function workflow()
    {
        // Only load once
        if (!empty(self::$loaded[__METHOD__])) {
            return;
        }

        // Load only of doc type is HTML
        if (JFactory::getDocument()->getType() == 'html') {
            $dispatcher	= JDispatcher::getInstance();
            $dispatcher->register('onBeforeCompileHead', 'triggerWorkflowScriptCore');
        }

        self::$loaded[__METHOD__] = true;
    }
}    

function triggerWorkflowScriptjQuery()
{
    $params = JComponentHelper::getParams('com_workflow');

    if (JFactory::getApplication()->isSite()) {
        $load = $params->get('jquery_site');
    }
    else {
        $load = $params->get('jquery_admin');
    }

    // Auto-load
    if ($load == '') {
        $scripts = (array) array_keys(JFactory::getDocument()->_scripts);
        $string  = implode('', $scripts);

        if (stripos($string, 'jquery') === false) {
            JHtml::_('script', 'com_workflow/jquery/jquery.min.js', false, true, false, false, false);
            JHtml::_('script', 'com_workflow/jquery/jquery.noconflict.js', false, true, false, false, false);
        }
    }

    // Force load
    if ($load == '1') {
        JHtml::_('script', 'com_workflow/jquery/jquery.min.js', false, true, false, false, false);
        JHtml::_('script', 'com_workflow/jquery/jquery.noconflict.js', false, true, false, false, false);
    }	
}
/**
 * Stupid but necessary way of adding WF form JS to the document head.
 * This function is called by the "onCompileHead" system event and makes sure that the form JS is loaded after jQuery
 *
 */
function triggerWorkflowScriptTransition()
{
    JHtml::_('script', 'com_workflow/workflow/transition.js', false, true, false, false, false);
}    

/**
 * Stupid but necessary way of adding PF core JS to the document head.
 * This function is called by the "onCompileHead" system event and makes sure that the core JS is loaded after jQuery
 *
 */
function triggerWorkflowScriptCore()
{
    JHtml::_('script', 'com_workflow/workflow/workflow.js', false, true, false, false, false);
}
     
