<?php
/**
 * Stripe webhook receiver — the actual source of truth for order status
 * (never the browser: a visitor's tab closing, a flaky connection after
 * stripe.confirmPayment(), or a card needing a 3-D Secure redirect all
 * mean the frontend alone can't be trusted to know whether payment truly
 * went through). URL: rest_url('appiappi/v1/stripe-webhook') — shown
 * on the settings page, register once in the Stripe Dashboard.
 *
 * Signature verification follows Stripe's documented algorithm exactly
 * (https://stripe.com/docs/webhooks#verify-manually) since this plugin
 * doesn't use the Stripe SDK — HMAC-SHA256 over "{timestamp}.{raw body}"
 * using the webhook signing secret, compared with hash_equals(), plus a
 * timestamp tolerance to reject stale/replayed requests.
 */

defined( 'ABSPATH' ) || exit;

define( 'APPIAPPI_CHECKOUT_WEBHOOK_TOLERANCE', 300 ); // seconds

function appiappi_checkout_register_webhook_route() {
	register_rest_route( 'appiappi/v1', '/stripe-webhook', array(
		'methods'             => 'POST',
		'callback'            => 'appiappi_checkout_handle_webhook',
		'permission_callback' => '__return_true', // Auth is the signature check inside, not a WP capability — Stripe isn't a logged-in WP user.
	) );
}
add_action( 'rest_api_init', 'appiappi_checkout_register_webhook_route' );

function appiappi_checkout_verify_webhook_signature( $payload, $signature_header, $secret ) {
	if ( ! $signature_header || ! $secret ) {
		return false;
	}

	$parts = array();
	foreach ( explode( ',', $signature_header ) as $part ) {
		$pair = explode( '=', $part, 2 );
		if ( 2 === count( $pair ) ) {
			$parts[ trim( $pair[0] ) ] = trim( $pair[1] );
		}
	}

	if ( empty( $parts['t'] ) || empty( $parts['v1'] ) ) {
		return false;
	}

	if ( abs( time() - (int) $parts['t'] ) > APPIAPPI_CHECKOUT_WEBHOOK_TOLERANCE ) {
		return false;
	}

	$expected = hash_hmac( 'sha256', $parts['t'] . '.' . $payload, $secret );
	return hash_equals( $expected, $parts['v1'] );
}

/**
 * Finds the appiappi_order post a Stripe object belongs to, preferring
 * the metadata we stamped on it at creation time and falling back to a
 * meta lookup by the stored PaymentIntent ID (covers events — like a
 * subscription invoice — that don't carry our metadata directly).
 */
function appiappi_checkout_find_order( $metadata, $payment_intent_id = '', $subscription_id = '' ) {
	if ( ! empty( $metadata['appiappi_order_id'] ) ) {
		$post = get_post( (int) $metadata['appiappi_order_id'] );
		if ( $post && 'appiappi_order' === $post->post_type ) {
			return $post->ID;
		}
	}

	$meta_key   = $payment_intent_id ? '_appiappi_order_stripe_payment_intent_id' : '_appiappi_order_stripe_subscription_id';
	$meta_value = $payment_intent_id ?: $subscription_id;
	if ( ! $meta_value ) {
		return 0;
	}

	$posts = get_posts( array(
		'post_type'      => 'appiappi_order',
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'meta_key'       => $meta_key,
		'meta_value'     => $meta_value,
		'fields'         => 'ids',
	) );

	return $posts ? $posts[0] : 0;
}

function appiappi_checkout_mark_order( $order_id, $status ) {
	if ( ! $order_id ) {
		return;
	}
	$previous = get_post_meta( $order_id, '_appiappi_order_status', true );
	update_post_meta( $order_id, '_appiappi_order_status', $status );

	if ( 'paid' === $status && 'paid' !== $previous ) {
		appiappi_checkout_send_order_emails( $order_id );
	}
}

