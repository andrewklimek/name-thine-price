<?php
namespace name_thine_price;
/*
Plugin Name: Name Thine Price
Plugin URI:  https://github.com/andrewklimek/name-thine-price
Description: Name Your Price / Donate plugin for WooCommerce
Version:     0.1.0
Author:      Andrew J Klimek
Author URI:  https://readycat.net
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Name Thine Price is free software: you can redistribute it and/or modify 
it under the terms of the GNU General Public License as published by the Free 
Software Foundation, either version 2 of the License, or any later version.

Name Thine Price is distributed in the hope that it will be useful, but without 
any warranty; without even the implied warranty of merchantability or fitness for a 
particular purpose. See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with 
Name Thine Price. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

add_filter( 'woocommerce_add_cart_item', __NAMESPACE__ .'/add_cart_item' );
add_filter( 'woocommerce_get_cart_item_from_session', __NAMESPACE__ .'/get_cart_item_from_session' );
add_action( 'woocommerce_before_add_to_cart_button', __NAMESPACE__ .'/add_price_field' );

function add_price_field() {
	print "<input type='number' min='0' step='0.01' name='name-thine-price' class='name-thine-price'>";
}

function get_cart_item_from_session( $cart_item ) {
	
	if ( ! empty( $cart_item['name_thine_price'] ) ) {
		
	$cart_item['data']->set_price( $cart_item['name_thine_price'] );
	}
	return $cart_item;
}

function add_cart_item( $cart_item ) {
	
	if ( ! empty ( $_REQUEST['name-thine-price'] ) ) {
		
		$cart_item['name_thine_price'] = $_REQUEST['name-thine-price'];
		
		$cart_item['data']->set_price( $cart_item['name_thine_price'] );
	}
	return $cart_item;
}
