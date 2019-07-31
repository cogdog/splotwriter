/* Javascript code for theme options editing
   code by Alan Levine @cogdog http://cogdog.info
   
   media uploader scripts somewhat lifted some from
   http://mikejolley.com/2012/12/using-the-new-wordpress-3-5-media-uploader-in-plugins/
  
*/

jQuery(document).ready(function() { 

	//hide the password field
	jQuery("#pkey").hide();

	//toggle password field to show/hide contents
	 jQuery("#showHide").click(function() {
		if (jQuery("#showHide").val() == "Show") {
		  jQuery("#pkey").show();
		  jQuery("#showHide").attr('value', 'Hide');

		} else {
		  jQuery("#pkey").hide();
		  jQuery("#showHide").attr('value', 'Show');
		}
	  });

	// called for via click of upload button in theme options	
	jQuery(document).on('click', '.upload_image_button', function(e){

		// disable defauklt behavior
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

		// fetch the id for this option so we can use it, comes from data-options_id value 
		// in form button
	
		options_id = jQuery( this ).data( 'options_id' );

		// set up call back from image selection from media uploader
		file_frame.on( 'select', function() {
	
		  // attachment object from upload
		  attachment = file_frame.state().get('selection').first().toJSON();
  
		  // insert the thumbnail url into the hidden field for the option value
		  jQuery("#"+options_id).val(attachment.id);  	
  
		  // update the src of the preview image so you can see it
		  jQuery('img#previewimage_'+options_id).attr( 'src', attachment.sizes.medium.url ); 
  
		});

		// Finally, open the modal
		file_frame.open();
	
	});
});