<?PHP
/* @var $this View */
?>
<div id="file-edit" class="file edit">
    <h2>Edit Your File</h2>

    <?php
    echo $this->Form->create('File', array('type' => 'file'));
    echo $this->Form->input('File.id');

    if($this->request->data['File']['type'] == 'video') {
        // thumbnail selector
            $options = array(
                '0'=>'<img src="/theme/default/files/thumbs/'.$this->data['File']['id'].'_0000.jpg" height="200" width="200" />',
                '1'=>'<img src="/theme/default/files/thumbs/'.$this->data['File']['id'].'_0001.jpg" height="200" width="200" />',
                '2'=>'<img src="/theme/default/files/thumbs/'.$this->data['File']['id'].'_0002.jpg" height="200" width="200" />',
                '3'=>'<img src="/theme/default/files/thumbs/'.$this->data['File']['id'].'_0003.jpg" height="200" width="200" />',
                '4'=>'<img src="/theme/default/files/thumbs/'.$this->data['File']['id'].'_0004.jpg" height="200" width="200" />'
                );
            $attributes = array('legend'=>'Video Preview Image');
            echo $this->Form->radio('File.thumbnail', $options, $attributes);
    }

    echo $this->Form->input('File.title', array('between'=>'<br />','type'=>'text', 'label' => 'Title:'));

    echo $this->Form->input('File.description', array('between'=>'<br />','type'=>'textarea', 'label' => 'Description:'));


    echo $this->Form->end('Save Changes');
    ?>
</div>