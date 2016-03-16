<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">
<div>
<table cellpadding="0" cellspacing="0" class="table table-fixed-header">
 <thead>
  <tr>
		<th><?php echo __('#'); ?></th>
		<th><?php echo __('Preview'); ?></th>
		<th><?php echo $this->Paginator->sort('model', __('Type'), array('view'=>'thumb')); ?>
		<?php if ($this->Paginator->sortKey() == 'model'): ?>
    <i class='fa fa-sort-<?php echo $this->Paginator->sortDir() === 'asc' ? 'up' : 'down'; ?>'></i>
		<?php else: ?>
				<i class='fa fa-sort'></i>
		<?php endif; ?>
		</th>
    <th><?php echo $this->Paginator->sort('filename'); ?>
		<?php if ($this->Paginator->sortKey() == 'filename'): ?>
    <i class='fa fa-sort-<?php echo $this->Paginator->sortDir() === 'asc' ? 'up' : 'down'; ?>'></i>
		<?php else: ?>
				<i class='fa fa-sort'></i>
		<?php endif; ?>
		</th>
		<th><?php echo $this->Paginator->sort('filesize'); ?>
		<?php if ($this->Paginator->sortKey() == 'filesize'): ?>
    <i class='fa fa-sort-<?php echo $this->Paginator->sortDir() === 'asc' ? 'up' : 'down'; ?>'></i>
		<?php else: ?>
				<i class='fa fa-sort'></i>
		<?php endif; ?>
		</th>
		<th><?php echo $this->Paginator->sort('adapter', __('Storage')); ?>
		<?php if ($this->Paginator->sortKey() == 'adapter'): ?>
    <i class='fa fa-sort-<?php echo $this->Paginator->sortDir() === 'asc' ? 'up' : 'down'; ?>'></i>
		<?php else: ?>
				<i class='fa fa-sort'></i>
		<?php endif; ?>
		</th>
		<th><?php echo $this->Paginator->sort('created'); ?>
		<?php if ($this->Paginator->sortKey() == 'created'): ?>
    <i class='fa fa-sort-<?php echo $this->Paginator->sortDir() === 'asc' ? 'up' : 'down'; ?>'></i>
		<?php else: ?>
				<i class='fa fa-sort'></i>
		<?php endif; ?>
		</th>
		<th><?php echo $this->Paginator->sort('modified'); ?>
		<?php if ($this->Paginator->sortKey() == 'modified'): ?>
    <i class='fa fa-sort-<?php echo $this->Paginator->sortDir() === 'asc' ? 'up' : 'down'; ?>'></i>
		<?php else: ?>
				<i class='fa fa-sort'></i>
		<?php endif; ?>
		</th>
  </tr>
 </thead>
 <tbody>
<?php foreach ($media as $m):?>
 <tr>
	<td><input type="checkbox" name="data[FileStorage][file][]" value="<?php echo $m['FileStorage']['id']; ?>" ></td>
  <td>
	<?php /** For Images */ 
					if($this->Image->isImage($m['FileStorage'])): ?>
						<?php echo $this->Image->display($m['FileStorage'], null, array('width' => 30, 'height' => 30)); ?>
			<?php endif; ?>
			<?php /** For Documents */ if($m['FileStorage']['model'] == "FileStorage"): ?>
			<?php
				switch ($m['FileStorage']['mime_type']) {
					case "application/pdf":
						$icon = "pdf-icon.png";
						break;
					default: 
						$icon = "default-icon.png";
				}
			?>
				<img src="/FileManager/img/<?php echo $icon; ?>" width="30" height="30" />
		<?php endif; ?>
		</td>
		<td>
		<?php echo str_replace('Storage', '', $m['FileStorage']['model']); ?>
		</td>
		<td><a href="<?php echo $this->Image->imageUrl($m['FileStorage']); ?>" target="_blank"><?php echo  $m['FileStorage']['filename']; ?></a>
		</td>
		<td>
		<?php echo  $this->Number->toReadableSize($m['FileStorage']['filesize']); ?>
		</td>
		<td>
		<?php echo  $m['FileStorage']['adapter']; ?>
		</td>
		<td>
		<?php echo  ZuhaInflector::datify($m['FileStorage']['created']); ?>
		</td>
		<td>
		<?php echo  ZuhaInflector::datify($m['FileStorage']['modified']); ?>
		</td>
	</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>
<?php 
// set the contextual breadcrumb items
$this->set('context_crumbs', array('crumbs' => array(
	$this->Html->link(__('Admin Dashboard'), '/admin'),
	'Media Manager',
)));