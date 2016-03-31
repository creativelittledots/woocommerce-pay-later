<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
	
class WC_Gateway_Pay_Later extends WC_Payment_Gateway {
	
	public $version 	= '1.0.0';
	
	public function __construct() { 
		
		global $woocommerce;
		
		$this->id			= 'PayLater';
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
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_filter( 'woocommerce_available_payment_gateways', array($this, 'remove_gateway_from_pay_page') );

	}
	
	public function plugin_url() {
	
		return plugins_url( basename( plugin_dir_path(__FILE__) ), basename( __FILE__ ) );
		
	}

	public function plugin_path() {
		
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
		
	}
	
	/**
	 * Initialise Gateway Settings Form Fields
	 */
	function init_form_fields() {
	
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
							'default' => __('Pay later, after you have parked.', 'woocommerce-pay-later')
						)
			);
	
	} // End init_form_fields()
	
	/**
	 * Admin Panel Options 
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 *
	 * @since 1.0.0
	 */
	public function admin_options() {

		?>
		<h3>Pay Later Payment Gateway</h3>
		<p>Allow your customers to choose to pay later</p>
		<p><b>Module Version:</b> 1.0 (For WooCommerce v2.1+)<br />
		<b>Module Date:</b> 20 April 2015</p>
		<table class="form-table">
		<?php
			// Generate the HTML For the settings form.
			$this->generate_settings_html();
		?>
		</table><!--/.form-table-->
		<?php
	} // End admin_options()
	
	/**
	 * Process the payment and return the result
	 **/
	public function process_payment( $order_id ) {
		
		$order = wc_get_order( $order_id );
		
		$order->update_status('pay-later', __( 'Awaiting payment', 'woocommerce' ) );
		
		// Reduce stock levels
		$order->reduce_order_stock();
		
		return array(
			'result' 	=> 'success',
			'redirect'	=> $order->get_checkout_order_received_url()
		);
		
	}
	
	public function remove_gateway_from_pay_page($_available_gateways) {
		
		if(get_query_var('order-pay')) {
			
			$order = new WC_Order(get_query_var('order-pay'));
			
			if($order->get_status() == 'pending') {
				
				unset($_available_gateways['PayLater']);
				
			}
			
		}
		
		return $_available_gateways;
		
	}
	
}