<?php
/*
This code is used to add subscribers to a Mailchimp list using the Mailchimp API v.3 and PHP. The implementation was used for a WordPress site with a WooCommerce webshop, but the functions can be used for other sites as well.
*/

$global_list_id = 'YOUR_LIST_ID';
$global_api_key = 'YOUR_API_KEY';

/* This function adds the user to the list using the MailChimp API*/
/**
 * nb_mailchimp_subscribe_to_list.
 *
 * @access public
 * @param string email, string status, string list_id, string api_key, array merge_fields
 * @return string
 */
function nb_mailchimp_subscribe_to_list( $email, $status, $list_id, $api_key, $merge_fields = array('FNAME' => '','LNAME' => '') ){
	$data = array(
		'apikey'        => $api_key,
    	'email_address' => $email,
		'status'        => $status,
		'merge_fields'  => $merge_fields
	);
	$mch_api = curl_init(); // initialize cURL connection
	curl_setopt($mch_api, CURLOPT_URL, 'https://' . substr($api_key,strpos($api_key,'-')+1) . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . md5(strtolower($data['email_address'])));
	curl_setopt($mch_api, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic '.base64_encode( 'user:'.$api_key )));
	curl_setopt($mch_api, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
	curl_setopt($mch_api, CURLOPT_RETURNTRANSFER, true); // return the API response
	curl_setopt($mch_api, CURLOPT_CUSTOMREQUEST, 'PUT'); // method PUT
	curl_setopt($mch_api, CURLOPT_TIMEOUT, 10);
	curl_setopt($mch_api, CURLOPT_POST, true);
	curl_setopt($mch_api, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($mch_api, CURLOPT_POSTFIELDS, json_encode($data) ); // send data in json
 
	$result = curl_exec($mch_api);
	return $result;
}


/* This function check if the user is already subscribing to the list using the MailChimp API*/
/**
 * mailchimp_check_status.
 *
 * @access public
 * @param string email, string status, string list_id, string api_key
 * @return int
 */

function nb_mailchimp_check_status( $email, $status, $list_id, $api_key){
	$data = array(
		'apikey'        => $api_key,
    	'email_address' => $email,
		'status'        => $status
	);
	$mch_api = curl_init(); // initialize cURL connection
	curl_setopt($mch_api, CURLOPT_URL, 'https://' . substr($api_key,strpos($api_key,'-')+1) . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . md5(strtolower($data['email_address'])));
	curl_setopt($mch_api, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Basic '.base64_encode( 'user:'.$api_key )));
	curl_setopt($mch_api, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');
	curl_setopt($mch_api, CURLOPT_RETURNTRANSFER, true); // return the API response
	curl_setopt($mch_api, CURLOPT_CUSTOMREQUEST, 'GET'); // method GET
	curl_setopt($mch_api, CURLOPT_TIMEOUT, 10);
	curl_setopt($mch_api, CURLOPT_POST, true);
	curl_setopt($mch_api, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($mch_api, CURLOPT_POSTFIELDS, json_encode($data) ); // send data in json
 	$response = curl_exec($mch_api);
	$result = curl_getinfo($mch_api, CURLINFO_HTTP_CODE);
	return $result;
}


/* This action is used to add a subscriber by only providing the email adress */
/**
 * mailchimp_check_status.
 *
 * @access public
 * @return void
 */

function nb_mailchimp_subscribe_simple(){
	$result = nb_mailchimp_check_status($_POST['email'], 'subscribed', $list_id, $api_key);
	if($result == 404){
		$result = nb_mailchimp_subscribe_to_list($_POST['email'], 'subscribed', $list_id, $api_key );
	} else {
		echo "already exists";
	}
	die();
}
 
add_action('wp_ajax_mailchimpsubscribe_simple','nb_mailchimp_subscribe_simple');
add_action('wp_ajax_nopriv_mailchimpsubscribe_simple','nb_mailchimp_subscribe_simple');



/* This action is used to add a subscriber by only providing email, first name and last name */
/**
 * mailchimp_check_status.
 *
 * @access public
 * @return void
 */

function nb_mailchimp_subscribe(){
	$list_id = $global_list_id;
	$api_key = $global_api_key;
	$result = nb_mailchimp_check_status($_POST['email'], 'subscribed', $list_id, $api_key);
	if( $result == 404){
		$result = nb_mailchimp_subscribe_to_list($_POST['email'], 'subscribed', $list_id, $api_key, array('FNAME' => $_POST['fname'],'LNAME' => $_POST['lname']) );
	} else {
		echo json_encode($result);
	}
	die();
}
 
add_action('wp_ajax_mailchimpsubscribe','nb_mailchimp_subscribe');
add_action('wp_ajax_nopriv_mailchimpsubscribe_simple','nb_mailchimp_subscribe');

/* This function is used to add a "sign up to newletter checkbox on the woocommerce check out page" */
/**
 * nb_order_status_changed function.
 *
 * @access public
 * @return void
 */
function nb_order_status_changed( $id, $status = 'new', $new_status = 'pending' ) {
	$list_id = $global_list_id;
	$api_key = $global_api_key;
	// Get WC order
	$order = nb_wc_get_order( $id );

	// get the wc_mailchimp_opt_in value from the post meta. "order_custom_fields" was removed with WooCommerce 2.1
	$subscribe_customer = get_post_meta( $id, 'wc_mailchimp_opt_in', true );

	//If the 'wc_mailchimp_opt_in' is yes, subscriber the customer
	if ( 'yes' == $subscribe_customer ) {
		// subscribe
		nb_mailchimp_subscribe_to_list($order->billing_email, 'subscribed', $list_id, $api_key, array('FNAME' => $order->billing_first_name,'LNAME' => $order->billing_last_name) );
	}
}


add_action( 'woocommerce_checkout_update_order_meta', 'nb_order_status_changed' , 1000, 1 );
/**
 * WooCommerce 2.2 support for wc_get_order
 *
 * @since 1.2.1
 *
 * @access private
 * @param int $order_id
 * @return void
 */
function nb_wc_get_order( $order_id ) {
	if ( function_exists( 'wc_get_order' ) ) {
		return wc_get_order( $order_id );
	} else {
		return new WC_Order( $order_id );
	}
}

/**
 * Add checkbox to the checkout fields (to be displayed on checkout).
 */
function nb_add_checkout_fields( $checkout_fields ) {	
	$checkout_fields['billing']['wc_mailchimp_opt_in'] = array(
		'type'    => 'checkbox',
		'label'   => 'Sign up for our newsletter',
		'default' => 1,
	);
	return $checkout_fields;
}

add_filter( 'woocommerce_checkout_fields', 'nb_add_checkout_fields' );
/**
 * When the checkout form is submitted, save opt-in value.
 *
 * @version 1.1
 */
function nb_save_checkout_fields( $order_id ) {
		$opt_in = isset( $_POST['wc_mailchimp_opt_in'] ) ? 'yes' : 'no';
		update_post_meta( $order_id, 'wc_mailchimp_opt_in', $opt_in );
}

add_action( 'woocommerce_checkout_update_order_meta',  'nb_save_checkout_fields'  );





	