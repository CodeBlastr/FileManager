<div id="files-my" class="files my">
    <h2>My Files</h2>
    <p>Here is a listing of everything that you've uploaded so far.</p>

    <?php
    if(!empty($files)) {
            echo '<ul>';
            foreach($files as $medium) {
                $thumbnailImage = !empty($medium['File']['filename']) ? '/theme/default/files/images/'.$medium['File']['filename'].'.'.$medium['File']['extension'] : '/img/noImage.jpg';
                ?>
                    <li id="files-my_Li">
                        <div id="files-my_Thumbnail">
                            <a href="/file_manager/files/view/<?php echo $medium['File']['id'] ?>"><img src="<?php echo $thumbnailImage ?>" alt="" height="200" width="200" /></a>
                        </div>
                        <div id="files-my_Title">
                            <a href="/file_manager/files/view/<?php echo $medium['File']['id'] ?>"><?php echo !empty($medium['File']['title']) ? $medium['File']['title'] : '(untitled)' ?></a>
                        </div>
                        <div id="files-my_Description">
                            <?php echo $medium['File']['description'] ?>
                        </div>
                        <div id="files-my_actions">
                            <a href="/file_manager/files/edit/<?php echo $medium['File']['id'] ?>">Edit</a>
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
