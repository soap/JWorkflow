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
<div id="j-sidebar-container" class="span2">
	<?php echo $this->sidebar; ?>
</div>
<div id="j-main-container" class="span10">
	<div class="row-fluid">
	<div class="span6">
            <?php if ($this->user->authorise('core.admin')) : ?>
                <a class="thumbnail btn pull-left" href="<?php echo JRoute::_('index.php?option=com_config&view=component&component=com_workflow');?>">
                    <?php echo JHtml::image('com_workflow/workflow/header/icon-48-config.png', JText::_('COM_WORKFLOW_DASHBOARD_CONFIG'), null, true); ?>
                    <span class="small"><?php echo JText::_('COM_WORKFLOW_DASHBOARD_CONFIG');?></span>
                </a>
            <?php endif; ?>
            <div class="clearfix"></div>
            <?php echo $modules->render('wf-dashboard-top', array('style' => 'xhtml'), null); ?>
        		<?php echo $modules->render('wf-dashboard-left', array('style' => 'xhtml'), null); ?>
	</div>
	<div class="span4" style="margin-left: 0;">
        <div class="well well-small">
        	<div class="module-title nav-header">JWorkflow <?php echo WFVERSION; ?></div>
            <p>
                <a href="http://www.joomlant.org" class="btn btn-success btn-wide btn-small" target="_blank">
                    <span aria-hidden="true" class="icon-home"></span> Visit the website
                </a>
            </p>
            <p>
                <a href="https://github.com/soap/Workflow/issues" class="btn btn-primary btn-wide btn-small" target="_blank">
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
</div>