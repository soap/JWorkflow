<?php
defined('_JEXEC') or die;
//JHtml::addIncludePath(JPATH_COMPONENT.'helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
?>
<script type="text/javascript">
	// Attach a behaviour to the submit button to check validation.
	Joomla.submitbutton = function(task)
	{
		var form = document.id('guard-form');
		if (task == 'guard.cancel' || document.formvalidator.isValid(form)) {
			<?php //echo $this->form->getField('description')->save(); ?>
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
	method="post" name="adminForm" id="guard-form" class="form-validate">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<ul class="adminformlist">
				<li>
					<?php echo $this->form->getLabel('id'); ?>
					<?php echo $this->form->getInput('id'); ?>
				</li>
				
				<li>
					<?php echo $this->form->getLabel('title'); ?>
					<?php echo $this->form->getInput('title'); ?>
				</li>
								
				<li>
					<?php echo $this->form->getLabel('transition_id'); ?>
					<?php echo $this->form->getInput('transition_id'); ?>
				</li>
							
				<li>
					<?php echo $this->form->getLabel('plugin_id'); ?>
					<?php echo $this->form->getInput('plugin_id'); ?>
				</li>
				
				<li>
					<?php echo $this->form->getLabel('namespace'); ?>
					<?php echo $this->form->getInput('namespace'); ?>
				</li>
				
				<li>
					<?php echo $this->form->getLabel('published'); ?>
					<?php echo $this->form->getInput('published'); ?>
				</li>

				<li>
					<?php echo $this->form->getLabel('ordering'); ?>
					<?php echo $this->form->getInput('ordering'); ?>
				</li>

				<li>
					<?php echo $this->form->getLabel('note'); ?>
					<?php echo $this->form->getInput('note'); ?>
				</li>
			</ul>

		</fieldset>
	</div>
	<div class="width-40 fltrt">
		<?php echo JHtml::_('sliders.start','guard-sliders-'.$this->item->id, array('useCookie' => 1)); ?>

		<?php echo $this->loadTemplate('config'); ?>
		<?php echo JHtml::_('sliders.end'); ?>

	</div>
	<div class="clr"></div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>