<?php
/**
 * Advanced/technical Site Settings page (Settings > Appiappi), per
 * MASTER_PROMPT.md § Site Settings. Deliberately separate from the
 * Customizer (inc/customizer.php): the Customizer holds *visual* brand
 * settings the owner previews live (colour, logo, contact info, social
 * links); this page holds *technical/back-office* settings (SEO
 * defaults, analytics IDs, tracking scripts) that don't need a live
 * preview and are easier to find grouped in one admin screen.
 *
 * Stored as a single option (`appiappi_settings`, an array) rather than
 * one option row per field — fewer autoloaded rows, and every reader
 * goes through appiappi_get_setting() so the storage shape can change
 * later without hunting down call sites.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_settings_fields() {
	return array(
		'seo_title'        => array( 'label' => __( 'Default SEO Title', 'appiappi' ), 'type' => 'text' ),
		'seo_description'  => array( 'label' => __( 'Default Meta Description', 'appiappi' ), 'type' => 'textarea' ),
		'ga_measurement_id'=> array( 'label' => __( 'Google Analytics Measurement ID', 'appiappi' ), 'type' => 'text', 'placeholder' => 'G-XXXXXXXXXX' ),
		'gsc_verification' => array( 'label' => __( 'Google Search Console Verification Code', 'appiappi' ), 'type' => 'text', 'description' => __( 'Just the content value of the verification meta tag, not the whole tag.', 'appiappi' ) ),
		'meta_pixel_id'    => array( 'label' => __( 'Meta (Facebook) Pixel ID', 'appiappi' ), 'type' => 'text' ),
		'business_hours'   => array( 'label' => __( 'Business Hours', 'appiappi' ), 'type' => 'textarea', 'placeholder' => "Mon–Fri: 9am–5pm\nSat–Sun: Closed" ),
		'currency'         => array( 'label' => __( 'Currency Symbol/Code', 'appiappi' ), 'type' => 'text', 'placeholder' => 'CAD $' ),
		'header_scripts'   => array( 'label' => __( 'Header Scripts', 'appiappi' ), 'type' => 'code', 'description' => __( 'Raw HTML/JS output just before &lt;/head&gt;. Only trusted admins should have access to this field — it executes exactly what you paste.', 'appiappi' ) ),
		'footer_scripts'   => array( 'label' => __( 'Footer Scripts', 'appiappi' ), 'type' => 'code', 'description' => __( 'Raw HTML/JS output just before &lt;/body&gt;.', 'appiappi' ) ),
	);
}

/**
 * Single read point for a settings value — swap the storage mechanism
 * later without touching every call site.
 */
function appiappi_get_setting( $key, $default = '' ) {
	$settings = get_option( 'appiappi_settings', array() );
	return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
}

function appiappi_settings_menu() {
	add_options_page(
		__( 'Appiappi Settings', 'appiappi' ),
		__( 'Appiappi Settings', 'appiappi' ),
		'manage_options',
		'appiappi-settings',
		'appiappi_settings_render_page'
	);
}
add_action( 'admin_menu', 'appiappi_settings_menu' );

function appiappi_settings_sanitize( $input ) {
	$fields = appiappi_settings_fields();
	$output = array();
	foreach ( $fields as $key => $field ) {
		if ( ! isset( $input[ $key ] ) ) {
			continue;
		}
		if ( 'code' === $field['type'] ) {
			// Intentionally NOT stripped/escaped — this field's whole purpose
			// is admin-supplied tracking/verification script markup. Access is
			// already gated to manage_options via the Settings API + nonce.
			$output[ $key ] = wp_unslash( $input[ $key ] );
		} elseif ( 'textarea' === $field['type'] ) {
			$output[ $key ] = sanitize_textarea_field( wp_unslash( $input[ $key ] ) );
		} else {
			$output[ $key ] = sanitize_text_field( wp_unslash( $input[ $key ] ) );
		}
	}
	return $output;
}

function appiappi_settings_register() {
	register_setting( 'appiappi_settings_group', 'appiappi_settings', array(
		'sanitize_callback' => 'appiappi_settings_sanitize',
		'default'           => array(),
	) );
}
add_action( 'admin_init', 'appiappi_settings_register' );

function appiappi_settings_render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Appiappi Settings', 'appiappi' ); ?></h1>
		<p><?php esc_html_e( 'Technical settings: SEO defaults, analytics, and tracking scripts. Brand colour, logo, contact info and social links are under Appearance > Customize instead.', 'appiappi' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'appiappi_settings_group' ); ?>
			<table class="form-table">
				<?php foreach ( appiappi_settings_fields() as $key => $field ) : ?>
					<tr>
						<th><label for="appiappi_settings_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
						<td>
							<?php $value = appiappi_get_setting( $key ); ?>
							<?php if ( 'textarea' === $field['type'] ) : ?>
								<textarea id="appiappi_settings_<?php echo esc_attr( $key ); ?>" name="appiappi_settings[<?php echo esc_attr( $key ); ?>]" rows="4" class="large-text" placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
							<?php elseif ( 'code' === $field['type'] ) : ?>
								<textarea id="appiappi_settings_<?php echo esc_attr( $key ); ?>" name="appiappi_settings[<?php echo esc_attr( $key ); ?>]" rows="5" class="large-text code"><?php echo esc_textarea( $value ); ?></textarea>
							<?php else : ?>
								<input type="text" id="appiappi_settings_<?php echo esc_attr( $key ); ?>" name="appiappi_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>">
							<?php endif; ?>
							<?php if ( ! empty( $field['description'] ) ) : ?>
								<p class="description"><?php echo wp_kses_post( $field['description'] ); ?></p>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
