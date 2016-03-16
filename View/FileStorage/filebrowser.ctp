<style>
.nav-tabs > li.active > a, .nav-tabs > li.active > a:hover, .nav-tabs > li.active > a:focus{
	color: #fff;
	background-color: #85b200;
	}
#mediaBrowser .media-item {
	height: 141px;
	}
</style>
<div class="container">
	<div class="row">
  		<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
  			<h1>The Filebrowser</h1>
  			<div id="mediaBrowser"><div class="row clearfix">
	<div class="col-md-3">
		<ul class="nav nav-pills nav-stacked">
			<li><a href="#" class="show-upload">Upload</a></li>
		</ul>
		<hr>
		<?php 
		echo $this->Form->create('FileBrowser', array('type'=>'get', 'url'=>array('plugin'=>'file_manager', 'controller'=>'file_storage', 'action'=>'browser'))); ?>
		<h5>Search by filename </h5>
		<?php echo $this->Form->input('keyword', array('placeholder'=>'type keyword', 'label'=>false)); ?>
		<h5>File Types to Show </h5>
		<?php echo $this->Form->select('type', $this->File->filterTypes, array('empty'=>false)); ?>
		<h5>Number to Show </h5>
		<?php echo $this->Form->select('limit', $this->File->filterLimits, array('empty'=>false)); ?><br />
		<?php echo $this->form->submit('APPLY FILTERS', array('class'=>'pull-right btn btn-success btn-sm'));?>
		<?php echo $this->form->hidden('viewType', array('name'=>'viewType','value'=>$viewType));?>
		<?php echo $this->form->end();?>
	</div>
	<div class="col-md-9">
	<div class="media-container">
		<?php 
		echo $this->Form->create('FileStorage', array('url'=>array('plugin'=>'file_manager', 'controller'=>'file_storage', 'action'=>'bulkactions'), 'id'=>'ListViewBulkActionForm')); ?>
		<!-- Nav tabs -->
		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation" <?php if($viewType=='thumb') { ?>class="active"<?php } ?>><a href="<?php echo $this->Html->url(array('controller'=>'file_storage','action'=>'browser','?'=>array('viewType'=>'thumb'))); ?>">Thumbnail view</a></li> <!-- aria-controls="thumbnailview" role="tab" data-toggle="tab" -->
			<li role="presentation" <?php if($viewType=='filelist') { ?>class="active"<?php } ?>><a href="<?php echo $this->Html->url(array('controller'=>'file_storage','action'=>'browser','?'=>array('viewType'=>'filelist'))); ?>">File list view</a></li>
			<li role="presentation">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</li>
			<li role="presentation"><input type="checkbox" name="data[FileStorage][checkAll]" id="checkAll"> <label for="checkAll">Select All</label></li>
			<li role="presentation"><?php echo $this->Form->select('FileStorage.bulkaction', array("Delete Selected"=>"Delete Selected"), array('empty'=>' - Choose Action - ', 'style'=>'display:inline;width:auto;')); ?><?php echo $this->Form->submit('GO',['div'=>['style'=>'display:inline']]); ?></li>
		</ul><!-- aria-controls="filelistview" role="tab" data-toggle="tab" #filelistview-->
	<br />
		<!-- Tab panes -->
  <div class="tab-content filesview">
		<?php if($viewType=='thumb')	{ ?>
    <div role="tabpanel" class="tab-pane active" id="thumbnailview">
			<?php echo $this->Element('FileManager.thumbnailview', array('media', $media)); ?>
		</div>
		<?php } elseif($viewType=='filelist')	{ ?>
    <div role="tabpanel" class="tab-pane active" id="filelistview">
			<?php echo $this->Element('FileManager.filelistview', array('media', $media)); ?>
		</div>
		<?php } ?>
  </div>
	<?php echo $this->form->end();?>
		<hr />
		<?php
		echo $this->Paginator->counter(array(
   'format' => __('Page {:page} of {:pages}, showing {:current} files out of {:count} total, starting on file {:start}, ending on {:end}')
   ));
   ?> </p>
   <div class="paging">
   <?php
    echo $this->Paginator->prev('< ' . __('previous'), array(), null, array('class' => 'prev disabled'));
    echo $this->Paginator->numbers(array('separator' => ''));
    echo $this->Paginator->next(__('next') . ' >', array(), null, array('class' => 'next disabled')); ?>
	</div>

	</div>
	<div class="upload-container" id="uploadPanel" style="display:none;">
		<?php echo $this->Element('FileManager.upload_form'); ?>
	</div>
	</div>
</div>
<hr>

<button type="button" class="show-upload pull-right btn btn-success btn-lg" data-dismiss="modal" aria-hidden="true">Insert</button>

</div>
		</div>
	</div>

<form id="deleteFileForm" style="display:none;" method="post">
	<input type="hidden" name="_method" value="POST">
	<input type="hidden" name="data[FileStorage][id]" id="fileStorageId" />
</form>
</div>

