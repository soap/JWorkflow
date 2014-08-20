<?php

defined('_JEXEC') or die();


jimport('joomla.application.component.view');


class WorkflowViewDashboard extends JViewLegacy
{
    /**
     * The current user object
     *
     * @var    object
     */
    protected $user;

    /**
     * The available buttons for rendering
     *
     * @var    array
     */
    protected $modules;


    /**
     * Display the view
     *
     */
    public function display($tpl = null)
    {
        $this->user       = JFactory::getUser();
        $this->modules    = JFactory::getDocument()->loadRenderer('modules');

        if ($this->getLayout() !== 'modal') $this->addToolbar();

        parent::display($tpl);
    }
    
    /**
     * Add the page title and toolbar.
     *
     */
    protected function addToolbar()
    {
        JToolBarHelper::title(JText::_('COM_WORKFLOW_DASHBOARD_TITLE'), 'article.png');

        if (JFactory::getUser()->authorise('core.admin')) {
            JToolBarHelper::preferences('com_workflow');
        }
    }    
}