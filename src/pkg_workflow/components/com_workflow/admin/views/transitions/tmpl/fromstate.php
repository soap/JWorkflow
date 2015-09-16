<?php
defined('_JEXEC') or die;
JHtml::_('behavior.tooltip');

$user		= JFactory::getUser();
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
$saveOrder	= $listOrder == 'a.ordering';
?>
<form action="<?php echo JRoute::_('index.php?option=com_workflow&view=transitions&layout=fromstate');?>" method="post" name="adminForm" id="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php
			// Search tools bar
			echo JLayoutHelper::render('searchtools.default', array('view' => $this, 'options'=>array('canChangeWorkflow'=>false)), JPATH_COMPONENT_ADMINISTRATOR.'/layouts');
		?>
		<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
		<?php else : ?>
		<table class="table table-striped table-bordered">
			<thead>
				<tr>
					<th rowspan="2" width="1%" class="nowrap center hidden-phone">
						<?php echo JHtml::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>
					</th>
					<th rowspan="2" width="1%" class="hidden-phone">
						<?php echo JHtml::_('grid.checkall'); ?>
					</th>
					<th rowspan="2" class="center">
						<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.title', $listDirn, $listOrder); ?>
					</th>								
					<th rowspan="2" width="10%">
						<?php echo JHtml::_('grid.sort', 'COM_WORKFLOW_HEADING_TARGET_STATE', 'target_state_title', $listDirn, $listOrder); ?>
					</th>			
							
					<?php if (!$this->state->get('filter.workflow_id')) : ?>				
					<th rowspan="2" width="10%">
						<?php echo JHtml::_('grid.sort', 'COM_WORKFLOW_HEADING_WORKFLOW', 'a.workflow_id', $listDirn, $listOrder); ?>
					</th>
					<?php endif;?>
					<th colspan="<?php echo count($this->states)?>" class="center">
						<?php echo JText::_('COM_WORKFLOW_HEADING_FROM_STATE')?>
					</th>
				</tr>
				<tr>
					<?php foreach($this->states as $state) : ?>
						<th class="center">
							<?php echo $this->escape($state->title)?>
						</th>
					<?php endforeach;?>
					<th width="1%" class="center">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td colspan="15">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
			</tfoot>
			<tbody>
			<?php foreach ($this->items as $i => $item) :
				$item->max_ordering = 0; //??
				$ordering	= ($listOrder == 'a.ordering');
				$canCreate	= $user->authorise('core.create',		'com_workflow');
				$canEdit	= $user->authorise('core.edit',			'com_workflow.transition.'.$item->id);
				$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
				$canChange	= $user->authorise('core.edit.state',	'com_workflow.transition.'.$item->id) && $canCheckin;
			?>
				<tr class="row<?php echo $i % 2; ?>" sortable-group-id="<?php echo $item->workflow_id; ?>">
					<td class="order nowrap center hidden-phone">
							<?php
							$iconClass = '';
							if (!$canChange)
							{
								$iconClass = ' inactive';
							}
							elseif (!$saveOrder)
							{
								$iconClass = ' inactive tip-top hasTooltip" title="' . JHtml::tooltipText('JORDERINGDISABLED');
							}
							?>
							<span class="sortable-handler<?php echo $iconClass ?>">
								<i class="icon-menu"></i>
							</span>
							<?php if ($canChange && $saveOrder) : ?>
								<input type="text" style="display:none" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order " />
							<?php endif; ?>
					</td>
					<td class="center">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>					
					<td>
						<?php if ($item->checked_out) : ?>
							<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'transitions.', $canCheckin); ?>
						<?php endif; ?>
						<?php if ($canCreate || $canEdit) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_workflow&task=transition.edit&id='.$item->id);?>">
							<?php echo $this->escape($item->title); ?></a>
						<?php else : ?>
							<?php echo $this->escape($item->title); ?>
						<?php endif; ?>
						<p class="smallsub">
							<?php if (empty($item->note)) : ?>
								<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias));?>
							<?php else : ?>
								<?php echo JText::sprintf('JGLOBAL_LIST_ALIAS_NOTE', $this->escape($item->alias), $this->escape($item->note));?>
							<?php endif; ?></p>
					</td>
					<td class="center">
						<?php echo $this->escape($item->target_state_title)?>
					</td>
					<?php if (!$this->state->get('filter.workflow_id')) : ?>
					<td class="center">
						<?php echo $this->escape($item->workflow_title); ?>
					</td>
					<?php endif; ?>
					<?php foreach($this->states as $state) : ?>
						<td class="center">
							<?php if (in_array($state->id, $this->fromStates[$item->id])) : ?>
								<?php $class='success'; $text = 'yes'?>
							<?php else: ?>
								<?php $class='danger'; $text='no'?>
							<?php endif?>
							<span class="label label-<?php echo $class?>"><?php echo $text?></span>	
						</td>
					<?php endforeach;?>
					
					<td class="center">
						<?php echo (int) $item->id; ?>
					</td>
				</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php endif; ?>
	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
</form>

