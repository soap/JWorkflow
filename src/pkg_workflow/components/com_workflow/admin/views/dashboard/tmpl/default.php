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
<?php if (version_compare(JVERSION, '3.0.0', 'ge')) : ?>
<div class="row-fluid">
    <div class="span8 hidden-phone">
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
<?php else : ?>
<div class="adminform">
    <div class="cpanel-left">
        <div class="cpanel">
            <?php if ($this->user->authorise('core.admin')) : ?>
                <div class="icon-wrapper">
                    <div class="icon">
                        <a class="modal thumbnail btn" rel="{handler: 'iframe', size: {x: 875, y: 550}, onClose: function() {}}" href="<?php echo JRoute::_('index.php?option=com_config&view=component&component=com_workflow&tmpl=component');?>">
                            <?php echo JHtml::image('com_workflow/workflow/header/icon-48-config.png', JText::_('COM_WORKFLOW_DASHBOARD_CONFIG'), null, true); ?>
                            <span><?php echo JText::_('COM_WORKFLOW_DASHBOARD_CONFIG');?></span>
                        </a>
                    </div>
                </div>
            <?php endif; ?>
            <div class="clr clearfix"></div>
            <?php echo $modules->render('wf-dashboard-top', array('style' => 'xhtml'), null); ?>
        </div>
        <div class="row-fluid">
        	<div class="span12">
        		<?php echo $modules->render('wf-dashboard-left', array('style' => 'xhtml'), null); ?>
        	</div>
        </div>
    </div>
    <div class="cpanel-right width-40">
        <div class="well well-small">
        	<h3>Workflow <?php echo WFVERSION; ?></h3>
            <div class="button2-left">
              <div class="blank">
            		<a href="http://www.joomlant.com" class="button" target="_blank">
            		    Visit the Website
            		</a>
              </div>
            </div>
            <div class="button2-left">
              <div class="blank">
            		<a href="https://github.com/soap/Workflow/issues" class="button" target="_blank">
            		    Report an Issue
            		</a>
              </div>
            </div>
            <div class="clr"></div>
            <h4>Please include:</h4>
        	<ul class="unstyled">
        	    <li><small>Joomla Version: <?php echo JVERSION; ?> <?php echo $jv->DEV_STATUS;?></small></li>
        	    <li><small>Workflow Version: <?php echo WFVERSION; ?> <?php echo $wfv->DEV_STATUS;?></small></li>
        	    <li><small>PHP Version: <?php echo phpversion(); ?></small></li>
        	</ul>
        </div>
        <?php echo $modules->render('wf-dashboard-right', array('style' => 'xhtml'), null); ?>
    </div>
</div>
<?php endif; ?>