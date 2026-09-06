<?php
/**
 * The one place checkout amounts get computed — used by the AJAX handler
 * right before talking to Stripe. Deliberately never trusts anything the
 * browser sends about price; it only ever trusts a plan ID (looked up
 * fresh against the appiappi_plan CPT) and a design post ID (looked up
 * fresh against appiappi_template), so nothing a visitor could tamper
 * with in the page or a network request can change what they're charged.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Looks up one appiappi_plan post by its slug (the same "id" the
 * pricing-plans plugin already uses everywhere else) and returns the
 * fields checkout needs, or null if it doesn't exist / isn't published.
 */
function appiappi_checkout_get_plan( $plan_id ) {
	$post = get_page_by_path( $plan_id, OBJECT, 'appiappi_plan' );
	if ( ! $post || 'publish' !== $post->post_status ) {
		return null;
	}

	$billing_frequency = get_post_meta( $post->ID, '_appiappi_plan_billing_frequency', true );
	if ( ! $billing_frequency && function_exists( 'appiappi_pricing_infer_billing_frequency' ) ) {
		$billing_frequency = appiappi_pricing_infer_billing_frequency( get_post_meta( $post->ID, '_appiappi_plan_period', true ) );
	}

	return array(
		'id'                => $post->post_name,
		'name'              => get_the_title( $post ),
		'price'             => (float) preg_replace( '/[^0-9.]/', '', (string) get_post_meta( $post->ID, '_appiappi_plan_price', true ) ),
		'billing_frequency' => $billing_frequency ?: 'one_time',
		'color_key'         => get_post_meta( $post->ID, '_appiappi_plan_color', true ) ?: 'business',
	);
}

/**
 * Looks up one appiappi_template post's real, current price by ID —
 * never the price a query-string/AJAX payload claims a design costs.
 */
function appiappi_checkout_get_design_price( $design_post_id ) {
	if ( ! $design_post_id ) {
		return 0.0;
	}
	$post = get_post( (int) $design_post_id );
	if ( ! $post || 'appiappi_template' !== $post->post_type || 'publish' !== $post->post_status ) {
		return 0.0;
	}
	$value = get_post_meta( $post->ID, '_appiappi_template_price_value', true );
	return $value ? (float) $value : 0.0;
}

/**
 * Full server-side price breakdown for one checkout attempt.
 *
 * @param string $plan_id           Plan slug.
 * @param string $billing_frequency 'one_time' | 'monthly' | 'yearly' —
 *                                   for a plan whose own frequency is
 *                                   'monthly', the customer may choose
 *                                   'yearly' at checkout (the discount
 *                                   toggle); any other combination is
 *                                   rejected rather than guessed at.
 * @param int    $design_post_id    Optional selected design.
 * @return array|WP_Error {plan, design_name, design_price, base_amount,
 *                          discount_percent, discount_amount, recurring_amount,
 *                          one_time_amount, currency, billing_frequency}
 */
function appiappi_checkout_compute_order( $plan_id, $billing_frequency, $design_post_id = 0 ) {
	$plan = appiappi_checkout_get_plan( $plan_id );
	if ( ! $plan ) {
		return new WP_Error( 'appiappi_checkout_bad_plan', __( 'That plan could not be found.', 'appiappi-checkout' ) );
	}

	$allowed = array( $plan['billing_frequency'] );
	if ( 'monthly' === $plan['billing_frequency'] ) {
		$allowed[] = 'yearly';
	}
	if ( ! in_array( $billing_frequency, $allowed, true ) ) {
		return new WP_Error( 'appiappi_checkout_bad_frequency', __( 'That billing frequency is not available for this plan.', 'appiappi-checkout' ) );
	}

	$discount_percent = 0.0;
	$recurring_amount = $plan['price'];

	if ( 'yearly' === $billing_frequency && 'monthly' === $plan['billing_frequency'] ) {
		$discount_percent = (float) appiappi_checkout_get_setting( 'annual_discount_percent', 5 );
		$annual_list       = $plan['price'] * 12;
		$recurring_amount  = round( $annual_list * ( 1 - $discount_percent / 100 ), 2 );
	}

	$design_price = appiappi_checkout_get_design_price( $design_post_id );
	$design_name  = $design_price > 0 ? get_the_title( (int) $design_post_id ) : '';

	// The design is a one-time build cost, charged once regardless of
	// whether the plan itself is a recurring subscription — it rides on
	// the first invoice/payment rather than being multiplied into every
	// future billing cycle.
	$one_time_amount = 'one_time' === $billing_frequency ? $recurring_amount + $design_price : $design_price;

	return array(
		'plan'              => $plan,
		'billing_frequency' => $billing_frequency,
		'design_post_id'    => (int) $design_post_id,
		'design_name'       => $design_name,
		'design_price'      => $design_price,
		'discount_percent'  => $discount_percent,
		// For a recurring plan this is the amount billed every cycle
		// (design price excluded); for a one_time plan it's 0 since
		// everything is folded into $one_time_amount instead.
		'recurring_amount'  => 'one_time' === $billing_frequency ? 0.0 : $recurring_amount,
		// The amount actually charged today.
		'one_time_amount'   => $one_time_amount,
		'total_today'       => 'one_time' === $billing_frequency ? $one_time_amount : $recurring_amount + $one_time_amount,
		'currency'          => appiappi_checkout_currency(),
	);
}
