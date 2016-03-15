<?php echo $this->Form->create("FileManager.Myfile", array('type' => 'file', 'url' => array('plugin' => 'file_manager', 'controller' => 'file_storage', 'action' => 'upload', '?'=>['debugger'=>2]))); ?>
<fieldset>
									<legend>Upload a File</legend>
		<?php 
			echo $this->Form->file('files][', array('id'=>'MyfileFile', 'multiple'=>'multiple'));
			echo $this->Form->error('files');
		?>
		</fieldset>
	<?php echo $this->Form->end('Upload'); ?>