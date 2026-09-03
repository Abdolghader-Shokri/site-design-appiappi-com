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
	?>
	<style id="appiappi-customizer-vars">
		:root {
			--color-primary: <?php echo esc_html( $primary ); ?>;
			--color-primary-dark: color-mix(in srgb, <?php echo esc_html( $primary ); ?> 80%, black);
			--color-primary-50: color-mix(in srgb, <?php echo esc_html( $primary ); ?> 8%, white);
		}
	</style>
	<?php
}
add_action( 'wp_head', 'appiappi_customizer_css_vars', 5 );
