<?php if ($this->item->id) : ?>
	<?php foreach ($this->form->getFieldset('trigger_config') as $field) : ?>
		<div class="control-group <?php echo $field->class; ?>" <?php echo $field->rel; ?>>
			<div class="control-label"><?php echo $field->label; ?></div>
			<div class="controls"><?php echo $field->input; ?></div>
		</div>
	<?php endforeach; ?>
<?php else : ?>
	<?php echo JText::_('COM_WORKFLOW_TRIGGER_CONFIG_AFTER_SAVE') ?>	
<?php endif;?>	
