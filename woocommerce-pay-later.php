<?php
/*
* Plugin Name: WooCommerce Pay Later Payment Gateway
* Description: A payment gateway that allows customers to pay later.
* Version: 1.0
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
function add_pay_later_gateway( $methods ) {
    
    include_once 'includes/gateways/pay-later/class-wc-gateway-pay-later.php';
    
	$methods[] = 'WC_Gateway_Pay_Later'; 
	
	return $methods;
	
}

add_filter( 'woocommerce_payment_gateways', 'add_pay_later_gateway' );

?>