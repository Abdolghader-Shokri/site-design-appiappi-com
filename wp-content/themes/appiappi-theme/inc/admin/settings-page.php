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
		'seo_title'        => array( 'label' => __( 'Default SEO Title', 'appiappi' ), 'type' => 'text', 'group' => 'seo' ),
		'seo_description'  => array( 'label' => __( 'Default Meta Description', 'appiappi' ), 'type' => 'textarea', 'group' => 'seo' ),
		'ga_measurement_id'=> array( 'label' => __( 'Google Analytics Measurement ID', 'appiappi' ), 'type' => 'text', 'placeholder' => 'G-XXXXXXXXXX', 'group' => 'seo' ),
		'gsc_verification' => array( 'label' => __( 'Google Search Console Verification Code', 'appiappi' ), 'type' => 'text', 'description' => __( 'Just the content value of the verification meta tag, not the whole tag.', 'appiappi' ), 'group' => 'seo' ),
		'meta_pixel_id'    => array( 'label' => __( 'Meta (Facebook) Pixel ID', 'appiappi' ), 'type' => 'text', 'group' => 'seo' ),
		'business_hours'   => array( 'label' => __( 'Business Hours', 'appiappi' ), 'type' => 'textarea', 'placeholder' => "Mon–Fri: 9am–5pm\nSat–Sun: Closed", 'group' => 'seo' ),
		'currency'         => array( 'label' => __( 'Currency Symbol/Code', 'appiappi' ), 'type' => 'text', 'placeholder' => 'CAD $', 'group' => 'seo' ),
		'header_scripts'   => array( 'label' => __( 'Header Scripts', 'appiappi' ), 'type' => 'code', 'description' => __( 'Raw HTML/JS output just before &lt;/head&gt;. Only trusted admins should have access to this field — it executes exactly what you paste.', 'appiappi' ), 'group' => 'seo' ),
		'footer_scripts'   => array( 'label' => __( 'Footer Scripts', 'appiappi' ), 'type' => 'code', 'description' => __( 'Raw HTML/JS output just before &lt;/body&gt;.', 'appiappi' ), 'group' => 'seo' ),

		// ---- Legal & Company Information ----
		// Read by page-privacy-policy.php and page-terms.php (via
		// appiappi_get_setting()) to fill in the legal specifics those
		// pages need — nothing here is guessed or hard-coded, so a field
		// left empty simply omits the sentence/row that depends on it
		// rather than showing a placeholder in a published legal page.
		'company_legal_name' => array( 'label' => __( 'Full Legal Company Name', 'appiappi' ), 'type' => 'text', 'group' => 'legal' ),
		'incorporation_province' => array(
			'label'   => __( 'Province/Territory of Incorporation', 'appiappi' ),
			'type'    => 'select',
			'group'   => 'legal',
			'choices' => array(
				''                          => __( '— Not set —', 'appiappi' ),
				'Alberta'                   => 'Alberta',
				'British Columbia'          => 'British Columbia',
				'Manitoba'                  => 'Manitoba',
				'New Brunswick'             => 'New Brunswick',
				'Newfoundland and Labrador' => 'Newfoundland and Labrador',
				'Nova Scotia'               => 'Nova Scotia',
				'Ontario'                   => 'Ontario',
				'Prince Edward Island'      => 'Prince Edward Island',
				'Quebec'                    => 'Quebec',
				'Saskatchewan'              => 'Saskatchewan',
				'Northwest Territories'     => 'Northwest Territories',
				'Nunavut'                   => 'Nunavut',
				'Yukon'                     => 'Yukon',
			),
		),
		'company_address'   => array( 'label' => __( 'Official Business Address', 'appiappi' ), 'type' => 'textarea', 'group' => 'legal', 'description' => __( 'Used on the Privacy Policy and Terms of Service pages.', 'appiappi' ) ),
		'general_email'      => array( 'label' => __( 'General Public Email', 'appiappi' ), 'type' => 'text', 'placeholder' => 'hello@appiappi.com', 'group' => 'legal' ),
		'privacy_email'      => array( 'label' => __( 'Privacy Email', 'appiappi' ), 'type' => 'text', 'placeholder' => 'privacy@appiappi.com', 'group' => 'legal' ),
		'privacy_officer_name' => array( 'label' => __( 'Privacy Officer Name', 'appiappi' ), 'type' => 'text', 'group' => 'legal' ),
		'payment_method'     => array( 'label' => __( 'Payment Method & Payment Provider', 'appiappi' ), 'type' => 'text', 'placeholder' => __( 'e.g. Credit card, processed via Stripe', 'appiappi' ), 'group' => 'legal' ),
		'cancellation_policy' => array( 'label' => __( 'Exact Cancellation Policy', 'appiappi' ), 'type' => 'textarea', 'description' => __( 'The precise rules for cancelling a monthly plan. Shown as-is on the Terms of Service page; leave empty to keep the generic default wording.', 'appiappi' ), 'group' => 'legal' ),
		'support_response_time' => array( 'label' => __( 'Support Response Time', 'appiappi' ), 'type' => 'text', 'placeholder' => __( 'e.g. within 1 business day', 'appiappi' ), 'group' => 'legal' ),
		'data_retention_period' => array( 'label' => __( 'Data Retention Period', 'appiappi' ), 'type' => 'text', 'placeholder' => __( 'e.g. for the duration of the client relationship, plus 3 years', 'appiappi' ), 'group' => 'legal' ),
		'portfolio_display_default' => array(
			'label'   => __( 'Client Portfolio Display Policy', 'appiappi' ),
			'type'    => 'select',
			'group'   => 'legal',
			'choices' => array(
				''             => __( '— Not set (use generic wording) —', 'appiappi' ),
				'default_yes'  => __( 'Yes — we may showcase completed projects unless the client opts out', 'appiappi' ),
				'default_no'   => __( 'No — only with the client\'s written permission', 'appiappi' ),
			),
		),
		'ownership_details' => array( 'label' => __( 'Final Ownership Details (Site, Templates, Plugins, Generated Content)', 'appiappi' ), 'type' => 'textarea', 'description' => __( 'Project-specific ownership clarifications, added to the standard Intellectual Property terms on the Terms of Service page.', 'appiappi' ), 'group' => 'legal' ),
	);
}

