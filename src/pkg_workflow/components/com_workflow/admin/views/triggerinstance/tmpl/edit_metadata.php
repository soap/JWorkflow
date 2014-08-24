<?php
echo JHtml::_('sliders.panel', JText::_('COM_WORKFLOW_METADATA_FIELDSET_LABEL'), 'metadata-options'); ?>
<fieldset class="panelform">
	<ul class="adminformlist">
		<?php if ($this->item->created) : ?>
			<li>
				<?php echo $this->form->getLabel('created'); ?>
				<?php echo $this->form->getInput('created'); ?>
			</li>
		<?php endif; ?>

		<?php if (intval($this->item->modified)) : ?>
			<li>
				<?php echo $this->form->getLabel('modified'); ?>
				<?php echo $this->form->getInput('modified'); ?>
			</li>
		<?php endif; ?>
	</ul>
</fieldset>