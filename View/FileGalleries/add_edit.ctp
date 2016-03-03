<?php echo $this->Form->create('File.FileGallery'); ?>
<?php if(isset($this->request->data['FileGallery']['id'])) { echo $this->Form->input('FileGallery.id'); } ?>
<?php echo $this->Form->input('FileGallery.title', array('label' => 'Gallery Title', 'class' => 'form-control')); ?>
<?php echo $this->Form->input('FileGallery.description', array('label' => 'Gallery Description', 'class' => 'form-control')); ?>

<?php echo $this->Element('File.selector', array('theme' => 'boot3', 'File' => $this->request->data['File'], 'multiple' => true)); ?>

<?php echo $this->Form->submit('Save Gallery'); ?>
<?php echo $this->Form->end(); ?>