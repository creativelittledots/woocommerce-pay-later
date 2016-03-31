<?php
/**
 * Customer pending order email
 *
 * @author 		Creative Little Dots
 * @package 	WooCommerce-Pay-Later/Templates/Emails
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<p><?php printf( __( "Your recent booking on %s is pending payment", 'woocommerce' ), get_option( 'blogname' ) ); ?></p>

<p><?php echo __('You have chosen to pay later. To pay for your order now, please click the button below.', 'woocommerce-pay-later'); ?></p>

<a href="<?php echo $order->get_checkout_payment_url(); ?>" target="_blank"><?php echo __('Pay Now', 'woocommmerce'); ?></a>

<?php

/**
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @since 2.5.0
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/**
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
