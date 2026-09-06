<?php
/**
 * Global, admin-editable site settings via the native Customizer:
 * brand colour, contact info, social links, header CTA, footer text.
 * Logo/favicon use core's built-in custom-logo + Site Icon controls.
 *
 * Advanced settings (Analytics ID, GTM, tracking scripts, currency,
 * pricing display) belong in a dedicated Settings page — see
 * PROJECT_MASTER.md, Phase 4.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_contact_sanitize_phone_type( $value ) {
	$allowed = array( 'call', 'sms', 'whatsapp', 'none' );
	return in_array( $value, $allowed, true ) ? $value : 'call';
}

function appiappi_customize_register( $wp_customize ) {

	// ---- Brand colour ----
	$wp_customize->add_section( 'appiappi_brand', array(
		'title'    => __( 'Brand Colour', 'appiappi' ),
		'priority' => 25,
	) );

	$wp_customize->add_setting( 'appiappi_color_primary', array(
		'default'           => '#1e5eff',
		'sanitize_callback' => 'sanitize_hex_color',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'appiappi_color_primary', array(
		'label'   => __( 'Primary Colour', 'appiappi' ),
		'section' => 'appiappi_brand',
	) ) );

	// ---- Header CTA ----
	$wp_customize->add_section( 'appiappi_header_cta', array(
		'title'    => __( 'Header Call to Action', 'appiappi' ),
		'priority' => 30,
	) );

	$wp_customize->add_setting( 'appiappi_cta_text', array(
		'default'           => __( 'Get Started', 'appiappi' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'appiappi_cta_text', array(
		'label'   => __( 'Button Text', 'appiappi' ),
		'section' => 'appiappi_header_cta',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'appiappi_cta_url', array(
		'default'           => '#pricing',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( 'appiappi_cta_url', array(
		'label'   => __( 'Button URL', 'appiappi' ),
		'section' => 'appiappi_header_cta',
		'type'    => 'url',
	) );

	// ---- Contact info ----
	$wp_customize->add_section( 'appiappi_contact', array(
		'title'    => __( 'Contact Information', 'appiappi' ),
		'priority' => 35,
	) );

	$contact_fields = array(
		'appiappi_phone'   => __( 'Phone Number', 'appiappi' ),
		'appiappi_email'   => __( 'Email Address', 'appiappi' ),
		'appiappi_address' => __( 'Address (City, Province, Country)', 'appiappi' ),
	);
	foreach ( $contact_fields as $id => $label ) {
		$wp_customize->add_setting( $id, array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( $id, array(
			'label'   => $label,
			'section' => 'appiappi_contact',
			'type'    => 'text',
		) );
	}

	// ---- Contact page info box ----
	// Deliberately separate from "Contact Information" above (footer +
	// schema.org LocalBusiness data, § inc/seo.php): this section only
	// drives the info card next to the form on the Contact page, per the
	// user's brief — map on top, address/phone/support email below, each
	// independently optional, the whole card hidden if all are empty.
	$wp_customize->add_section( 'appiappi_contact_page_box', array(
		'title'       => __( 'Contact Page Info Box', 'appiappi' ),
		'description' => __( 'The info box shown beside the form on the Contact page. Leave everything below empty to hide the box entirely and show just the form.', 'appiappi' ),
		'priority'    => 36,
	) );

	$wp_customize->add_setting( 'appiappi_contact_map_embed', array(
		'default'           => '',
		'sanitize_callback' => 'esc_url_raw',
	) );
	$wp_customize->add_control( 'appiappi_contact_map_embed', array(
		'label'       => __( 'Google Maps Embed URL', 'appiappi' ),
		'description' => __( 'In Google Maps: Share → Embed a map, then copy just the URL inside the src="..." attribute of the code it gives you.', 'appiappi' ),
		'section'     => 'appiappi_contact_page_box',
		'type'        => 'url',
	) );

	$wp_customize->add_setting( 'appiappi_contact_address_label', array(
		'default'           => __( 'Address', 'appiappi' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'appiappi_contact_address_label', array(
		'label'   => __( 'Address Label', 'appiappi' ),
		'section' => 'appiappi_contact_page_box',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'appiappi_contact_address_value', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'appiappi_contact_address_value', array(
		'label'   => __( 'Address', 'appiappi' ),
		'section' => 'appiappi_contact_page_box',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'appiappi_contact_phone_label', array(
		'default'           => __( 'Phone', 'appiappi' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'appiappi_contact_phone_label', array(
		'label'       => __( 'Phone Label', 'appiappi' ),
		'description' => __( 'Whatever this number actually is — e.g. "Phone", "Fax", or "WhatsApp Support".', 'appiappi' ),
		'section'     => 'appiappi_contact_page_box',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'appiappi_contact_phone_value', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'appiappi_contact_phone_value', array(
		'label'   => __( 'Phone Number', 'appiappi' ),
		'section' => 'appiappi_contact_page_box',
		'type'    => 'text',
	) );

	$wp_customize->add_setting( 'appiappi_contact_phone_type', array(
		'default'           => 'call',
		'sanitize_callback' => 'appiappi_contact_sanitize_phone_type',
	) );
	$wp_customize->add_control( 'appiappi_contact_phone_type', array(
		'label'   => __( 'Phone Number Links To', 'appiappi' ),
		'section' => 'appiappi_contact_page_box',
		'type'    => 'select',
		'choices' => array(
			'call'     => __( 'Phone Call', 'appiappi' ),
			'sms'      => __( 'Text Message (SMS)', 'appiappi' ),
			'whatsapp' => __( 'WhatsApp', 'appiappi' ),
			'none'     => __( 'None — just show the number', 'appiappi' ),
		),
	) );

	$wp_customize->add_setting( 'appiappi_contact_support_email', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_email',
	) );
	$wp_customize->add_control( 'appiappi_contact_support_email', array(
		'label'   => __( 'Support Email', 'appiappi' ),
		'section' => 'appiappi_contact_page_box',
		'type'    => 'email',
	) );

	// ---- Social links ----
	$wp_customize->add_section( 'appiappi_social', array(
		'title'    => __( 'Social Links', 'appiappi' ),
		'priority' => 40,
	) );

	$social_fields = array(
		'appiappi_social_facebook'  => 'Facebook',
		'appiappi_social_linkedin'  => 'LinkedIn',
		'appiappi_social_instagram' => 'Instagram',
		'appiappi_social_youtube'   => 'YouTube',
	);
	foreach ( $social_fields as $id => $label ) {
		$wp_customize->add_setting( $id, array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		) );
		$wp_customize->add_control( $id, array(
			'label'   => $label,
			'section' => 'appiappi_social',
			'type'    => 'url',
		) );
	}

	// ---- Layout spacing ----
	// Desktop-only side padding for specific sections that need more
	// breathing room than the sitewide --container-pad (10px, tight by
	// design to match themeforest.net's edge-to-edge look) — the
	// homepage's Hero, Pricing preview and Website Designs preview, plus
	// the Final CTA + site footer (treated as one closing "footer area").
	// Mobile is untouched; each falls back to the default in tokens.css
	// if never touched here.
	$wp_customize->add_section( 'appiappi_layout_spacing', array(
		'title'       => __( 'Layout Spacing', 'appiappi' ),
		'description' => __( 'Desktop-only side padding for specific sections (px). Mobile spacing is unaffected.', 'appiappi' ),
		'priority'    => 44,
	) );

	$spacing_fields = array(
		'appiappi_hero_pad'             => array( __( 'Hero Side Padding', 'appiappi' ), 30 ),
		'appiappi_pricing_preview_pad'  => array( __( 'Pricing Preview Side Padding', 'appiappi' ), 20 ),
		'appiappi_templates_preview_pad'=> array( __( 'Website Designs Preview Side Padding', 'appiappi' ), 20 ),
		'appiappi_footer_pad'           => array( __( 'Footer Side Padding', 'appiappi' ), 50 ),
	);
	foreach ( $spacing_fields as $id => $field ) {
		list( $label, $default ) = $field;
		$wp_customize->add_setting( $id, array(
			'default'           => $default,
			'sanitize_callback' => 'absint',
		) );
		$wp_customize->add_control( $id, array(
			'label'       => $label,
			'section'     => 'appiappi_layout_spacing',
			'type'        => 'range',
			'input_attrs' => array( 'min' => 0, 'max' => 120, 'step' => 1 ),
		) );
	}

	// ---- Footer ----
	$wp_customize->add_section( 'appiappi_footer', array(
		'title'    => __( 'Footer', 'appiappi' ),
		'priority' => 45,
	) );

	$wp_customize->add_setting( 'appiappi_footer_tagline', array(
		'default'           => __( 'We build, host and grow websites for Canadian businesses.', 'appiappi' ),
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'appiappi_footer_tagline', array(
		'label'   => __( 'Footer Tagline', 'appiappi' ),
		'section' => 'appiappi_footer',
		'type'    => 'text',
	) );
}
add_action( 'customize_register', 'appiappi_customize_register' );

/**
 * Push the admin-chosen primary colour into CSS as a token override.
 * Derives a darker hover shade and a light tint automatically so the
 * business owner only has to pick one colour.
 */
