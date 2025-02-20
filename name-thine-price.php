<?php
namespace name_thine_price;
/*
Plugin Name: Name Thine Price
Plugin URI:  https://github.com/andrewklimek/name-thine-price
Description: Name Your Price / Donate plugin for WooCommerce
Version:     0.3.0
Author:      Andrew J Klimek
Author URI:  https://github.com/andrewklimek

TODO currently this plugin adds Name Your Price to every product.  Make a way to disable on a product-by-product basis
*/


add_filter( 'woocommerce_add_cart_item', __NAMESPACE__ .'\add_cart_item' );
add_filter( 'woocommerce_get_cart_item_from_session', __NAMESPACE__ .'\get_cart_item_from_session' );
add_action( 'woocommerce_before_add_to_cart_button', __NAMESPACE__ .'\add_price_field' );

// maybe use woocommerce_get_price_suffix instead https://woocommerce.github.io/code-reference/files/woocommerce-includes-abstracts-abstract-wc-product.html#source-view.2046
add_filter( 'woocommerce_get_price_html',  __NAMESPACE__ .'\price_html', 10, 2 );

function price_html( $html, $product ) {
	return $html ? $html . "+" : "";
}

add_filter( 'woocommerce_is_purchasable',  __NAMESPACE__ .'\is_purchasable', 10, 2 );
function is_purchasable( $purchasable, $product ) {
	return true;
}


function add_price_field() {
	
	global $product;
	$id = $product->get_id();// product ID to make field ID unique
	$price = $product->get_price();
	$min = get_option( 'namethineprice_minimum' );
	$min = is_numeric( $min ) ? $min : $price;// regular price to act as minimum in HTML5 validation
	
	echo "<label for='name_thine_price-{$id}'>Name your price: </label> <input id='name_thine_price-{$id}' name='name_thine_price' type='number' min='{$min}' step='0.5' value='{$price}' required>";
}

function get_cart_item_from_session( $cart_item ) {
	
	if ( ! empty( $cart_item['name_thine_price'] ) ) {
		$cart_item['data']->set_price( $cart_item['name_thine_price'] );
	}
	return $cart_item;
}

function add_cart_item( $cart_item ) {

	// $cart_item['data'] is a product object.
	
	if ( ! empty ( $_REQUEST['name_thine_price'] ) ) {

		$min = get_option( 'namethineprice_minimum' );
		$min = is_numeric( $min ) ? $min : $cart_item['data']->get_price();
		
		if ( $_REQUEST['name_thine_price'] < $min ) {
			// TODO this could be checked on the actual validation hook but it seemed tricky dealing with variations and ajax
			throw new \Exception( "Please set a price of {$min} or more." );
			
		} else {
		
			$cart_item['name_thine_price'] = $_REQUEST['name_thine_price'];
		
			$cart_item['data']->set_price( $cart_item['name_thine_price'] );
		}
	}
	return $cart_item;
}

// add_filter( 'woocommerce_add_to_cart_validation', __NAMESPACE__ .'\validate', 10, 4 );
function validate( $pass, $product_id, $quantity, $variation_id ) {
	$product_data = wc_get_product( (int) $variation_id );
	poo($product_data);
	return $pass;
}


function make_popup( $a='', $c='', $shortcode=false ) {

	if ( $shortcode ) ob_start();

	// print the wrapped button but remove class that serves as ajax trigger
	echo str_replace( 'ajax_add_to_cart', 'open-ntp-popup', $c );
?>
<script>
jQuery(document.body).on('should_send_ajax_request.adding_to_cart', function(e, button ) {
	console.log(button);
	var nypForm = button.closest('.nyp-product').wc_nyp_get_script_object();
	if ( nypForm && ! nypForm.isValid() ) {
		return false
	}
	return true;
});
</script>
<?php
	if ( $shortcode ) return ob_get_clean();
}


/************************
* Settings Page
**/

add_action( 'rest_api_init', __NAMESPACE__.'\register_options_endpoint' );
function register_options_endpoint() {
	register_rest_route( __NAMESPACE__.'/v1', '/settings', ['methods' => 'POST', 'callback' => __NAMESPACE__.'\api_options', 'permission_callback' => function(){ return current_user_can('manage_options');} ] );
}

function api_options( $request ) {
	foreach ( $request->get_body_params() as $k => $v ) update_option( $k, $v );
	return "Saved";
}

add_action( 'admin_menu', __NAMESPACE__.'\admin_menu' );
function admin_menu() {
	add_submenu_page( 'options-general.php', 'Name Thine Price', 'Name Thine Price', 'manage_options', 'name-thine-price', __NAMESPACE__.'\settings_page' );
}

function settings_page() {

	$options = [
		'minimum' => [
			'type' => 'number',
			'desc' => 'leave blank to use regular price as minimum',
		],
	];

	/**
	 *  Build Settings Page using framework in settings_page.php
	 **/
	$prefix = 'namethineprice_';
	$endpoint = rest_url(__NAMESPACE__.'/v1/settings');
	$title = "Name Thine Price";
	require( __DIR__.'/settings-page.php' );// needs $options, $endpoint, $title
}