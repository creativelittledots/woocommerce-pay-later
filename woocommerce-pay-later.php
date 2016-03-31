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

define('WC_PAY_LATER_TEMPLATE_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/templates/');

add_filter( 'woocommerce_resend_order_emails_available', 'add_resend_pay_later_order_woocommerce_email' );

function add_resend_pay_later_order_woocommerce_email($available_emails) {
	
	$available_emails[] = 'customer_pay-later_order';
	
	return $available_emails;
	
}

add_filter( 'woocommerce_email_classes', 'add_pay_later_order_woocommerce_email' );

function add_pay_later_order_woocommerce_email( $email_classes ) {

	// add the email class to the list of email classes that WooCommerce loads
	$email_classes['WC_Email_Customer_Pay_Later_Order'] = include( 'includes/emails/class-wc-email-customer-pay-later.php' );

	return $email_classes;

}

add_filter( 'woocommerce_email_actions', 'add_pay_later_order_woocommerce_email_action' );

function add_pay_later_order_woocommerce_email_action($actions) {
	
	$actions[] = 'woocommerce_order_status_failed_to_pay-later';
	$actions[] = 'woocommerce_order_status_pending_to_pay-later';
	$actions[] = 'woocommerce_order_status_pay-later_to_processing';
	
	return $actions;
	
}

add_action( 'init', 'register_pay_later_order_woocommerce_status' );

function register_pay_later_order_woocommerce_status() {
    register_post_status( 'wc-pay-later', array(
        'label'                     => _x( 'Pay Later', 'Order status', 'woocommerce' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Pay Later <span class="count">(%s)</span>', 'Pay Later<span class="count">(%s)</span>', 'woocommerce' )
    ) );
}

add_filter( 'wc_order_statuses', 'add_pay_later_order_woocommerce_status' );

function add_pay_later_order_woocommerce_status($order_statuses) {
	
	$order_statuses['wc-pay-later'] = _x( 'Pay Later', 'Order status', 'woocommerce' );

    return $order_statuses;
    
}

add_filter( 'woocommerce_payment_gateways', 'add_pay_later_gateway' );

function add_pay_later_gateway( $methods ) {
	
	// include our custom gateway class
	require_once( 'includes/gateways/pay-later/class-wc-gateway-pay-later.php' );
	
	$methods[] = 'WC_Gateway_Pay_Later'; 
	
	return $methods;
	
}

add_filter( 'woocommerce_email_format_string_find', 'add_order_status_format_string_find' );

function add_order_status_format_string_find($find) {
	
	$find['order-status'] = '{order_status}';
	
	return $find;
	
}

add_filter( 'woocommerce_email_format_string_replace', 'add_order_status_format_string_replace', 10, 2 );

function add_order_status_format_string_replace($replace, $email) {
	
	$replace['order-status'] = wc_get_order_status_name( $email->object->get_status() );
	
	return $replace;
	
}

add_action( 'woocommerce_email', 'trigger_new_order_email_on_pay_later_status' );

function trigger_new_order_email_on_pay_later_status($emails) {
	
	// Trigger on new paid orders
	add_action( 'woocommerce_order_status_pending_to_pay-later_notification', array( $emails->emails['WC_Email_New_Order'], 'trigger' ), 90 );
	add_action( 'woocommerce_order_status_failed_to_pay-later_notification',  array( $emails->emails['WC_Email_New_Order'], 'trigger' ), 90 );
	add_action( 'woocommerce_order_status_pay-later_to_processing_notification', array( $emails->emails['WC_Email_Customer_Processing_Order'], 'trigger' ), 90 );
	
}

add_action( 'admin_enqueue_scripts', 'enqueue_pay_later_admin_css' );

function enqueue_pay_later_admin_css() {
	
	wp_enqueue_style( 'wc-pay-later-admin-css', untrailingslashit( plugins_url( '/', __FILE__ ) ) . '/assets/css/admin.css' );
	
}

add_action( 'woocommerce_admin_order_actions', 'add_pay_later_order_actions', 2, 2);

function add_pay_later_order_actions($actions, $order) {
	
	if ( $order->has_status( array( 'pay-later' ) ) ) {
		
		$actions['processing'] = array(
			'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=processing&order_id=' . $order->id ), 'woocommerce-mark-order-status' ),
			'name'      => __( 'Processing', 'woocommerce' ),
			'action'    => "processing"
		);

	
		$actions['complete'] = array(
			'url'       => wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=completed&order_id=' . $order->id ), 'woocommerce-mark-order-status' ),
			'name'      => __( 'Complete', 'woocommerce' ),
			'action'    => "complete"
		);
		
	}
	
	return $actions;
	
}

?>