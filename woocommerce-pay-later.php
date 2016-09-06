<?php
/*
* Plugin Name: WooCommerce Pay Later Payment Gateway
* Description: A Payment Gateway for WooCommerce that allows Customers to Pay Later at Checkout.
* Version: 1.0.1
* Author: Creative Little Dots
* Author URI: http://creativelittledots.co.uk
* Text Domain: woocommerce-pay-later
* Domain Path: /languages/
*
* Requires at least: 3.8
* Tested up to: 4.1.1
*
* Copyright: © 2009-2015 Creative Little Dots
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Add the gateway to WooCommerce
 **/
function wcpl_add_gateway( $methods ) {
    
    include_once 'includes/gateways/pay-later/class-wc-gateway-pay-later.php';
    
	$methods[] = 'WC_Gateway_Pay_Later'; 
	
	return $methods;
	
}

add_filter( 'woocommerce_payment_gateways', 'wcpl_add_gateway' );

?>