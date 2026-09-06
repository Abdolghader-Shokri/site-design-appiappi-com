<?php
/**
 * Settings → Appiappi Checkout: the only things that should ever need
 * touching after moving this site to real hosting. Stripe test/live mode
 * is read directly off the key prefix (pk_test_/pk_live_, sk_test_/sk_live_)
 * rather than a separate toggle, so pasting live keys here is the entire
 * "go live" step — nothing else in this plugin needs editing.
 *
 * The webhook URL is a REST route (appiappi_checkout_webhook_url()) built
 * from rest_url(), so it's correct on any domain automatically; the only
 * manual step is pasting that URL into the Stripe Dashboard's webhook
 * config once (Stripe has no way to discover it on its own — that's a
 * one-time setup step on Stripe's side, not something code can automate),
 * then pasting the signing secret Stripe gives back into the field below.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_checkout_get_setting( $key, $default = '' ) {
	$settings = get_option( 'appiappi_checkout_settings', array() );
	return isset( $settings[ $key ] ) && '' !== $settings[ $key ] ? $settings[ $key ] : $default;
}

/**
 * 'live', 'test', or '' (not configured yet) — read from the secret key's
 * own prefix rather than a separate admin toggle, so there's exactly one
 * place ("which keys are pasted in") that decides the mode.
 */
function appiappi_checkout_mode() {
	$secret = appiappi_checkout_get_setting( 'stripe_secret_key' );
	if ( 0 === strpos( $secret, 'sk_live_' ) || 0 === strpos( $secret, 'rk_live_' ) ) {
		return 'live';
	}
	if ( 0 === strpos( $secret, 'sk_test_' ) || 0 === strpos( $secret, 'rk_test_' ) ) {
		return 'test';
	}
	return '';
}

function appiappi_checkout_is_configured() {
	return (bool) appiappi_checkout_get_setting( 'stripe_secret_key' ) && (bool) appiappi_checkout_get_setting( 'stripe_publishable_key' );
}

/**
 * ISO 4217 lowercase currency code Stripe expects — deliberately fixed
 * to USD, not the theme's general "Currency Symbol/Code" setting
 * (Settings → Appiappi Settings, defaults to CAD for on-page display
 * elsewhere on the site). Decided 2026-09-06: Website Design prices
 * come from Envato/ThemeForest, which always lists in USD regardless of
 * buyer location — mixing those with CAD-labelled plan prices as if
 * they were the same unit was a real bug (a $1 USD design line simply
 * isn't $1 CAD). Rather than run a live exchange-rate conversion or make
 * the buyer choose a currency mid-checkout, the whole checkout — plan
 * prices and design prices alike — charges in USD; a Canadian
 * cardholder's own bank/card network converts to CAD automatically at
 * settlement, exactly as it already does for any other US-priced
 * purchase. If this ever needs to change, this is the one function that
 * decides it.
 */
function appiappi_checkout_currency() {
	return 'usd';
}

function appiappi_checkout_webhook_url() {
	return rest_url( 'appiappi/v1/stripe-webhook' );
}

function appiappi_checkout_settings_fields() {
	return array(
		'stripe_publishable_key' => array(
			'label'       => __( 'Stripe Publishable Key', 'appiappi-checkout' ),
			'type'        => 'text',
			'placeholder' => 'pk_live_… or pk_test_…',
		),
		'stripe_secret_key'      => array(
			'label'       => __( 'Stripe Secret Key', 'appiappi-checkout' ),
			'type'        => 'password',
			'placeholder' => 'sk_live_… or sk_test_…',
			'description' => __( 'From your Stripe Dashboard → Developers → API keys. Whether checkout runs in test or live mode is decided automatically by this key\'s own prefix — nothing else to configure.', 'appiappi-checkout' ),
		),
		'stripe_webhook_secret'  => array(
			'label'       => __( 'Stripe Webhook Signing Secret', 'appiappi-checkout' ),
			'type'        => 'password',
			'placeholder' => 'whsec_…',
			'description' => __( 'From the webhook endpoint you create in Stripe Dashboard → Developers → Webhooks, using the URL shown below. Required for orders to be marked as paid.', 'appiappi-checkout' ),
		),
		'annual_discount_percent' => array(
			'label'       => __( 'Annual Billing Discount (%)', 'appiappi-checkout' ),
			'type'        => 'number',
			'placeholder' => '5',
			'description' => __( 'Applied when a customer switches a monthly plan to annual billing at checkout.', 'appiappi-checkout' ),
		),
	);
}

