<?php
/**
 * The one place checkout amounts get computed — used by the AJAX handler
 * right before talking to Stripe. Deliberately never trusts anything the
 * browser sends about price, hosting, or eligibility; it only ever
 * trusts IDs (plan slug, design post ID, hosting package post ID) looked
 * up fresh against their own CPTs, so nothing a visitor could tamper
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
		'id'                    => $post->post_name,
		'name'                  => get_the_title( $post ),
		'price'                 => (float) preg_replace( '/[^0-9.]/', '', (string) get_post_meta( $post->ID, '_appiappi_plan_price', true ) ),
		'billing_frequency'     => $billing_frequency ?: 'one_time',
		'color_key'             => get_post_meta( $post->ID, '_appiappi_plan_color', true ) ?: 'business',
		'includes_free_hosting' => (bool) get_post_meta( $post->ID, '_appiappi_plan_includes_free_hosting', true ),
		'design_credit'         => (float) get_post_meta( $post->ID, '_appiappi_plan_design_credit', true ),
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
 * Full server-side price breakdown for one checkout attempt — the only
 * function that decides what a customer actually owes today vs. later.
 *
 * Business rules (revised 2026-09-07):
 * - A plan may or may not include free hosting (`includes_free_hosting`).
 * - "Pay after work is completed" (deferring the plan's own fee) is
 *   only ever offered for plans WITH free hosting — for a plan without
 *   it, full payment upfront is enforced here regardless of what the
 *   client requested, since there's no free-hosting bridge to cover the
 *   gap otherwise.
 * - A hosting package is ALWAYS required, in every scenario — every
 *   site needs to be hosted somewhere, and even a "this is free" plan
 *   perk still needs the customer to say which package to provision.
 *   What varies is only whether it's *charged*: it's free ($0, but the
 *   real annual price is still returned as `hosting_original_price` so
 *   the invoice can show it struck through with a "now free" label)
 *   only when the plan includes free hosting AND the customer is paying
 *   in full today — every other combination (no free hosting at all, or
 *   the plan fee is being deferred, since the free-hosting perk only
 *   activates once the plan is actually paid) charges the real price.
 * - A plan's Website Design credit only applies when paying in full
 *   today — never when deferring the plan fee. It reduces the design's
 *   price (never below $0); any leftover credit past the design's price
 *   is forfeited, not applied to the plan or hosting cost.
 *
 * @param string $plan_id           Plan slug.
 * @param string $billing_frequency 'one_time' | 'monthly' | 'yearly'.
 * @param int    $design_post_id    Optional selected Website Design.
 * @param string $payment_timing    'now' | 'later'.
 * @param int    $hosting_id        Selected Hosting Package post ID —
 *                                   always required (see rules above).
 * @return array|WP_Error
 */
function appiappi_checkout_compute_order( $plan_id, $billing_frequency, $design_post_id = 0, $payment_timing = 'now', $hosting_id = 0 ) {
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

	$payment_timing = in_array( $payment_timing, array( 'now', 'later' ), true ) ? $payment_timing : 'now';
	// The one enforcement point for the whole feature: a plan with no
	// free hosting always requires full payment today, no matter what
	// was requested.
	if ( ! $plan['includes_free_hosting'] ) {
		$payment_timing = 'now';
	}

	$hosting = appiappi_checkout_get_hosting_package( $hosting_id );
	if ( ! $hosting ) {
		return new WP_Error( 'appiappi_checkout_bad_hosting', __( 'Please select a hosting package.', 'appiappi-checkout' ) );
	}
	$hosting_is_free       = $plan['includes_free_hosting'] && 'now' === $payment_timing;
	$hosting_original_price = $hosting['annualPrice'];
	$hosting_price          = $hosting_is_free ? 0.0 : $hosting_original_price;

	$discount_percent = 0.0;
	$plan_amount      = $plan['price'];
	if ( 'yearly' === $billing_frequency && 'monthly' === $plan['billing_frequency'] ) {
		$discount_percent = (float) appiappi_checkout_get_setting( 'annual_discount_percent', 5 );
		$annual_list      = $plan['price'] * 12;
		$plan_amount      = round( $annual_list * ( 1 - $discount_percent / 100 ), 2 );
	}

	$design_price = appiappi_checkout_get_design_price( $design_post_id );
	$design_name  = $design_price > 0 ? get_the_title( (int) $design_post_id ) : '';

	$credit_applied = 0.0;
	$design_price_after_credit = $design_price;
	if ( 'now' === $payment_timing && $plan['design_credit'] > 0 && $design_price > 0 ) {
		$credit_applied            = min( $plan['design_credit'], $design_price );
		$design_price_after_credit = round( $design_price - $credit_applied, 2 );
	}

	// Always charged today, regardless of payment timing.
	$charged_today_extra = $design_price_after_credit + $hosting_price;

	$charged_today   = 'now' === $payment_timing ? $plan_amount + $charged_today_extra : $charged_today_extra;
	$deferred_amount = 'later' === $payment_timing ? $plan_amount : 0.0;

	return array(
		'plan'                      => $plan,
		'billing_frequency'         => $billing_frequency,
		'payment_timing'            => $payment_timing,
		'design_post_id'            => (int) $design_post_id,
		'design_name'               => $design_name,
		'design_price'              => $design_price,
		'design_credit_applied'     => $credit_applied,
		'design_price_after_credit' => $design_price_after_credit,
		'hosting'                   => $hosting,
		'hosting_is_free'           => $hosting_is_free,
		'hosting_original_price'    => $hosting_original_price,
		'hosting_price'             => $hosting_price,
		'discount_percent'          => $discount_percent,
		'plan_amount'               => $plan_amount,
		'charged_today'             => round( $charged_today, 2 ),
		'deferred_amount'           => round( $deferred_amount, 2 ),
		'currency'                  => appiappi_checkout_currency(),
	);
}
