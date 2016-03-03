<div id="file-index" class="files view">
    <h2>Files</h2>

    <?php
    if(!empty($files)) {
            echo '<ul>';
            foreach($files as $medium) {
                #debug($medium);
                $thumbnailImage = !empty($medium['File']['thumbnail']) ? '/theme/default/files/thumbs/'.$medium['File']['id'].'_000'.$medium['File']['thumbnail'].'.png' : '/img/noImage.jpg';
                ?>
                    <li class="filesIndexLi">
                        <div class="filesIndexThumbnail">
                            <a href="/file_manager/files/view/<?php echo $medium['File']['id'] ?>"><img src="<?php echo $thumbnailImage ?>" alt="<?php echo $medium['File']['title'] ?>" height="200" width="200"/></a>
                        </div>
                        <div class="filesIndexTitle">
                            <a href="/file_manager/files/view/<?php echo $medium['File']['id'] ?>"><?php echo !empty($medium['File']['title']) ? $medium['File']['title'] : '(untitled)' ?></a>
                        </div>
                        <div class="filesIndexDescription">
                            <?php echo $medium['File']['description'] ?>
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