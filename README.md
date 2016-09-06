## WooCommerce Pay Later Payment Gateway

A Payment Gateway for WooCommerce that allows customers to Pay Later at Checkout.

After a Customer has checked via Pay Later, the Customer is directed to the Order Received page and is sent an invoice using the built in WooCommerce Customer Invoice email.

There is a filter you can use called **wc_pay_later_order_received_url** to direct to a custom page if you'd prefer to direct the Customer to a different page:

```php
apply_filters( 'wc_pay_later_order_received_url', $order->get_checkout_order_received_url(), $order, $this )
```

Which can be used in functions.php like this:

```php
add_filter( 'wc_pay_later_order_received_url', 'redirect_to_custom_page', 10, 3 );

function redirect_to_custom_page( $url, $order, $gateway ) {
	
	return add_query_arg(array(
		'order_id' => $order->id,
		'gateway_id' => $gateway->id,
		'some_arbitrary' => 'text'
	), 'http://somedomain.com');
	
}
```

Orders that are checked out via Pay Later result in the status of **pending**. Customers can pay for the Order by click the **Pay button** in their My Account > Orders area.

## Installation

1. Upload the plugin to the **/wp-content/plugins/** directory
2. Activate the plugin through the 'Plugins' menu in WordPress

## Requirements

PHP 5.4+

Wordpress 4+

WooCommerce 2.5+

## License

[GNU General Public License v3.0](http://www.gnu.org/licenses/gpl-3.0.html)