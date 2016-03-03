<?php
//Setting the $selected variable on element call will control how many items can be selected
$multiple = isset($multiple) && is_bool($multiple) ? $multiple : false;
$wrapperclass = isset($class) ? $class : 'thumbnail pull-left';
//Format the media regardless of how it sent
$bootstrap = isset($bootstrap) ? $bootstrap :  3;
$selecteditems = array();
if(isset($media) && !empty($media)) {
	foreach ($media as $m) {
		if(isset($m['File'])) {
			$m['File']['selected'] = true;
			$selecteditems[] = $m['File'];
		} else {
			$m['selected'] = true;
			$selecteditems[] = $m;
		}
	}
}
$thumbnail = isset($this->request->data['FileThumbnail'][0]) ? json_encode($this->request->data['FileThumbnail'][0]) : false;
$selecteditems = json_encode($selecteditems); ?>

<div id="MediaSelector">
	<a data-toggle="modal" href="#mediaBrowserModal" class="btn btn-primary btn-xs">Select File</a>
	<div id="mediaSelected" class="clearfix"></div>
</div>
<script type="template/javascript" id="mediaModalTemplate">
	<div class="modal fade" id="mediaBrowserModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
      			<div class="modal-body">
        			<div id="mediaBrowser"> <br>&nbsp;&nbsp;Loading Browser...<br><br> </div>
      			</div>
      			<div class="modal-footer"></div>
      		</div>
	 	</div>
	</div>
</script>

<script type="text/javascript">
	$($('#mediaModalTemplate').html()).appendTo('body');
	var thumbnail = <?php echo $thumbnail ? $thumbnail : 'false' ?>;
	var selectable = true;
	var wrapperclass = '<?php echo $wrapperclass; ?>';
	var selecteditems = <?php echo $selecteditems; ?>;
	var baseUrl = '<?php echo $this->Html->url(array('plugin' => 'FileManager', 'controller' => 'file_manager', 'action' => 'File', '?'=>array('limit'=>50))); ?>';
</script>

<?php if($bootstrap == 2): ?>
	<script data-main="/FileManager/js/mediabrowser_boot2/build/media-min.js" src="/FileManager/js/mediabrowser/scripts/require.js"></script>
<?php else: ?>
	<!--script data-main="/FileManager/js/mediabrowser/build/media-min.js" src="/FileManager/js/mediabrowser/scripts/require.js"></script-->
	<script data-main="/FileManager/js/mediabrowser/build/media-min.js" src="/FileManager/js/mediabrowser/scripts/require.js"></script>
	<?php // this is used instead of the line above to make edits... <script data-main="/FileManager/js/mediabrowser_boot2/scripts/mediabrowser.js" src="/FileManager/js/mediabrowser/scripts/require.js"></script> ?>
<?php endif; ?>