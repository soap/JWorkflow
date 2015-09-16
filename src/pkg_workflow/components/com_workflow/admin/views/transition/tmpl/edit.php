<?php
use Joomla\Registry\Registry;
use Composer\Autoload\ClassLoader;
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
JHtml::_('jquery.framework');
JHtml::_('formbehavior.chosen');

$data = array();
$data['selector'] 		= '.advancedSelectUsers';
$data['type'] 			= 'GET';
$data['url']			= 'index.php?option=com_workflow&task=user.getUsers&tmpl=component&format=json';
$data['dataType']		= 'json';
$data['jsonTermKey']	= 'search';
$data['afterTypeDelay'] = '500';
$data['minTermLength']	= '3';

$options = new Registry($data);

JHtml::_('formbehavior.ajaxchosen', $options);

JHtml::_('script', 'com_workflow/workflow/workflow.js', false, true, false, false, false);
JHtml::_('script', 'com_workflow/workflow/form.js', false, true, false, false, false);
?>
<script type="text/javascript">
	// Attach a behaviour to the submit button to check validation.
	Joomla.submitbutton = function(task)
	{
		var form = document.id('transition-form');
		if (task == 'transition.cancel' || document.formvalidator.isValid(form)) {
			Joomla.submitform(task, form);
		}
		else {
			<?php JText::script('COM_WORKFLOW_ERROR_N_INVALID_FIELDS'); ?>
			// Count the fields that are invalid.
			var elements = form.getElements('fieldset').concat(Array.from(form.elements));
			var invalid = 0;

			for (var i = 0; i < elements.length; i++) {
				if (document.formvalidator.validate(elements[i]) == false) {
					valid = false;
					invalid++;
				}
			}

			alert(Joomla.JText._('COM_WORKFLOW_ERROR_N_INVALID_FIELDS').replace('%d', invalid));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_workflow&layout=edit&id='.(int) $this->item->id); ?>"
	method="post" name="adminForm" id="transition-form" class="form-validate">
	
	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>
	
	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>
		
		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_WORKFLOW_DETAILS', true)); ?>
		<div class="row-fluid">
			<div class="span9">
				<div class="row-fluid">
					<div class="span6 form-vertical" >
						<?php echo $this->form->renderField('workflow_id'); ?>
						<div class="control-group">
							<div class="control-label">
								<?php echo $this->form->getLabel('target_state_id')?>
							</div>		
							<div class="controls" id="jform_target_state_id_element">
								<div id="jform_target_state_id_reload">
									<?php echo $this->form->getInput('target_state_id'); ?>
								</div>
							</div>
						</div>
						<?php echo $this->form->renderField('system_path')?>
						<?php echo $this->form->renderField('ordering'); ?>
					</div>
					<div class="span3 form-vertical">
						<?php echo $this->form->renderField('fromstates')?>
						<?php echo $this->form->renderField('allowed_groups')?>
						<?php echo $this->form->renderField('allowed_users')?>
					</div>
				</div>
			</div>
			<div class="span3">
				<?php echo JLayoutHelper::render('joomla.edit.global', $this); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'publishing', JText::_('JGLOBAL_FIELDSET_PUBLISHING', true)); ?>
		<div class="row-fluid form-horizontal-desktop">
			<div class="span6">
				<?php echo JLayoutHelper::render('joomla.edit.publishingdata', $this); ?>
			</div>
			<div class="span6">
				<?php echo JLayoutHelper::render('joomla.edit.metadata', $this); ?>
			</div>
		</div>
		<?php echo JHtml::_('bootstrap.endTab'); ?>

		<?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>

		<?php if (isset($assoc)) : ?>
			<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'associations', JText::_('JGLOBAL_FIELDSET_ASSOCIATIONS', true)); ?>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
		<?php endif; ?>

		<?php echo JHtml::_('bootstrap.endTabSet'); ?>
	</div>
	
	<?php echo $this->form->getInput('elements')?>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="view" value="transition" />
	<?php echo JHtml::_('form.token'); ?>
</form>