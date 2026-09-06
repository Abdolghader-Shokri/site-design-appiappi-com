<?php
/**
 * Minimal Stripe REST API client — deliberately not the Stripe PHP SDK
 * (that would need Composer/vendor autoloading, which this project's
 * plugins don't otherwise use). Stripe's API is plain REST with the
 * secret key as HTTP Basic Auth username, so wp_remote_post() covers
 * everything this plugin actually needs: PaymentIntents, Customers,
 * Subscriptions, and one-off invoice items.
 */

defined( 'ABSPATH' ) || exit;

define( 'APPIAPPI_CHECKOUT_STRIPE_API_BASE', 'https://api.stripe.com/v1' );

/**
 * Calls one Stripe API endpoint. $params is sent as a standard
 * application/x-www-form-urlencoded body — Stripe's own documented
 * format, including PHP's native "array[key]=value" encoding for
 * nested params (e.g. `items[0][price_data][unit_amount]`), which
 * http_build_query() produces natively.
 *
 * Returns the decoded response array (Stripe error responses decode
 * to an array with an 'error' key — check for that, not just is_wp_error)
 * or a WP_Error for a transport-level failure (network, timeout).
 */
function appiappi_checkout_stripe_request( $method, $endpoint, $params = array() ) {
	$secret_key = appiappi_checkout_get_setting( 'stripe_secret_key' );
	if ( ! $secret_key ) {
		return new WP_Error( 'appiappi_checkout_not_configured', __( 'Stripe is not configured yet.', 'appiappi-checkout' ) );
	}

	$args = array(
		'method'  => strtoupper( $method ),
		'timeout' => 20,
		'headers' => array(
			'Authorization' => 'Basic ' . base64_encode( $secret_key . ':' ),
			'Content-Type'  => 'application/x-www-form-urlencoded',
			'Stripe-Version'=> '2024-06-20',
		),
	);

	$url = APPIAPPI_CHECKOUT_STRIPE_API_BASE . '/' . ltrim( $endpoint, '/' );

	if ( 'GET' === $args['method'] ) {
		if ( $params ) {
			$url .= '?' . http_build_query( $params );
		}
	} else {
		$args['body'] = http_build_query( $params );
	}

	$response = wp_remote_request( $url, $args );
	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$body = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $body ) ) {
		return new WP_Error( 'appiappi_checkout_bad_response', __( 'Stripe returned an unreadable response.', 'appiappi-checkout' ) );
	}

	return $body;
}

/**
 * True when $result is a well-formed Stripe error response (as opposed
 * to a WP_Error transport failure, or a successful object) — the one
 * helper every caller needs since Stripe API errors come back as HTTP
 * 4xx/5xx with a normal JSON body, not as a WordPress-level error.
 */
function appiappi_checkout_is_stripe_error( $result ) {
	return is_wp_error( $result ) || ( is_array( $result ) && isset( $result['error'] ) );
}

function appiappi_checkout_stripe_error_message( $result ) {
	if ( is_wp_error( $result ) ) {
		return $result->get_error_message();
	}
	return $result['error']['message'] ?? __( 'Unknown Stripe error.', 'appiappi-checkout' );
}