<link href="/Media/css/mediaBrowswer.css" type="text/css" />


	<script type="text/javascript">
		
		// Helper function to get parameters from the query string.
		function getUrlParam( paramName ) {
		    var reParam = new RegExp( '(?:[\?&]|&)' + paramName + '=([^&]+)', 'i' ) ;
		    var match = window.location.search.match(reParam) ;
	
		    return ( match && match.length > 1 ) ? match[ 1 ] : null ;
		}

		var funcNum = getUrlParam( 'CKEditorFuncNum' );

		function sendUrl( fileUrl ) {
			window.opener.CKEDITOR.tools.callFunction( funcNum, fileUrl );
		}


		$(document).ready(function() { 

			$('.show-upload').on('click', function()	{
				$('.media-container').toggle();
				$('.upload-container').toggle();
			})

			$("#checkAll").click(function () {
        if ($("#checkAll").is(':checked')) {
					$("#thumbnailview input[type=checkbox], #filelistview input[type=checkbox]").each(function () {
							$(this).prop("checked", true);
					});
				} else {
					$("#thumbnailview input[type=checkbox], #filelistview input[type=checkbox]").each(function () {
							$(this).prop("checked", false);
					});
        }
    });

		$("#ListViewBulkActionForm").on('submit', function () {
			if(!$('#FileStorageBulkaction').val())	{
				alert('Please choose an action to perform');$('#FileStorageBulkaction').focus();return false;
			}
			return confirm('Are you sure to do this action?');
		});



		    var options = { 
		        target:        '#browserList',   // target element(s) to be updated with server response 
		        beforeSubmit:  showRequest,  // pre-submit callback 
		        success:       showResponse,  // post-submit callback
		        error:		   showError
		 
		        // other available options: 
		        //url:       url         // override for form's 'action' attribute 
		        //type:      type        // 'get' or 'post', override for form's 'method' attribute 
		        //dataType:  null        // 'xml', 'script', or 'json' (expected server response type) 
		        //clearForm: true        // clear all form fields after successful submit 
		        //resetForm: true        // reset the form after successful submit 
		 
		        // $.ajax options can be used here too, for example: 
		        //timeout:   3000 
		    }; 
		 
		    // bind form using 'ajaxForm' 
		    //$('#FileBrowserForm').ajaxForm(options); 

			//Click Handler for ckeditor
			$("#browserList").on('click', '.select-media', function(e) {
				var url = $(this).parent().data('url');
				sendUrl(url);
				window.close();
			});

			$(".filesview").on('click', '.remove-media', function(e) {
				if (confirm('Are you sure you want to delete this file?')) {
					var id = $(this).data('id');
					var action = "<?php echo $this->Html->url(array('plugin' => 'file_manager', 'controller' => 'file_storage', 'action' => 'delete')); ?>";
					$('#deleteFileForm').attr('action', action);
					$('#deleteFileForm #fileStorageId').val(id);
					$('#deleteFileForm').submit();
				}
			});

			$('#FilterOptions').on('click', 'a', function(e) {
				var type = $(this).data('type');
				var url = "<?php echo $this->Html->url(array('plugin' => 'file_storage', 'controller' => 'file_storage', 'action' => 'browser')); ?>";
				$.post(
					url + "?type="+type,
					function(html) {
						$('#browserList').html(html);
						$(document).foundation();
					}
				).fail(function() {
				    alert( "error: Something went wrong");
				 });
			});

		    
		}); 
		 
		// pre-submit callback 
		function showRequest(formData, jqForm, options) { 
			showLoader();
		    return true; 
		} 
		 
		// post-submit callback 
		function showResponse(responseText, statusText, xhr, $form)  { 
		    // for normal html responses, the first argument to the success callback 
		    // is the XMLHttpRequest object's responseText property 
		 
		    // if the ajaxForm method was passed an Options Object with the dataType 
		    // property set to 'xml' then the first argument to the success callback 
		    // is the XMLHttpRequest object's responseXML property 
		 
		    // if the ajaxForm method was passed an Options Object with the dataType 
		    // property set to 'json' then the first argument to the success callback 
		    // is the json data object returned by the server
		    removeLoader();
		 	$form.resetForm();
		    $('a[href=#fileBrowser]').trigger("click");
		} 

		function showError(responseText, statusText, xhr, $form) {
			removeLoader();
			if(responseText.status == 415) {
				alert("Invalid file, please upload a supported file type");
			};
			
		}

		function showLoader() {
			var loader = $($('#loader-html').html());
			$('body').append(loader);
			loader.css('top', 200);
			loader.css('left', ($(window).width()/2)-100);
		}

		function removeLoader() {
			$('#uploaderSpinner').remove();
		}
		
	</script>
	
	<script id="loader-html" type="html/template">
		<div id="uploaderSpinner" style="padding: 50px; background: #fff; width: 200px; height: 200px; position:absolute;">
			<img alt="" src="data:image/gif;base64,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" />
		</div>
	</script>
