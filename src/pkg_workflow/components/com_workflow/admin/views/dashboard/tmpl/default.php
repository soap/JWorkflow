<?php
defined('_JEXEC') or die();
jimport('workflow.version.version');

$modules = &$this->modules;
$wfv     = new WFVersion();
$jv      = new JVersion();
//
//Why we got a complaint on a undefined constant as we already done in library?
if (!defined('WFVERSION')) {
    define('WFVERSION', $wfv->getShortVersion());
}
?>
<div class="row-fluid">
	<div class="span2">
		<?php echo $this->sidebar?>
	</div>
	<div class="span6 hidden-phone">
            <?php if ($this->user->authorise('core.admin')) : ?>
                <a class="thumbnail btn pull-left" href="<?php echo JRoute::_('index.php?option=com_config&view=component&component=com_workflow');?>">
                    <?php echo JHtml::image('com_workflow/workflow/header/icon-48-option.png', JText::_('COM_WORKFLOW_DASHBOARD_CONFIG'), null, true); ?>
                    <span class="small"><?php echo JText::_('COM_WORKFLOW_DASHBOARD_CONFIG');?></span>
                </a>
                <!-- Dashboard Icon-->
                 <a class="thumbnail btn pull-left" href="<?php echo JRoute::_('index.php?option=com_workflow&view=dashboard');?>">
                    <?php echo JHtml::image('com_workflow/workflow/header/icon-48-dashboard.png', JText::_('COM_WORKFLOW_SUBMENU_DASHBOARD'), null, true); ?>
                    <span class="small"><?php echo JText::_('COM_WORKFLOW_SUBMENU_DASHBOARD');?></span>
                </a>                   
                <!-- Categories Icon-->
                 <a class="thumbnail btn pull-left" href="<?php echo JRoute::_('index.php?option=com_categories&extension=com_workflow');?>">
                    <?php echo JHtml::image('com_workflow/workflow/header/icon-48-categories.png', JText::_('COM_WORKFLOW_SUBMENU_CATEGORIES'), null, true); ?>
                    <span class="small"><?php echo JText::_('COM_WORKFLOW_SUBMENU_CATEGORIES');?></span>
                </a>  
                <!-- Worksflows Icon-->
                 <a class="thumbnail btn pull-left" href="<?php echo JRoute::_('index.php?option=com_workflow&view=workflows');?>">
                    <?php echo JHtml::image('com_workflow/workflow/header/icon-48-workflow.png', JText::_('COM_WORKFLOW_SUBMENU_WORKFLOWS'), null, true); ?>
                    <span class="small"><?php echo JText::_('COM_WORKFLOW_SUBMENU_WORKFLOWS');?></span>
                </a> 
                <!-- Bindings Icon-->
                 <a class="thumbnail btn pull-left" href="<?php echo JRoute::_('index.php?option=com_workflow&view=bindings');?>">
                    <?php echo JHtml::image('com_workflow/workflow/header/icon-48-bindings.png', JText::_('COM_WORKFLOW_SUBMENU_BINDINGS'), null, true); ?>
                    <span class="small"><?php echo JText::_('COM_WORKFLOW_SUBMENU_BINDINGS');?></span>
                </a>      
                <!-- States Icon-->
                 <a class="thumbnail btn pull-left" href="<?php echo JRoute::_('index.php?option=com_workflow&view=states');?>">
                    <?php echo JHtml::image('com_workflow/workflow/header/icon-48-state.png', JText::_('COM_WORKFLOW_SUBMENU_STATES'), null, true); ?>
                    <span class="small"><?php echo JText::_('COM_WORKFLOW_SUBMENU_STATES');?></span>
                </a>
                <!-- Transitions Icon-->
                 <a class="thumbnail btn pull-left" href="<?php echo JRoute::_('index.php?option=com_workflow&view=transitions');?>">
                    <?php echo JHtml::image('com_workflow/workflow/header/icon-48-transition.png', JText::_('COM_WORKFLOW_SUBMENU_TRANSITIONS'), null, true); ?>
                    <span class="small"><?php echo JText::_('COM_WORKFLOW_SUBMENU_TRANSITIONS');?></span>
                </a> 
                <!-- Trigger Icon-->
                 <a class="thumbnail btn pull-left" href="<?php echo JRoute::_('index.php?option=com_workflow&view=triggers');?>">
                    <?php echo JHtml::image('com_workflow/workflow/header/icon-48-trigger.png', JText::_('COM_WORKFLOW_SUBMENU_TRIGGERS'), null, true); ?>
                    <span class="small"><?php echo JText::_('COM_WORKFLOW_SUBMENU_TRIGGERS');?></span>
                </a> 
                <!-- Install Icon-->
                 <a class="thumbnail btn pull-left" href="<?php echo JRoute::_('index.php?option=com_workflow&view=install');?>">
                    <?php echo JHtml::image('com_workflow/workflow/header/icon-48-install.png', JText::_('COM_WORKFLOW_SUBMENU_INSTALL'), null, true); ?>
                    <span class="small"><?php echo JText::_('COM_WORKFLOW_SUBMENU_INSTALL');?></span>
                </a>  
            <?php endif; ?>
            <div class="clearfix"></div>
            <?php echo $modules->render('wf-dashboard-top', array('style' => 'xhtml'), null); ?>
        	<?php echo $modules->render('wf-dashboard-left', array('style' => 'xhtml'), null); ?>
	</div>
	<div class="span4">
        <div class="well well-small">
        	<div class="module-title nav-header">JWorkflow <?php echo WFVERSION; ?></div>
            <p>
                <a href="http://www.joomlant.org" class="btn btn-success btn-wide btn-small" target="_blank">
                    <span aria-hidden="true" class="icon-home"></span> Visit the website
                </a>
            </p>
            <p>
                <a href="https://github.com/soap/JWorkflow/issues" class="btn btn-primary btn-wide btn-small" target="_blank">
                    <span aria-hidden="true" class="icon-warning"></span> Report an Issue
                </a>
            </p>
            <div class="alert alert-info small">
            	<strong>Please include:</strong>
            	<ul class="unstyled">
            	    <li><small>Joomla Version: <?php echo JVERSION; ?> <?php echo $jv->DEV_STATUS;?></small></li>
            	    <li><small>Workflow Version: <?php echo WFVERSION; ?> <?php echo $wfv->DEV_STATUS;?></small></li>
            	    <li><small>PHP Version: <?php echo phpversion(); ?></small></li>
            	</ul>
            </div>
        </div>
        <?php echo $modules->render('wf-dashboard-right', array('style' => 'xhtml'), null); ?>
	</div>
</div>