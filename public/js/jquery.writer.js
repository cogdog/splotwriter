/* SPLOT Writer Page Scripts
   code by Alan Levine @cogdog http://cogdog.info
   
   media uploader scripts somewhat lifted from
   http://mikejolley.com/2012/12/using-the-new-wordpress-3-5-media-uploader-in-plugins/
  
*/

	
jQuery(document).ready(function() { 

	// called for via click of upload button for adding media
	jQuery(document).on('click', '.upload_image_button', function(e){

		// disable default behavior for button
		e.preventDefault();

		// Create the media frame
		// use title and label passed from data-items in form button
	
		file_frame = wp.media.frames.file_frame = wp.media({
		  title: jQuery( this ).data( 'uploader_title' ),
		  button: {
			text: jQuery( this ).data( 'uploader_button_text' ),
		  },
		  multiple: false  // Set to true to allow multiple files to be selected
		});

		// fetch the type for this option so we can use it, comes from data-ctype value 
		// in form button
			
		// set up call back from image selection from media uploader
		file_frame.on( 'select', function() {
	
		  // attachment object from upload
		  attachment = file_frame.state().get('selection').first().toJSON();
    		  
		  // insert the base url into the hidden field for the option value
		  jQuery("#wHeaderImage").val(attachment.id);  
		  
		  jQuery("#wHeaderImageCaption").val(attachment.caption);  
		  
		  // update the thumbnail preview
		  jQuery("#headerthumb").attr("src", attachment.sizes.thumbnail.url);  
		  
		});

		// Finally, open the modal
		file_frame.open();
	
	});
	
	// enable tag autocomplete using suggest
	// writerObject is passed from WordPress localize script hook
	jQuery('#wTags').suggest( writerObject.siteURL + "/wp-admin/admin-ajax.php?action=ajax-tag-search&tax=post_tag", {multiple:true, multipleSep: ","});

	
});
