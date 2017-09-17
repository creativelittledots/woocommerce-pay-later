<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
	
/**
 * Credit Card Payment Gateway
 *
 * @since       1.0.0
 * @package		WooCommerce Pay Later/Classes
 * @author 		Creative Little Dots
 */
class WC_Gateway_Pay_Later extends WC_Payment_Gateway {
	
	/**
	 * Constructor for the gateway.
	 */
	public function __construct() { 
		
		global $woocommerce;
		
		$this->id			= 'pay_later';
		$this->has_fields 	= false;
		$this->method_title = __('Pay Later', 'woocommerce-pay-later');
		
		// Load the form fields.
		$this->init_form_fields();
		
		// Load the settings.
		$this->init_settings();
		
		// Define user set variables
		$this->title 				= $this->settings['title'];
		$this->description 			= $this->settings['description'];
		
		// Actions
		add_filter( 'woocommerce_default_order_status', array($this, 'default_order_status') );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_filter( 'woocommerce_email_format_string_find', array($this, 'order_status_format_string_find') );
		add_filter( 'woocommerce_email_format_string_replace', array($this, 'order_status_format_string_replace'), 10, 2 );
		add_action( 'woocommerce_order_status_pending', array($this, 'send_pending_order_emails') );
		add_filter( 'woocommerce_valid_order_statuses_for_payment', array($this, 'valid_order_statuses_for_payment' ), 10, 2 );
		add_action( 'wp', array($this, 'change_order_to_pending_on_order_received'), 8 );

	}
	
	public function valid_order_statuses_for_payment($statuses, $order) {
		
		if( $order->is_pay_later ) {
			
			$statuses[] = 'on-hold';
			
		}
		
		return $statuses;
		
	}
	
	/**
	 * Change the default order status to on-hold so that pending order emails can be triggered
	 */
	public function default_order_status($default) {
		/* if( ! is_admin() && WC()->session->set( 'chosen_payment_method') == $this->id ) {*/
		if( ! is_admin() && WC()->session->get( 'chosen_payment_method') == $this->id ) {
			
			$default = 'on-hold';
			
		}
		
		return $default;
		
	}
	
	/**
	 * Allow Order status to be accessible from emails
	 */
	public function order_status_format_string_find( $find ) {
	
		$find['order-status'] = '{order_status}';
		
		return $find;
		
	}
	
	/**
	 * Replace Order status in emails
	 */
	public function order_status_format_string_replace( $replace, $email ) {
    	
    	if( $email->object ) {
	
		    $replace['order-status'] = wc_get_order_status_name( $email->object->get_status() );
		    
        }
		
		return $replace;
		
	}
	
	/**
	 * Trigger pending order emails and invoice email
	 */
	public function send_pending_order_emails( $order_id ) {
	
		$emails = new WC_Emails();
		
		$order = wc_get_order( $order_id );
			
		$emails->customer_invoice( $order_id );
		
		$emails->emails['WC_Email_New_Order']->trigger( $order_id );
		
		$order->set_payment_method( $this );
		
	}
	
	/**
	 * WC Shop As Customer support on Order Received, because the default status is on hold we need to change these orders to pending
	 */
	public function change_order_to_pending_on_order_received() {
		
		if( class_exists('WC_Shop_As_Customer') && ! empty( $_GET['order_on_behalf'] ) && ! empty( $_GET['key'] ) && ! empty( $_GET['send_invoice'] ) ) {
		
			global $wp;
			
			if ( ! isset( $wp->query_vars['order-received'] ) )
				return;
				
			// Bail if we're not shopping-as - don't display the special interface.
			if ( ! WC_Shop_As_Customer::get_original_user() )
				return;
				
			$order_id = $wp->query_vars['order-received'];

			if ( ! empty( $order_id ) ) {
	
				$order = new WC_Order( absint( $order_id) );
				
				$order->update_status( 'pending' );
				
			}
			
			unset( $_GET['send_invoice'] );
			
		}
			
	}
	
	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {
	
		$this->form_fields = array(
			'enabled' => array(
							'title' => __( '<b>Enable/Disable:</b>', 'woocommerce-pay-later' ), 
							'type' => 'checkbox', 
							'label' => __( 'Enable Pay Later Payment Gateway.', 'woocommerce-pay-later' ), 
							'default' => 'yes'
						), 
			'title' => array(
							'title' => __( '<b>Title:</b>', 'woocommerce-pay-later' ), 
							'type' => 'text', 
							'description' => __( 'The title which the user sees during checkout.', 'woocommerce-pay-later' ), 
							'default' => __( 'Pay Later', 'woocommerce-pay-later' )
						),
			'description' => array(
							'title' => __( '<b>Description:</b>', 'woocommerce-pay-later' ), 
							'type' => 'textarea', 
							'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-pay-later' ), 
							'default' => __('Choose to pay later, we\'ll send you an invoice.', 'woocommerce-pay-later')
						)
			);
	
	}
	
	/**
	 * Admin Panel Options 
	 */
	public function admin_options() {

		?>
		
		<h3>Pay Later Payment Gateway</h3>
		
		<p>Allow your customers to choose to pay later</p>
		
		<table class="form-table">
			
		<?php
			
		// Generate the HTML For the settings form.
		$this->generate_settings_html();

	}
	
	/**
	 * Process the payment, set the Order to pending and return the result
	 **/
	public function process_payment( $order_id ) {
		
		$order = wc_get_order( $order_id );
		
		$order->update_status( 'pending' );
		
		update_post_meta( $order_id, '_is_pay_later', true );
		
		// Reduce stock levels
		$order->reduce_order_stock();
		
		// Remove cart
		WC()->cart->empty_cart();
		
		return array(
			'result' 	=> 'success',
			'redirect'	=> apply_filters( 'wc_pay_later_order_received_url', $order->get_checkout_order_received_url(), $order, $this )
		);
		
	}
	
	/**
	 * There are some scenerios where Pay Later should not be a checkout option, such as when on the Order Pay endpoint and when us WC Shop As Customer at checkout 
	 */
	public function is_available() {
		
		// if we are available lets run some checks
		
        if( $is_available = parent::is_available() ) { 
	        
	        if( class_exists('WC_Shop_As_Customer') && WC_Shop_As_Customer::get_original_user() ) {
		        
		        // WC Shop As Customer support on Checkout, remove Pay Later as an option because WC Shop As Customer already shows a button for this
			
				$is_available = false;
				
			} else if( get_query_var('order-pay') ) {
				
				// Order Pay page should not display Pay Later
				
				$is_available = false;
				
			}
	        
        }

        return $is_available;
    }
	
}
