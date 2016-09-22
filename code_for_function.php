	
<?php
	//Load themes javascript
function theme_js(){
	wp_enqueue_script('mailchimp_js', 
		get_template_directory_uri(). '/mailchimp/mailchimp.js', array('jquery'),'',true);

	wp_localize_script( 'mailchimp_js', 'mailchimp_ajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
}
add_action( 'wp_enqueue_scripts', 'theme_js');

?>