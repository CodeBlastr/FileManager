<?php
//default image
$thumbnailImage = !empty($theFile['Myfile']['thumbnail']) ? '/theme/default/files/'.strtolower(ZuhaInflector::pluginize($theFile['Myfile']['model'])).'/images/thumbs/'.$theFile['Myfile']['id'].'_000'.$theFile['Myfile']['thumbnail'].'.jpg' : '/img/noImage.jpg';


// load the star ratings files
echo $this->Html->script('/ratings/js/jquery.ui.stars.min');
echo $this->Html->css('/ratings/css/jquery.ui.stars.min');
?>

<div id="fileViewBox">

    <?php
    if($theFile['Myfile']['type'] == 'audio') {
        echo $this->Html->video($theFile['Myfile']['filename'], array('width'=>'709', 'height'=>'404', 'title'=>$theFile['Myfile']['title']));
    }
    elseif($theFile['Myfile']['type'] == 'videos') {
        echo $this->Html->video($theFile['Myfile']['filename'], array('width'=>'709', 'height'=>'404', 'poster'=>$thumbnailImage, 'title'=>$theFile['Myfile']['title']));
    }
    ?>
    <div id="fileView_titleBox">
        <div id="fileView_titleInfo">
            <?php
            echo '<h2>';
            echo !empty($theFile['Myfile']['title']) ? $theFile['Myfile']['title'] : '(untitled)';
            echo '</h2>';
            ?>
        </div><!-- #fileView_titleInfo -->
        <div id="fileView_ratingBox">
            <?php
            echo $this->Rating->display(array(
                'item' => $theFile['Myfile']['id'],
                'type' => 'radio',
                'stars' => 10,
                'value' =>  $theFile['Myfile']['rating'],
                'createForm' => array('url' => array($theFile['Myfile']['id'], 'rate' =>  $theFile['Myfile']['id'], 'redirect' => false), 'label'=>false)
                ));
            ?>
        </div>
    </div>

    <?php echo '<div class="fileViewDescription">' . $theFile['Myfile']['description'] . '</div>'; ?>

</div><!-- #FileFileBox -->

<script type="text/javascript">
    $(document).ready(function() {
        $('#FileViewForm').stars({
            split:2,
            cancelShow:false,
            callback: function(ui, type, value) {
                ui.$form.submit();
            }
        });
    });
</script>