function appiappi_checkout_send_order_emails( $order_id ) {
	$customer_name  = get_post_meta( $order_id, '_appiappi_order_customer_name', true );
	$customer_email = get_post_meta( $order_id, '_appiappi_order_customer_email', true );
	$plan_name      = get_post_meta( $order_id, '_appiappi_order_plan_name', true );
	$design_name    = get_post_meta( $order_id, '_appiappi_order_design_name', true );
	$total          = get_post_meta( $order_id, '_appiappi_order_total_amount', true );
	$currency       = strtoupper( get_post_meta( $order_id, '_appiappi_order_currency', true ) ?: 'cad' );

	$lines = array(
		sprintf( __( 'Plan: %s', 'appiappi-checkout' ), $plan_name ),
	);
	if ( $design_name ) {
		$lines[] = sprintf( __( 'Website Design: %s', 'appiappi-checkout' ), $design_name );
	}
	$lines[] = sprintf( __( 'Amount charged today: %s %s', 'appiappi-checkout' ), number_format( (float) $total, 2 ), $currency );

	// Admin notification.
	wp_mail(
		get_option( 'admin_email' ),
		sprintf( '[%s] %s', get_bloginfo( 'name' ), __( 'New paid order', 'appiappi-checkout' ) ),
		sprintf( "%s\n\n%s\n\n%s", sprintf( __( 'New order from %s (%s):', 'appiappi-checkout' ), $customer_name, $customer_email ), implode( "\n", $lines ), admin_url( 'post.php?post=' . $order_id . '&action=edit' ) )
	);

	// Customer receipt.
	if ( $customer_email && is_email( $customer_email ) ) {
		wp_mail(
			$customer_email,
			sprintf( __( 'Your %s order is confirmed', 'appiappi-checkout' ), get_bloginfo( 'name' ) ),
			sprintf( "%s,\n\n%s\n\n%s\n\n%s", $customer_name, __( 'Thank you — your payment was successful and your order is confirmed:', 'appiappi-checkout' ), implode( "\n", $lines ), __( "We'll be in touch shortly to get started.", 'appiappi-checkout' ) )
		);
	}
}

function appiappi_checkout_handle_webhook( WP_REST_Request $request ) {
	$secret  = appiappi_checkout_get_setting( 'stripe_webhook_secret' );
	$payload = $request->get_body();
	$sig     = $request->get_header( 'stripe_signature' );

	if ( ! appiappi_checkout_verify_webhook_signature( $payload, $sig, $secret ) ) {
		return new WP_REST_Response( array( 'error' => 'invalid signature' ), 400 );
	}

	$event = json_decode( $payload, true );
	if ( ! is_array( $event ) || empty( $event['type'] ) ) {
		return new WP_REST_Response( array( 'error' => 'bad payload' ), 400 );
	}

	$object = $event['data']['object'] ?? array();

	switch ( $event['type'] ) {
		case 'payment_intent.succeeded':
			$order_id = appiappi_checkout_find_order( $object['metadata'] ?? array(), $object['id'] ?? '' );
			appiappi_checkout_mark_order( $order_id, 'paid' );
			break;

		case 'payment_intent.payment_failed':
			$order_id = appiappi_checkout_find_order( $object['metadata'] ?? array(), $object['id'] ?? '' );
			appiappi_checkout_mark_order( $order_id, 'failed' );
			break;

		case 'invoice.paid':
			$order_id = appiappi_checkout_find_order(
				$object['subscription_details']['metadata'] ?? array(),
				$object['payment_intent'] ?? '',
				$object['subscription'] ?? ''
			);
			appiappi_checkout_mark_order( $order_id, 'paid' );
			break;

		case 'invoice.payment_failed':
			$order_id = appiappi_checkout_find_order(
				$object['subscription_details']['metadata'] ?? array(),
				$object['payment_intent'] ?? '',
				$object['subscription'] ?? ''
			);
			appiappi_checkout_mark_order( $order_id, 'failed' );
			break;
	}

	return new WP_REST_Response( array( 'received' => true ), 200 );
}
