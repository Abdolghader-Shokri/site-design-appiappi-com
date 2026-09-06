<?php
/**
 * The checkout modal itself: enqueues Stripe.js + this plugin's own
 * assets on the Pricing page only, hands the frontend everything it
 * needs to render/compute totals for *display* (the AJAX handler in
 * includes/ajax.php independently recomputes the real amount server-side
 * before ever talking to Stripe — nothing here is a security boundary),
 * and prints the modal's HTML shell once in the footer.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Reads ?design_id= from the current request and returns the design's
 * real, current name + price (or null if absent/invalid/unpublished) —
 * the one place appiappi_render_pricing_cards() and the modal's initial
 * state both pull this from, so the URL param is only ever a *lookup
 * key*, never a value that's trusted directly.
 */
function appiappi_checkout_get_selected_design() {
	if ( empty( $_GET['design_id'] ) ) {
		return null;
	}
	$post_id = (int) $_GET['design_id'];
	$post    = get_post( $post_id );
	if ( ! $post || 'appiappi_template' !== $post->post_type || 'publish' !== $post->post_status ) {
		return null;
	}
	$price = appiappi_checkout_get_design_price( $post_id );
	if ( $price <= 0 ) {
		return null;
	}
	return array(
		'id'    => $post_id,
		'name'  => get_the_title( $post ),
		'price' => $price,
	);
}

function appiappi_checkout_is_pricing_page() {
	return is_page_template( 'page-pricing.php' ) || is_page( 'pricing' );
}

function appiappi_checkout_enqueue_assets() {
	if ( ! appiappi_checkout_is_pricing_page() ) {
		return;
	}

	wp_enqueue_style( 'appiappi-checkout', APPIAPPI_CHECKOUT_URL . 'assets/checkout.css', array(), APPIAPPI_CHECKOUT_VERSION );

	// Stripe requires Stripe.js to be loaded directly from their own CDN
	// (never bundled/self-hosted) as a condition of using Elements/Payment
	// Element at all — this is a hard Stripe integration requirement, not
	// a stylistic choice.
	wp_enqueue_script( 'stripe-js', 'https://js.stripe.com/v3/', array(), null, true );
	wp_enqueue_script( 'appiappi-checkout', APPIAPPI_CHECKOUT_URL . 'assets/checkout.js', array( 'stripe-js' ), APPIAPPI_CHECKOUT_VERSION, true );

	$plans = array();
	if ( function_exists( 'appiappi_pricing_get_plans' ) ) {
		foreach ( appiappi_pricing_get_plans() as $plan ) {
			$plan_data = appiappi_checkout_get_plan( $plan['id'] );
			if ( ! $plan_data ) {
				continue;
			}
			$plans[ $plan['id'] ] = array(
				'id'                   => $plan_data['id'],
				'name'                 => $plan_data['name'],
				'price'                => $plan_data['price'],
				'billingFrequency'     => $plan_data['billing_frequency'],
				'color'                => $plan['color'],
				'includesFreeHosting'  => $plan_data['includes_free_hosting'],
				'designCredit'         => $plan_data['design_credit'],
			);
		}
	}

	wp_localize_script( 'appiappi-checkout', 'appiappiCheckout', array(
		'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
		'nonce'           => wp_create_nonce( 'appiappi_checkout' ),
		'configured'      => appiappi_checkout_is_configured(),
		'plans'           => $plans,
		'hostingPackages' => function_exists( 'appiappi_checkout_get_hosting_packages' ) ? appiappi_checkout_get_hosting_packages() : array(),
		'selectedDesign'  => appiappi_checkout_get_selected_design(),
		'annualDiscount'  => (float) appiappi_checkout_get_setting( 'annual_discount_percent', 5 ),
		'currency'        => strtoupper( appiappi_checkout_currency() ),
		'returnUrl'       => remove_query_arg( array( 'checkout', 'order_id' ) ),
		'i18n'            => array(
			'monthly'          => __( 'Monthly', 'appiappi-checkout' ),
			'annual'           => __( 'Annual', 'appiappi-checkout' ),
			'save'             => __( 'Save', 'appiappi-checkout' ),
			'oneTime'          => __( 'One-time', 'appiappi-checkout' ),
			'websiteDesign'    => __( 'Website Design', 'appiappi-checkout' ),
			'designCredit'     => __( 'Design Credit', 'appiappi-checkout' ),
			'hosting'          => __( 'Hosting', 'appiappi-checkout' ),
			'totalDueToday'    => __( 'Total due today', 'appiappi-checkout' ),
			'thenBilled'       => __( 'then %s billed %s, cancel anytime', 'appiappi-checkout' ),
			'dueOnCompletion'  => __( '%s due when your website is complete', 'appiappi-checkout' ),
			'payNowOption'     => __( 'Pay in full now', 'appiappi-checkout' ),
			'payLaterOption'   => __( 'Pay after work is completed', 'appiappi-checkout' ),
			'payLaterHint'     => __( 'Design and hosting are paid today; the plan fee is due once your website is ready.', 'appiappi-checkout' ),
			'hostingRequiredNote' => __( 'This plan doesn\'t include free hosting, so a hosting package is required.', 'appiappi-checkout' ),
			'chooseLocation'   => __( 'Choose a location…', 'appiappi-checkout' ),
			'chooseStorage'    => __( 'Choose storage…', 'appiappi-checkout' ),
			'chooseTraffic'    => __( 'Choose traffic…', 'appiappi-checkout' ),
			'location'         => __( 'Location', 'appiappi-checkout' ),
			'storage'          => __( 'Storage', 'appiappi-checkout' ),
			'traffic'          => __( 'Traffic', 'appiappi-checkout' ),
			'unlimited'        => __( 'Unlimited', 'appiappi-checkout' ),
			'continueToPayment'=> __( 'Continue to Payment', 'appiappi-checkout' ),
			'payNow'           => __( 'Pay Now', 'appiappi-checkout' ),
			'processing'       => __( 'Processing…', 'appiappi-checkout' ),
			'genericError'     => __( 'Something went wrong. Please try again.', 'appiappi-checkout' ),
			'notConfigured'    => __( 'Payments are not set up on this site yet. Please contact us directly to get started.', 'appiappi-checkout' ),
			'checkingStatus'   => __( 'Confirming your payment…', 'appiappi-checkout' ),
			'successTitle'     => __( "You're all set!", 'appiappi-checkout' ),
			'successBody'      => __( "Payment received — thank you! We'll be in touch shortly to get started.", 'appiappi-checkout' ),
			'failedTitle'      => __( 'Payment not completed', 'appiappi-checkout' ),
			'failedBody'       => __( "Your payment didn't go through. No charge was made — please try again or contact us for help.", 'appiappi-checkout' ),
			'selectHosting'    => __( 'Please finish choosing a hosting package.', 'appiappi-checkout' ),
		),
	) );
}
add_action( 'wp_enqueue_scripts', 'appiappi_checkout_enqueue_assets' );

