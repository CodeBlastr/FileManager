<?PHP
/* @var $this View */
?>
<div id="file-add" class="file add">
    <h2>Submit Your File</h2>

    <?php
    echo $this->Form->create('File', array('type' => 'file'));
    echo $this->Form->hidden('File.user_id', array('value'=> $this->Session->read('Auth.User.id')));

//
//    $options = array('audio'=>'Audio','video'=>'Video');
//    $attributes = array('legend'=>'Type of File');
//    echo $this->Form->radio('File.type', $options, $attributes);


    echo $this->Form->input('File.filename', array('type'=>'file', 'label' => 'Upload a file from your computer:')); // , 'accept' => 'audio/* video/*'

    echo $this->Form->input('File.submittedurl', array('type'=>'text', 'label' => 'Alternatively enter the URL of a file that is already online:'));

    echo $this->Form->input('File.title', array('type'=>'text', 'label' => 'Title:'));

    echo $this->Form->input('File.description', array('type'=>'textarea', 'label' => 'Description:'));

    echo $this->Form->end('Submit');
    ?>
</div>