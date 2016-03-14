<?PHP
/* @var $this View */
?>
<div id="file-add" class="file add">
    <h2>Submit Your File</h2>

    <?php
    echo $this->Form->create('Myfile', array('type' => 'file'));
    echo $this->Form->hidden('Myfile.user_id', array('value'=> $this->Session->read('Auth.User.id')));

//
//    $options = array('audio'=>'Audio','video'=>'Video');
//    $attributes = array('legend'=>'Type of File');
//    echo $this->Form->radio('Myfile.type', $options, $attributes);


    echo $this->Form->input('Myfile.filename', array('type'=>'file', 'label' => 'Upload a file from your computer:')); // , 'accept' => 'audio/* video/*'

    echo $this->Form->input('Myfile.submittedurl', array('type'=>'text', 'label' => 'Alternatively enter the URL of a file that is already online:'));

    echo $this->Form->input('Myfile.title', array('type'=>'text', 'label' => 'Title:'));

    echo $this->Form->input('Myfile.description', array('type'=>'textarea', 'label' => 'Description:'));

    echo $this->Form->end('Submit');
    ?>
</div>