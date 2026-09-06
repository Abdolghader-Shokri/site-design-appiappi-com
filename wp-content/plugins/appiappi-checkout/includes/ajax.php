<?php
/**
 * The checkout modal's only two server round-trips:
 *
 * 1. appiappi_checkout_create — customer fills the invoice (plan,
 *    billing frequency, payment timing, hosting choice if required,
 *    contact details) and hits "Continue to Payment". Creates the Order
 *    (status 'pending'), a Stripe Customer, and — depending on
 *    $order['payment_timing'] and billing frequency, see
 *    includes/pricing.php for the full rules — either a one-off
 *    PaymentIntent (one-time plans, or any plan whose fee is being
 *    deferred until work is done) or a Subscription with the design
 *    and/or hosting folded in as one-off invoice items on the first
 *    invoice only. Returns the client_secret the frontend needs to mount
 *    Stripe's Payment Element.
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
	$payment_timing    = isset( $_POST['payment_timing'] ) ? sanitize_key( wp_unslash( $_POST['payment_timing'] ) ) : 'now';
	$hosting_id        = isset( $_POST['hosting_id'] ) ? (int) $_POST['hosting_id'] : 0;
	$name              = isset( $_POST['customer_name'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_name'] ) ) : '';
	$email             = isset( $_POST['customer_email'] ) ? sanitize_email( wp_unslash( $_POST['customer_email'] ) ) : '';
	$phone             = isset( $_POST['customer_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['customer_phone'] ) ) : '';

	if ( ! $name || ! $email || ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please provide your name and a valid email address.', 'appiappi-checkout' ) ), 400 );
	}

	$order = appiappi_checkout_compute_order( $plan_id, $billing_frequency, $design_post_id, $payment_timing, $hosting_id );
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
	update_post_meta( $order_post_id, '_appiappi_order_payment_timing', $order['payment_timing'] );
	update_post_meta( $order_post_id, '_appiappi_order_design_name', $order['design_name'] );
	update_post_meta( $order_post_id, '_appiappi_order_design_price', $order['design_price'] );
	update_post_meta( $order_post_id, '_appiappi_order_design_credit_applied', $order['design_credit_applied'] );
	update_post_meta( $order_post_id, '_appiappi_order_hosting_id', $order['hosting']['id'] );
	update_post_meta( $order_post_id, '_appiappi_order_hosting_label', $order['hosting']['location'] . ' — ' . ( $order['hosting']['storageUnlimited'] ? __( 'Unlimited storage', 'appiappi-checkout' ) : $order['hosting']['storageAmount'] ) );
	update_post_meta( $order_post_id, '_appiappi_order_hosting_price', $order['hosting_price'] );
	update_post_meta( $order_post_id, '_appiappi_order_hosting_original_price', $order['hosting_original_price'] );
	update_post_meta( $order_post_id, '_appiappi_order_hosting_is_free', $order['hosting_is_free'] ? 1 : 0 );
	update_post_meta( $order_post_id, '_appiappi_order_discount_percent', $order['discount_percent'] );
	update_post_meta( $order_post_id, '_appiappi_order_plan_amount', $order['plan_amount'] );
	update_post_meta( $order_post_id, '_appiappi_order_total_amount', $order['charged_today'] );
	update_post_meta( $order_post_id, '_appiappi_order_deferred_amount', $order['deferred_amount'] );
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

	$extra_items_description = array_filter( array(
		$order['design_name'],
		$order['hosting_price'] > 0 ? $order['hosting']['location'] . ' ' . __( 'Hosting', 'appiappi-checkout' ) : '',
	) );

	// 3a. Plan fee deferred until work is complete: charge only design
	// (after credit) + hosting today, if there's anything to charge at
	// all. The plan's own Subscription/PaymentIntent for $order['plan_amount']
	// is created later, manually, once the work is actually done — not
	// automated here, since "work completed" isn't a fixed calendar date
	// Stripe could bill against on its own.
	if ( 'later' === $order['payment_timing'] ) {
		if ( $order['charged_today'] <= 0 ) {
			update_post_meta( $order_post_id, '_appiappi_order_status', 'failed' );
			wp_send_json_error( array( 'message' => __( 'Nothing to charge today for this selection.', 'appiappi-checkout' ) ), 400 );
		}

		$intent = appiappi_checkout_stripe_request( 'POST', 'payment_intents', array(
			'amount'                    => $cents( $order['charged_today'] ),
			'currency'                  => $order['currency'],
			'customer'                  => $customer['id'],
			'description'               => implode( ' + ', $extra_items_description ) ?: $order['plan']['name'],
			'automatic_payment_methods' => array( 'enabled' => 'true' ),
			'metadata'                  => array( 'appiappi_order_id' => $order_post_id ),
		) );
		if ( appiappi_checkout_is_stripe_error( $intent ) ) {
			update_post_meta( $order_post_id, '_appiappi_order_status', 'failed' );
			wp_send_json_error( array( 'message' => appiappi_checkout_stripe_error_message( $intent ) ), 502 );
		}
		update_post_meta( $order_post_id, '_appiappi_order_stripe_payment_intent_id', $intent['id'] );

		wp_send_json_success( array(
			'client_secret'   => $intent['client_secret'],
			'publishable_key' => appiappi_checkout_get_setting( 'stripe_publishable_key' ),
			'order_id'        => $order_post_id,
		) );
	}

	// 3b. Paying in full today, one-time plan: a single PaymentIntent for
	// plan + design (after credit) + hosting.
	if ( 'one_time' === $order['billing_frequency'] ) {
		// automatic_payment_methods lets Stripe decide what to show (card,
		// wallets, bank debits...) based purely on what's enabled in the
		// Dashboard — no code change needed here when that changes later.
		$intent = appiappi_checkout_stripe_request( 'POST', 'payment_intents', array(
			'amount'        => $cents( $order['charged_today'] ),
			'currency'      => $order['currency'],
			'customer'      => $customer['id'],
			'description'   => $order['plan']['name'] . ( $extra_items_description ? ' + ' . implode( ' + ', $extra_items_description ) : '' ),
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

	// 3c. Paying in full today, recurring plan: a Subscription, with the
	// design and/or hosting (whichever apply) as one-off items on the
	// first invoice only.
	$interval = 'yearly' === $order['billing_frequency'] ? 'year' : 'month';

	$sub_params = array(
		'customer'          => $customer['id'],
		'items'             => array(
			array(
				'price_data' => array(
					'currency'    => $order['currency'],
					'unit_amount' => $cents( $order['plan_amount'] ),
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

	$invoice_items = array();
	if ( $order['design_price_after_credit'] > 0 ) {
		$invoice_items[] = array(
			'price_data' => array(
				'currency'    => $order['currency'],
				'unit_amount' => $cents( $order['design_price_after_credit'] ),
				'product_data'=> array( 'name' => __( 'Website Design', 'appiappi-checkout' ) . ': ' . $order['design_name'] ),
			),
		);
	}
	if ( $order['hosting_price'] > 0 ) {
		$invoice_items[] = array(
			'price_data' => array(
				'currency'    => $order['currency'],
				'unit_amount' => $cents( $order['hosting_price'] ),
				'product_data'=> array( 'name' => __( 'Hosting', 'appiappi-checkout' ) . ': ' . $order['hosting']['location'] ),
			),
		);
	}
	if ( $invoice_items ) {
		$sub_params['add_invoice_items'] = $invoice_items;
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
