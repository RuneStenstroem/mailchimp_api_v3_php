/* 
Example of you can use and html form and ajax to trigger the form and show the appropriate status message. In you need to change
the classes and so on to make it fit your own html form and status boxes. In this case I use Bootstrap 3 modals to show the messages
*/

jQuery(function($){
	$('#footer-submit').click(function(){
		//Check if the email is valid
		if( !isEmail(jQuery(".footer-email").val())) {
			    jQuery('.signup-title').text('Problem with the signing up');
                jQuery('.signup-status-message').text('This is not a valid email');
                jQuery('#newletter-signup-modal').modal('show');
			} else {
				var mailchimpform = $("#mailchimpform_footer");
				$.ajax({
					url:mailchimp_ajax.ajaxurl,
					type:'POST',
					data:mailchimpform.serialize(),
					success:function(data){
					var mail = jQuery(".footer-email").val();
					jQuery(".footer-email").val("");
					jQuery('.signup-title').text("You are now signed up to our news letter");
					if(data == "already exists"){
						jQuery('.signup-status-message').text("The mail address " + mail + " is already signed up to our newletter" );
					} else {
              			jQuery('.signup-status-message').text('You will now recieve our newletter on ' + mail);
              		}
              		jQuery(".footer-email").val("");
              		jQuery('#newletter-signup-modal').modal('show');
				}
			})
			return false;	
		}
	
	});
});
               
function isEmail(email) {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
}