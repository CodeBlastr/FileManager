<?php
//default image
$thumbnailImage = !empty($theFile['File']['thumbnail']) ? '/theme/default/files/'.strtolower(ZuhaInflector::pluginize($theFile['File']['model'])).'/images/thumbs/'.$theFile['File']['id'].'_000'.$theFile['File']['thumbnail'].'.jpg' : '/img/noImage.jpg';


// load the star ratings files
echo $this->Html->script('/ratings/js/jquery.ui.stars.min');
echo $this->Html->css('/ratings/css/jquery.ui.stars.min');
?>

<div id="fileViewBox">

    <?php
    if($theFile['File']['type'] == 'audio') {
        echo $this->Html->video($theFile['File']['filename'], array('width'=>'709', 'height'=>'404', 'title'=>$theFile['File']['title']));
    }
    elseif($theFile['File']['type'] == 'videos') {
        echo $this->Html->video($theFile['File']['filename'], array('width'=>'709', 'height'=>'404', 'poster'=>$thumbnailImage, 'title'=>$theFile['File']['title']));
    }
    ?>
    <div id="fileView_titleBox">
        <div id="fileView_titleInfo">
            <?php
            echo '<h2>';
            echo !empty($theFile['File']['title']) ? $theFile['File']['title'] : '(untitled)';
            echo '</h2>';
            ?>
        </div><!-- #fileView_titleInfo -->
        <div id="fileView_ratingBox">
            <?php
            echo $this->Rating->display(array(
                'item' => $theFile['File']['id'],
                'type' => 'radio',
                'stars' => 10,
                'value' =>  $theFile['File']['rating'],
                'createForm' => array('url' => array($theFile['File']['id'], 'rate' =>  $theFile['File']['id'], 'redirect' => false), 'label'=>false)
                ));
            ?>
        </div>
    </div>

    <?php echo '<div class="fileViewDescription">' . $theFile['File']['description'] . '</div>'; ?>

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