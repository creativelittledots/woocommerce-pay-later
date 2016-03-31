<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * A custom Pending Order WooCommerce Email class
 *
 * @since 0.1
 * @extends \WC_Email
 */
class WC_Email_Customer_Pay_Later_Order extends WC_Email {

	/**
	 * Set email defaults
	 *
	 * @since 0.1
	 */
	public function __construct() {

		// set ID, this simply needs to be a unique name
		$this->id = 'customer_pay-later_order';
		$this->customer_email = true;

		// this is the title in WooCommerce Email settings
		$this->title = 'Pay Later Order';

		// this is the description in WooCommerce email settings
		$this->description = 'Pay Later Order Notification emails are sent when a customer places an order via the Pay Later gateway';

		// these are the default heading and subject lines that can be overridden using the settings
		$this->heading = 'Your order is pending payment';
		$this->subject = 'Your {site_title} order from {order_date} is pending payment';
		
		$this->template_base	= WC_PAY_LATER_TEMPLATE_PATH;
		$this->template_html 	= 'emails/customer-pay-later-order.php';
		$this->template_plain 	= 'emails/plain/customer-pay-later-order.php';

		// Trigger on new paid orders
		add_action( 'woocommerce_order_status_pending_to_pay-later_notification', array( $this, 'trigger' ) );
		add_action( 'woocommerce_order_status_failed_to_pay-later_notification',  array( $this, 'trigger' ) );

		// Call parent constructor to load any other defaults not explicity defined here
		parent::__construct();
		
	}

	/**
	 * Determine if the email should actually be sent and setup email merge variables
	 *
	 * @since 0.1
	 * @param int $order_id
	 */
	public function trigger( $order_id ) {
		
		if ( $order_id ) {
			$this->object 		= wc_get_order( $order_id );
			$this->recipient    = $this->object->billing_email;

			$this->find['order-date']      = '{order_date}';
			$this->find['order-number']    = '{order_number}';

			$this->replace['order-date']   = date_i18n( wc_date_format(), strtotime( $this->object->order_date ) );
			$this->replace['order-number'] = $this->object->get_order_number();
		}

		if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
			return;
		}
		
		$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		
	}


	/**
	 * get_content_html function.
	 *
	 * @since 0.1
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html( $this->template_html, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => false
		), '', $this->template_base );
	}


	/**
	 * get_content_plain function.
	 *
	 * @since 0.1
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html( $this->template_plain, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => true
		), '', $this->template_base );
	}

} // end \WC_Email_Customer_Pay_Later_Order class

return new WC_Email_Customer_Pay_Later_Order();