function appiappi_customizer_css_vars() {
	$primary = get_theme_mod( 'appiappi_color_primary', '#1e5eff' );

	$hero_pad             = (int) get_theme_mod( 'appiappi_hero_pad', 30 );
	$pricing_preview_pad  = (int) get_theme_mod( 'appiappi_pricing_preview_pad', 20 );
	$templates_preview_pad = (int) get_theme_mod( 'appiappi_templates_preview_pad', 20 );
	$footer_pad           = (int) get_theme_mod( 'appiappi_footer_pad', 50 );
	?>
	<style id="appiappi-customizer-vars">
		:root {
			--color-primary: <?php echo esc_html( $primary ); ?>;
			--color-primary-dark: color-mix(in srgb, <?php echo esc_html( $primary ); ?> 80%, black);
			--color-primary-50: color-mix(in srgb, <?php echo esc_html( $primary ); ?> 8%, white);
			--hero-pad-desktop: <?php echo esc_html( $hero_pad ); ?>px;
			--pricing-preview-pad-desktop: <?php echo esc_html( $pricing_preview_pad ); ?>px;
			--templates-preview-pad-desktop: <?php echo esc_html( $templates_preview_pad ); ?>px;
			--footer-pad-desktop: <?php echo esc_html( $footer_pad ); ?>px;
		}
	</style>
	<?php
}
add_action( 'wp_head', 'appiappi_customizer_css_vars', 5 );
