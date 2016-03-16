<?php foreach ($media as $m):?>
<div>
	<div class="media-item text-center">
	<div class="actions text-left">
		<!-- <a 
			href="#" 
			class="edit" 
			data-toggle="popover"
			data-html=true 
			title="" 
			data-content="<form class='editor'><label>title</label><input class='title-input' value='{{model.title}}' type='text'/><label>Description</label><textarea class='description-input' type='text'>{{model.description}}</textarea><button type='submit' class='btn btn-success'>Save</button></form>" 
			data-original-title="edit {{model.title}}"><span class="glyphicon glyphicon-edit"></span></a> -->
			<a href="#" href="javascript:void(0);" data-id="<?php echo $m['FileStorage']['id']; ?>" class="delete remove-media" title="Delete"><span class="glyphicon glyphicon-remove-circle"></span></a>
			<a href="#" class="makethumbnail" title="Make Thumbnail"><span class="glyphicon glyphicon-ok-circle"></span></a>
			<input type="checkbox" name="data[FileStorage][file][]" value="<?php echo $m['FileStorage']['id']; ?>" style="float:right;">
		</div>
		<div class="content">
			<?php /** For Images */ if($this->Image->isImage($m['FileStorage'])): ?>
						<?php echo $this->Image->display($m['FileStorage'], null, array('width' => 100, 'height' => 100)); ?>
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
				<img src="/FileManager/img/<?php echo $icon; ?>" />
		<?php endif; ?>
		</div>
		<div class="title"><?php echo $m['FileStorage']['filename']; ?></div>
		<?php if ($this->request->query('CKEditor')) : ?>
			<a href="javascript:void(0);" data-url="<?php echo $this->Image->imageUrl($m['FileStorage']); ?>" class="select-media tiny expand button split">Select<span data-dropdown="media-drop-<?php echo $m['FileStorage']['id']; ?>"></span></a>
			<?php else : ?>
			<a href="<?php echo $this->Image->imageUrl($m['FileStorage']); ?>" target="_blank">View</a>
			<?php endif; ?>
		</div>
	</div>
<?php endforeach; ?>

<?php 
// set the contextual breadcrumb items
$this->set('context_crumbs', array('crumbs' => array(
	$this->Html->link(__('Admin Dashboard'), '/admin'),
	'Media Manager',
)));