function appiappi_settings_groups() {
	return array(
		'seo'   => __( 'SEO, Analytics & Technical', 'appiappi' ),
		'legal' => __( 'Legal & Company Information', 'appiappi' ),
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
		} elseif ( 'select' === $field['type'] ) {
			$value = sanitize_text_field( wp_unslash( $input[ $key ] ) );
			$output[ $key ] = array_key_exists( $value, $field['choices'] ?? array() ) ? $value : '';
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
			<?php
			$groups       = appiappi_settings_groups();
			$fields       = appiappi_settings_fields();
			$current_group = null;
			foreach ( $fields as $key => $field ) :
				$field_group = $field['group'] ?? '';
				if ( $field_group !== $current_group ) :
					if ( null !== $current_group ) :
						?>
						</table>
						<?php
					endif;
					$current_group = $field_group;
					?>
					<h2><?php echo esc_html( $groups[ $current_group ] ?? '' ); ?></h2>
					<table class="form-table">
					<?php
				endif;
				?>
				<tr>
					<th><label for="appiappi_settings_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
					<td>
						<?php $value = appiappi_get_setting( $key ); ?>
						<?php if ( 'textarea' === $field['type'] ) : ?>
							<textarea id="appiappi_settings_<?php echo esc_attr( $key ); ?>" name="appiappi_settings[<?php echo esc_attr( $key ); ?>]" rows="4" class="large-text" placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>"><?php echo esc_textarea( $value ); ?></textarea>
						<?php elseif ( 'code' === $field['type'] ) : ?>
							<textarea id="appiappi_settings_<?php echo esc_attr( $key ); ?>" name="appiappi_settings[<?php echo esc_attr( $key ); ?>]" rows="5" class="large-text code"><?php echo esc_textarea( $value ); ?></textarea>
						<?php elseif ( 'select' === $field['type'] ) : ?>
							<select id="appiappi_settings_<?php echo esc_attr( $key ); ?>" name="appiappi_settings[<?php echo esc_attr( $key ); ?>]">
								<?php foreach ( $field['choices'] as $choice_value => $choice_label ) : ?>
									<option value="<?php echo esc_attr( $choice_value ); ?>" <?php selected( $value, $choice_value ); ?>><?php echo esc_html( $choice_label ); ?></option>
								<?php endforeach; ?>
							</select>
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
