<?php
/**
 * The checkout modal's only two server round-trips:
 *
 * 1. appiappi_checkout_create — customer fills the invoice (plan,
 *    billing frequency, contact details) and hits "Continue to Payment".
 *    Creates the Order (status 'pending'), a Stripe Customer, and either
 *    a one-off PaymentIntent (one_time plans) or a Subscription with the
 *    design folded in as a one-off invoice item (monthly/yearly plans —
 *    see includes/pricing.php for why). Returns the client_secret the
 *    frontend needs to mount Stripe's Payment Element.
 *
 * 2. appiappi_checkout_order_status — the return page polls this a few
 *    times after stripe.confirmPayment() redirects back, so the visitor
 *    sees a real "paid" confirmation without waiting on the webhook if
 *    it's already landed (the webhook — includes/webhook.php — remains
 *    the actual source of truth either way).
 */

defined( 'ABSPATH' ) || exit;

function appiappi_checkout_ajax_create() {
	check_ajax_referer( 'appiappi_checkout', 'nonce' );

	if ( ! appiappi_checkout_is_configured() ) {
		wp_send_json_error( array( 'message' => __( 'Payments are not set up on this site yet. Please contact us directly.', 'appiappi-checkout' ) ), 400 );
	}

	$plan_id           = isset( $_POST['plan_id'] ) ? sanitize_key( wp_unslash( $_POST['plan_id'] ) ) : '';
	$billing_frequency = isset( $_POST['billing_frequency'] ) ? sanitize_key( wp_unslash( $_POST['billing_frequency'] ) ) : '';
	$design_post_id    = isset( $_POST['design_post_id'] ) ? (int) $_POST['design_post_id'] : 0;
	$name              = isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '';
	$email             = isset( $_POST['customer_email'] ) ? sanitize_email( wp_unslash( $_POST['customer_email'] ) ) : '';
	$phone             = isset( $_POST['customer_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_phone'] ) ) : '';

	if ( ! $name || ! $email || ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please provide your name and a valid email address.', 'appiappi-checkout' ) ), 400 );
	}

	$order = appiappi_checkout_compute_order( $plan_id, $billing_frequency, $design_post_id );
	if ( is_wp_error( $order ) ) {
		wp_send_json_error( array( 'message' => $order->get_error_message() ), 400 );
	}

	$mode = appiappi_checkout_mode();

	// 1. Create the Order record first (status: pending) so we always
	// have a durable record even if the Stripe call below fails.
	$order_post_id = wp_insert_post( array(
		'post_type'   => 'appiappi_order',
		'post_status' => 'publish',
		'post_title'  => sprintf( '%s — %s (%s)', $name, $order['plan']['name'], $order['billing_frequency'] ),
	), true );

	if ( is_wp_error( $order_post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Could not create the order. Please try again.', 'appiappi-checkout' ) ), 500 );
	}

	update_post_meta( $order_post_id, '_appiappi_order_plan_id', $order['plan']['id'] );
	update_post_meta( $order_post_id, '_appiappi_order_plan_name', $order['plan']['name'] );
	update_post_meta( $order_post_id, '_appiappi_order_billing_frequency', $order['billing_frequency'] );
	update_post_meta( $order_post_id, '_appiappi_order_design_name', $order['design_name'] );
	update_post_meta( $order_post_id, '_appiappi_order_design_price', $order['design_price'] );
	update_post_meta( $order_post_id, '_appiappi_order_discount_percent', $order['discount_percent'] );
	update_post_meta( $order_post_id, '_appiappi_order_total_amount', $order['total_today'] );
	update_post_meta( $order_post_id, '_appiappi_order_currency', $order['currency'] );
	update_post_meta( $order_post_id, '_appiappi_order_customer_name', $name );
	update_post_meta( $order_post_id, '_appiappi_order_customer_email', $email );
	update_post_meta( $order_post_id, '_appiappi_order_customer_phone', $phone );
	update_post_meta( $order_post_id, '_appiappi_order_status', 'pending' );
	update_post_meta( $order_post_id, '_appiappi_order_stripe_mode', $mode );

	// 2. Stripe Customer.
	$customer = appiappi_checkout_stripe_request( 'POST', 'customers', array(
		'name'  => $name,
		'email' => $email,
		'phone' => $phone,
		'metadata' => array( 'appiappi_order_id' => $order_post_id ),
	) );
	if ( appiappi_checkout_is_stripe_error( $customer ) ) {
		update_post_meta( $order_post_id, '_appiappi_order_status', 'failed' );
		wp_send_json_error( array( 'message' => appiappi_checkout_stripe_error_message( $customer ) ), 502 );
	}
	update_post_meta( $order_post_id, '_appiappi_order_stripe_customer_id', $customer['id'] );

	$cents = function ( $amount ) {
		return (int) round( $amount * 100 );
	};

	if ( 'one_time' === $order['billing_frequency'] ) {
		// 3a. One-time plan (or a design bought on its own): a single PaymentIntent.
		// automatic_payment_methods lets Stripe decide what to show (card,
		// wallets, bank debits...) based purely on what's enabled in the
		// Dashboard — no code change needed here when that changes later.
		$intent = appiappi_checkout_stripe_request( 'POST', 'payment_intents', array(
			'amount'        => $cents( $order['total_today'] ),
			'currency'      => $order['currency'],
			'customer'      => $customer['id'],
			'description'   => $order['plan']['name'] . ( $order['design_name'] ? ' + ' . $order['design_name'] : '' ),
			'automatic_payment_methods' => array( 'enabled' => 'true' ),
			'metadata'      => array( 'appiappi_order_id' => $order_post_id ),
		) );
		if ( appiappi_checkout_is_stripe_error( $intent ) ) {
			update_post_meta( $order_post_id, '_appiappi_order_status', 'failed' );
			wp_send_json_error( array( 'message' => appiappi_checkout_stripe_error_message( $intent ) ), 502 );
		}
		update_post_meta( $order_post_id, '_appiappi_order_stripe_payment_intent_id', $intent['id'] );

		wp_send_json_success( array(
			'client_secret'      => $intent['client_secret'],
			'publishable_key'    => appiappi_checkout_get_setting( 'stripe_publishable_key' ),
			'order_id'           => $order_post_id,
		) );
	}

	// 3b. Recurring plan: a Subscription, with the design (if any) as a
	// one-off item on the first invoice only.
	$interval = 'yearly' === $order['billing_frequency'] ? 'year' : 'month';

	$sub_params = array(
		'customer'          => $customer['id'],
		'items'             => array(
			array(
				'price_data' => array(
					'currency'    => $order['currency'],
					'unit_amount' => $cents( $order['recurring_amount'] ),
					'recurring'   => array( 'interval' => $interval ),
					'product_data'=> array( 'name' => $order['plan']['name'] . ' (' . ( 'year' === $interval ? __( 'Annual', 'appiappi-checkout' ) : __( 'Monthly', 'appiappi-checkout' ) ) . ')' ),
				),
			),
		),
		'payment_behavior'  => 'default_incomplete',
		'payment_settings'  => array( 'save_default_payment_method' => 'on_subscription' ),
		'expand'            => array( 'latest_invoice.payment_intent' ),
		'metadata'          => array( 'appiappi_order_id' => $order_post_id ),
	);

	if ( $order['design_price'] > 0 ) {
		$sub_params['add_invoice_items'] = array(
			array(
				'price_data' => array(
					'currency'    => $order['currency'],
					'unit_amount' => $cents( $order['design_price'] ),
					'product_data'=> array( 'name' => __( 'Website Design', 'appiappi-checkout' ) . ': ' . $order['design_name'] ),
				),
			),
		);
	}

	$subscription = appiappi_checkout_stripe_request( 'POST', 'subscriptions', $sub_params );
	if ( appiappi_checkout_is_stripe_error( $subscription ) ) {
		update_post_meta( $order_post_id, '_appiappi_order_status', 'failed' );
		wp_send_json_error( array( 'message' => appiappi_checkout_stripe_error_message( $subscription ) ), 502 );
	}

	$payment_intent = $subscription['latest_invoice']['payment_intent'] ?? null;
	if ( ! $payment_intent || empty( $payment_intent['client_secret'] ) ) {
		update_post_meta( $order_post_id, '_appiappi_order_status', 'failed' );
		wp_send_json_error( array( 'message' => __( 'Stripe did not return a payment form for this subscription.', 'appiappi-checkout' ) ), 502 );
	}

	update_post_meta( $order_post_id, '_appiappi_order_stripe_subscription_id', $subscription['id'] );
	update_post_meta( $order_post_id, '_appiappi_order_stripe_payment_intent_id', $payment_intent['id'] );

	wp_send_json_success( array(
		'client_secret'   => $payment_intent['client_secret'],
		'publishable_key' => appiappi_checkout_get_setting( 'stripe_publishable_key' ),
		'order_id'        => $order_post_id,
	) );
}
add_action( 'wp_ajax_appiappi_checkout_create', 'appiappi_checkout_ajax_create' );
add_action( 'wp_ajax_nopriv_appiappi_checkout_create', 'appiappi_checkout_ajax_create' );

function appiappi_checkout_ajax_order_status() {
	check_ajax_referer( 'appiappi_checkout', 'nonce' );

	$order_id = isset( $_POST['order_id'] ) ? (int) $_POST['order_id'] : 0;
	$post     = $order_id ? get_post( $order_id ) : null;

	if ( ! $post || 'appiappi_order' !== $post->post_type ) {
		wp_send_json_error( array( 'message' => __( 'Order not found.', 'appiappi-checkout' ) ), 404 );
	}

	wp_send_json_success( array(
		'status' => get_post_meta( $order_id, '_appiappi_order_status', true ) ?: 'pending',
	) );
}
add_action( 'wp_ajax_appiappi_checkout_order_status', 'appiappi_checkout_ajax_order_status' );
add_action( 'wp_ajax_nopriv_appiappi_checkout_order_status', 'appiappi_checkout_ajax_order_status' );
