<?php
defined('_JEXEC') or die;
//JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');

$user		= JFactory::getUser();
$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));
?>
<form action="<?php echo JRoute::_('index.php?option=com_workflow&view=triggerinstances');?>" method="post" name="adminForm" id="adminForm">

<?php if (!empty( $this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
<?php else : ?>
	<div id="j-main-container">
<?php endif;?>
	<span class="label label-primary"><?php echo $this->workflow->title?></span>
	<span class="label label-info"><?php echo $this->transition->title?></span>
	<table class="table table-striped">
		<thead>
			<tr>
				<th width="1%">
					<input type="checkbox" name="toggle" value="" onclick="checkAll(this)" />
				</th>
				<th class="center">
					<?php echo JHtml::_('grid.sort', 'JGLOBAL_TITLE', 'a.namespace', $listDirn, $listOrder); ?>
				</th>
				<th width="10%" class="center">
					<?php echo JHtml::_('grid.sort', 'COM_WORKFLOW_HEADING_TRIGGER_TYPE', 'p.group', $listDirn, $listOrder); ?>					
				</th>
				<th width="20%" class="center">
					<?php echo JHtml::_('grid.sort', 'COM_WORKFLOW_HEADING_TRANSITION', 'a.transition_id', $listDirn, $listOrder); ?>
				</th>				
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'JPUBLISHED', 'a.published', $listDirn, $listOrder); ?>
				</th>
				<th width="10%" class="center">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_CREATED_BY', 'a.created_by', $listDirn, $listOrder); ?>
				</th>
				<th width="10%" class="center">
					<?php echo JHtml::_('grid.sort', 'JDATE', 'a.created', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" class="nowrap">
					<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="13">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php foreach ($this->items as $i => $item) :
			$item->max_ordering = 0; //??
			$ordering	= ($listOrder == 'a.ordering');
			$canCreate	= $user->authorise('core.create',		'com_workflow');
			$canEdit	= $user->authorise('core.edit',			'com_workflow.guard.'.$item->id);
			$canCheckin	= $user->authorise('core.manage',		'com_checkin') || $item->checked_out == $user->get('id') || $item->checked_out == 0;
			$canChange	= $user->authorise('core.edit.state',	'com_workflow.guard.'.$item->id) && $canCheckin;
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td>
					<?php if ($item->checked_out) : ?>
						<?php echo JHtml::_('jgrid.checkedout', $i, $item->editor, $item->checked_out_time, 'triggerinstances.', $canCheckin); ?>
					<?php endif; ?>
					<?php if ($canCreate || $canEdit) : ?>
					<a href="<?php echo JRoute::_('index.php?option=com_workflow&task=triggerinstance.edit&id='.$item->id);?>">
						<?php echo $this->escape($item->title); ?></a>
					<?php else : ?>
						<?php echo $this->escape($item->title); ?>
					<?php endif; ?>
					<p class="smallsub">
						<?php echo $this->escape($item->trigger->getConfigSummary());?>
					</p>
				</td>
				<td class="center">
					<?php echo $this->escape($item->trigger_type);?>
				</td>
				<td class="center">
					<?php echo $this->escape($item->transition_title); ?>
				</td>				
				<td class="center">
					<?php echo JHtml::_('jgrid.published', $item->published, $i, 'triggerinstances.', $canChange); ?>
				</td>
				<td class="center">
					<?php echo $this->escape($item->author_name); ?>
				</td>
				<td class="center">
					<?php echo JHTML::_('date',$item->created, 'Y-m-d'); ?>
				</td>
				<td class="center">
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="transition_id" value="<?php echo $this->transition->id; ?>" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
