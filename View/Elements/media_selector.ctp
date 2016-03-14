<?php
	//Setting the $selected variable on element call will control how many items can be selected
	$multiple = isset($multiple) && is_bool($multiple) ? $multiple : false;
	$wrapperclass = isset($class) ? $class : 'col-md-3';
	//Format the media regardless of how it sent
	$bootstrap = isset($bootstrap) ? $bootstrap :  3;
	$selecteditems = array();
	if(isset($media) && !empty($media)) {
	foreach ($media as $m) {
		if(isset($m['Myfile'])) {
			$m['Myfile']['selected'] = true;
			$selecteditems[] = $m['Myfile'];
		}else {
			$m['selected'] = true;
			$selecteditems[] = $m;
		}
	}
	}
	$thumbnail = isset($this->request->data['MediaThumbnail'][0]) ? json_encode($this->request->data['MediaThumbnail'][0]) : false;
	$selecteditems = json_encode($selecteditems);
?>

<div id="MediaSelector">
	<?php /* seems like the thumbnails show up under the button, not sure so bring this back if it suits you
	<div class="row">
		<div class="col-md-4 well clearfix">
			<div id="mediaThumbnail">No Thumbnail Selected</div>	
		</div>
	</div> */ ?>
	
	<a data-toggle="modal" href="#mediaBrowserModal" class="btn btn-primary btn-lg">Select Media</a>
	<p>&nbsp;</p>
	<div id="mediaSelected" class="clearfix">
		
	</div>

</div>
<script type="template/javascript" id="mediaModalTemplate">
	<div class="modal fade" id="mediaBrowserModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">Browse Files</h4>
      </div>
      <div class="modal-body">
        <div id="mediaBrowser"></div>
      </div>
      <div class="modal-footer">
      </div>
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
	var baseUrl = '<?php echo $this->Html->url(array('plugin' => 'FileManager', 'controller' => 'file_manager', 'action' => 'file', '?'=>array('limit'=>50))); ?>';
	
</script>

<?php if($bootstrap == 2): ?>
	<script data-main="/FileManager/js/mediabrowser_boot2/build/media-min.js" src="/FileManager/js/mediabrowser/scripts/require.js"></script>
<?php else: ?>
<script data-main="/FileManager/js/mediabrowser/build/media-min.js" src="/FileManager/js/mediabrowser/scripts/require.js"></script>
<?php endif; ?>


<style>
	
	.modal-footer {
		border: none;
	}
	
	
</style>