<div class="container">
	<div class="row">
  		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
  			<h1>The Filebrowser</h1>
  			<div id="mediaBrowser"></div>
		</div>
	</div>
</div>
<link href="/FileManager/css/mediaBrowser.css" type="text/css" />
<script type="text/javascript">
	var baseUrl = '<?php echo $this->Html->url(array('plugin' => 'file_manager', 'action' => 'file', '?'=>array('limit'=>50))); ?>';
	var fileManagerLimit = 20;
</script>
<script data-main="/FileManager/js/mediabrowser/build/media-min.js" src="/FileManager/js/mediabrowser/scripts/require.js"></script>
