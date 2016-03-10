<?php echo $this->Form->create("File", array('type' => 'file', 'url' => array('plugin' => 'file_manager', 'controller' => 'file_storage', 'action' => 'upload', '?'=>['debugger'=>2]))); ?>
<fieldset>
									<legend>Upload a File</legend>
		<?php 
			echo $this->Form->file('file');
			echo $this->Form->error('file');
		?>
		</fieldset>
	<?php echo $this->Form->end('Upload'); ?>