function appiappi_checkout_settings_sanitize( $input ) {
	$fields = appiappi_checkout_settings_fields();
	$output = array();
	foreach ( $fields as $key => $field ) {
		if ( ! isset( $input[ $key ] ) ) {
			continue;
		}
		if ( 'number' === $field['type'] ) {
			$output[ $key ] = max( 0, min( 100, (float) $input[ $key ] ) );
		} else {
			$output[ $key ] = sanitize_text_field( wp_unslash( $input[ $key ] ) );
		}
	}
	return $output;
}

function appiappi_checkout_settings_register() {
	register_setting( 'appiappi_checkout_settings_group', 'appiappi_checkout_settings', array(
		'sanitize_callback' => 'appiappi_checkout_settings_sanitize',
		'default'           => array(),
	) );
}
add_action( 'admin_init', 'appiappi_checkout_settings_register' );

function appiappi_checkout_settings_menu() {
	add_submenu_page(
		'edit.php?post_type=appiappi_order',
		__( 'Checkout Settings', 'appiappi-checkout' ),
		__( 'Settings', 'appiappi-checkout' ),
		'manage_options',
		'appiappi-checkout-settings',
		'appiappi_checkout_render_settings_page'
	);
}
add_action( 'admin_menu', 'appiappi_checkout_settings_menu' );

function appiappi_checkout_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$mode = appiappi_checkout_mode();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Appiappi Checkout — Settings', 'appiappi-checkout' ); ?></h1>
		<p><?php esc_html_e( 'Everything Stripe-related lives here. Moving the site to real hosting later should only ever require updating the keys below — nothing else in the checkout flow needs to change.', 'appiappi-checkout' ); ?></p>

		<?php if ( ! $mode ) : ?>
			<div class="notice notice-warning"><p><?php esc_html_e( 'No Stripe Secret Key set yet — the checkout button will show a friendly "payments aren\'t set up yet" message instead of a payment form until one is added.', 'appiappi-checkout' ); ?></p></div>
		<?php elseif ( 'test' === $mode ) : ?>
			<div class="notice notice-info"><p><?php esc_html_e( 'Checkout is running in TEST mode (your secret key starts with sk_test_). No real charges will be made. Switch to sk_live_/pk_live_ keys when you\'re ready to accept real payments.', 'appiappi-checkout' ); ?></p></div>
		<?php else : ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'Checkout is running in LIVE mode — real payments will be charged.', 'appiappi-checkout' ); ?></p></div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'appiappi_checkout_settings_group' ); ?>
			<table class="form-table">
				<?php foreach ( appiappi_checkout_settings_fields() as $key => $field ) : ?>
					<tr>
						<th><label for="appiappi_checkout_settings_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
						<td>
							<?php $value = appiappi_checkout_get_setting( $key, 'annual_discount_percent' === $key ? 5 : '' ); ?>
							<input type="<?php echo esc_attr( $field['type'] ); ?>" id="appiappi_checkout_settings_<?php echo esc_attr( $key ); ?>" name="appiappi_checkout_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>" autocomplete="off">
							<?php if ( ! empty( $field['description'] ) ) : ?>
								<p class="description"><?php echo wp_kses_post( $field['description'] ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
				<tr>
					<th><?php esc_html_e( 'Webhook Endpoint URL', 'appiappi-checkout' ); ?></th>
					<td>
						<input type="text" readonly value="<?php echo esc_attr( appiappi_checkout_webhook_url() ); ?>" class="regular-text code" onclick="this.select()">
						<p class="description"><?php esc_html_e( 'Paste this into Stripe Dashboard → Developers → Webhooks → Add endpoint. Listen for: payment_intent.succeeded, payment_intent.payment_failed, invoice.paid, invoice.payment_failed. Stripe will give you a signing secret — paste that into the field above.', 'appiappi-checkout' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
