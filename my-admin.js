jQuery(document).ready(function($){
 
 
    var custom_uploader;
 
 
    $('#upload_image_button').click(function(e) {
 
        e.preventDefault();
 
        //If the uploader object has already been created, reopen the dialog
        if (custom_uploader) {
            custom_uploader.open();
            return;
        }
 
        //Extend the wp.media object
        custom_uploader = wp.media.frames.file_frame = wp.media({
            title: 'Choose Image',
            button: {
                text: 'Choose Image'
            },
			
            multiple: true
        });
 


custom_uploader.on('select', function() {
            var selection = custom_uploader.state().get('selection');
            selection.map( function( attachment ) {
            attachment = attachment.toJSON();
             $("#something").after("<img src=" +attachment.url+">");
            });
        });
 
 
				
        //Open the uploader dialog
        custom_uploader.open();
 
    });
 
 
});
