<?php
namespace name_thine_price;
/*
Plugin Name: Name Thine Price
Plugin URI:  https://github.com/andrewklimek/name-thine-price
Description: Name Your Price / Donate plugin for WooCommerce
Version:     0.2.0
Author:      Andrew J Klimek
Author URI:  https://github.com/andrewklimek
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

TODO currently this plugin adds Name Your Price to every product.  Make a way to disable on a product-by-product basis
*/


// This removes quantity fields and makes so you can't add to cart twice.  Temp solution.
add_filter( 'woocommerce_is_sold_individually', '__return_true' );
// also temporary.  Remove cart buttons from loops
add_action( 'wp_head',  __NAMESPACE__ .'\remove_actions' );
function remove_actions() {
	remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
}

add_filter( 'woocommerce_add_cart_item', __NAMESPACE__ .'\add_cart_item' );
add_filter( 'woocommerce_get_cart_item_from_session', __NAMESPACE__ .'\get_cart_item_from_session' );
add_action( 'woocommerce_before_add_to_cart_button', __NAMESPACE__ .'\add_price_field' );

add_filter( 'woocommerce_price_html',  __NAMESPACE__ .'\or_more', 10, 2 );
add_filter( 'woocommerce_free_price_html',  __NAMESPACE__ .'\free', 10, 2 );
add_filter( 'woocommerce_empty_price_html',  __NAMESPACE__ .'\free', 10, 2 );

function or_more( $html, $this ) { return $html . "+"; }
function free( $html, $this ) { return ""; }

add_filter( 'woocommerce_is_purchasable',  __NAMESPACE__ .'\is_purchasable', 10, 2 );
function is_purchasable( $purchasable, $product ) {
	return true;
}


function add_price_field() {
	
	global $product;
	$id = esc_attr( $product->id );// product ID to make field ID unique
	$min = esc_attr( $product->get_display_price() );// regular price to act as minimum in HTML5 validation
	
	print "<label for='name-thine-price-{$id}'>Name your price: </label> <input id='name-thine-price-{$id}' name='name-thine-price' type='number' min='{$min}' step='0.01' required>";
}

function get_cart_item_from_session( $cart_item ) {
	
	if ( ! empty( $cart_item['name_thine_price'] ) ) {
		
	$cart_item['data']->set_price( $cart_item['name_thine_price'] );
	}
	return $cart_item;
}

function add_cart_item( $cart_item ) {
	
	if ( ! empty ( $_REQUEST['name-thine-price'] ) ) {
		
		if ( $_REQUEST['name-thine-price'] < $cart_item['data']->price ) {
			// TODO this could be checked on the actual validation hook but it seemed tricky dealing with variations and ajax
			throw new \Exception( "Please set a price of {$cart_item['data']->price} or more." );
			
		} else {
		
			$cart_item['name_thine_price'] = $_REQUEST['name-thine-price'];
		
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