//Requires jquery.form.js
//http://jquery.malsup.com/form/
(function($) {
		
		var links = $('#MediaFileBrowser .nav a');
		var windows = $('#MediaFileBrowser .content-panels .panel');
		var mediaItems = $('#fileBrowser li.media-item');
		var loader = $('#MediaFileBrowser .loader');
		
		$(document).ready(function() {
			windows.hide();
			var id;
			links.each(function(i, link) {
				if($(link).hasClass('active')) {
					id = $(link).attr('href');
					return false;
				}
			});
			loader.hide();
			showWindow(id);
		});
		
		$('#FileAddForm').ajaxForm({
			    beforeSend: function() {
			        loader.show();
			    },
			    uploadProgress: function(event, position, total, percentComplete) {
			        var percentVal = percentComplete + '%';
			        console.log(percentVal);
			    },
			    success: function(html) {
			    	console.log(html);
			        links.removeClass('active');
			        $('#fileBrowser').addClass('active');
			        if(mediaItems.length > 0) {
			        	mediaItems.last().after(html);
			        }else {
			        	$('#fileBrowser ul').html(html);
			        }
			        mediaItems = $('#fileBrowser li.media-item');
			        showWindow('#fileBrowser');
			        $('#FileAddForm').clearForm();
			    },
				complete: function(xhr) {
					loader.hide();
				}
		}); 
				     
		
		links.click(function(e) {
			e.preventDefault();
			var id = $(this).attr('href');
			links.removeClass('active');
			$(this).addClass('active');
			showWindow(id);
			
		});
		
		$('#fileBrowser').on('click', 'a.thumbnail', function(e){
			if($(this).hasClass('selected')) {
				$(this).removeClass('selected');
			}else{
				if(!multiple) {
					$('#fileBrowser a.thumbnail').removeClass('selected');
				}
				$(this).addClass('selected');
			}
		})
		
		function showWindow (id) {
			windows.hide('fast');
			$(id).show('fast');
		}
		
})(jQuery)