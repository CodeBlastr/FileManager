<div id="file-index" class="files view">
    <h2>Files</h2>

    <?php
    if(!empty($files)) {
            echo '<ul>';
            foreach($files as $medium) {
                #debug($medium);
                $thumbnailImage = !empty($medium['Myfile']['thumbnail']) ? '/theme/default/files/thumbs/'.$medium['Myfile']['id'].'_000'.$medium['Myfile']['thumbnail'].'.png' : '/img/noImage.jpg';
                ?>
                    <li class="filesIndexLi">
                        <div class="filesIndexThumbnail">
                            <a href="/file_manager/files/view/<?php echo $medium['Myfile']['id'] ?>"><img src="<?php echo $thumbnailImage ?>" alt="<?php echo $medium['Myfile']['title'] ?>" height="200" width="200"/></a>
                        </div>
                        <div class="filesIndexTitle">
                            <a href="/file_manager/files/view/<?php echo $medium['Myfile']['id'] ?>"><?php echo !empty($medium['Myfile']['title']) ? $medium['Myfile']['title'] : '(untitled)' ?></a>
                        </div>
                        <div class="filesIndexDescription">
                            <?php echo $medium['Myfile']['description'] ?>
                        </div>
                    </li>
                <?php
            }//foreach()
            echo '</ul>';
    }//if(videos)
    else {
            echo '<div class="error">No file found.</div>';
    }
    ?>
</div>