function appiappi_checkout_print_modal() {
	if ( ! appiappi_checkout_is_pricing_page() ) {
		return;
	}
	?>
	<div id="appiappi-checkout-modal" class="checkout-modal" hidden>
		<div class="checkout-modal__overlay" data-checkout-close></div>
		<div class="checkout-modal__panel" role="dialog" aria-modal="true" aria-labelledby="appiappi-checkout-title">
			<button type="button" class="checkout-modal__close" data-checkout-close aria-label="<?php esc_attr_e( 'Close', 'appiappi-checkout' ); ?>">
				<?php echo appiappi_icon( 'close' ); ?>
			</button>

			<div class="checkout-step" data-checkout-step="invoice">
				<h2 id="appiappi-checkout-title" class="checkout-modal__title" data-checkout-plan-name></h2>

				<div class="checkout-payment-timing" data-checkout-payment-timing hidden>
					<label class="checkout-payment-timing__option">
						<input type="radio" name="appiappi_payment_timing" value="now" checked>
						<span><?php esc_html_e( 'Pay in full now', 'appiappi-checkout' ); ?></span>
					</label>
					<label class="checkout-payment-timing__option">
						<input type="radio" name="appiappi_payment_timing" value="later">
						<span><?php esc_html_e( 'Pay after work is completed', 'appiappi-checkout' ); ?></span>
					</label>
					<p class="checkout-payment-timing__hint" data-checkout-payment-timing-hint></p>
				</div>
				<p class="checkout-hosting-required-note" data-checkout-hosting-required-note hidden></p>

				<div class="checkout-hosting-selector" data-checkout-hosting-selector hidden>
					<div class="form-row">
						<label for="appiappi-checkout-hosting-location" data-checkout-hosting-location-label></label>
						<select id="appiappi-checkout-hosting-location" data-checkout-hosting-location></select>
					</div>
					<div class="form-row" data-checkout-hosting-storage-row hidden>
						<label for="appiappi-checkout-hosting-storage" data-checkout-hosting-storage-label></label>
						<select id="appiappi-checkout-hosting-storage" data-checkout-hosting-storage></select>
					</div>
					<div class="form-row" data-checkout-hosting-traffic-row hidden>
						<label for="appiappi-checkout-hosting-traffic" data-checkout-hosting-traffic-label></label>
						<select id="appiappi-checkout-hosting-traffic" data-checkout-hosting-traffic></select>
					</div>
				</div>

				<div class="checkout-invoice">
					<div class="checkout-invoice__row" data-checkout-plan-row>
						<span><?php esc_html_e( 'Plan', 'appiappi-checkout' ); ?></span>
						<span data-checkout-plan-price></span>
					</div>
					<div class="checkout-invoice__row" data-checkout-design-row hidden>
						<span><?php esc_html_e( 'Website Design', 'appiappi-checkout' ); ?></span>
						<span data-checkout-design-price></span>
					</div>
					<div class="checkout-invoice__row checkout-invoice__row--credit" data-checkout-credit-row hidden>
						<span data-checkout-credit-label></span>
						<span data-checkout-credit-amount></span>
					</div>
					<div class="checkout-invoice__row" data-checkout-hosting-row hidden>
						<span data-checkout-hosting-label></span>
						<span data-checkout-hosting-price></span>
					</div>

					<div class="checkout-billing-toggle" data-checkout-billing-toggle hidden>
						<label>
							<input type="radio" name="appiappi_billing_frequency" value="monthly" checked>
							<span><?php esc_html_e( 'Monthly', 'appiappi-checkout' ); ?></span>
						</label>
						<label>
							<input type="radio" name="appiappi_billing_frequency" value="yearly">
							<span><?php esc_html_e( 'Annual', 'appiappi-checkout' ); ?></span>
							<span class="checkout-billing-toggle__badge" data-checkout-discount-badge></span>
						</label>
					</div>

					<div class="checkout-invoice__row checkout-invoice__row--total">
						<span><?php esc_html_e( 'Total due today', 'appiappi-checkout' ); ?></span>
						<span data-checkout-total></span>
					</div>
					<p class="checkout-invoice__recurring-note" data-checkout-recurring-note hidden></p>
					<p class="checkout-invoice__deferred-note" data-checkout-deferred-note hidden></p>
				</div>

				<form data-checkout-contact-form>
					<div class="form-row">
						<label for="appiappi-checkout-name"><?php esc_html_e( 'Full Name', 'appiappi-checkout' ); ?></label>
						<input type="text" id="appiappi-checkout-name" name="customer_name" required>
					</div>
					<div class="form-row">
						<label for="appiappi-checkout-email"><?php esc_html_e( 'Email', 'appiappi-checkout' ); ?></label>
						<input type="email" id="appiappi-checkout-email" name="customer_email" required>
					</div>
					<div class="form-row">
						<label for="appiappi-checkout-phone"><?php esc_html_e( 'Phone (optional)', 'appiappi-checkout' ); ?></label>
						<input type="tel" id="appiappi-checkout-phone" name="customer_phone">
					</div>
					<p class="checkout-error" data-checkout-error hidden></p>
					<button type="submit" class="btn btn-primary btn-block" data-checkout-continue>
						<?php esc_html_e( 'Continue to Payment', 'appiappi-checkout' ); ?>
					</button>
				</form>
			</div>

			<div class="checkout-step" data-checkout-step="payment" hidden>
				<h2 class="checkout-modal__title"><?php esc_html_e( 'Payment', 'appiappi-checkout' ); ?></h2>
				<div class="checkout-invoice checkout-invoice--summary">
					<span><?php esc_html_e( 'Total due today', 'appiappi-checkout' ); ?></span>
					<span data-checkout-total-repeat></span>
				</div>
				<div id="appiappi-payment-element"></div>
				<p class="checkout-error" data-checkout-payment-error hidden></p>
				<button type="button" class="btn btn-primary btn-block" data-checkout-pay>
					<?php esc_html_e( 'Pay Now', 'appiappi-checkout' ); ?>
				</button>
				<p class="checkout-secure-note"><?php esc_html_e( 'Payments are securely processed by Stripe. We never see or store your card details.', 'appiappi-checkout' ); ?></p>
			</div>

			<div class="checkout-step" data-checkout-step="status" hidden>
				<div class="checkout-status" data-checkout-status-body></div>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'appiappi_checkout_print_modal' );
