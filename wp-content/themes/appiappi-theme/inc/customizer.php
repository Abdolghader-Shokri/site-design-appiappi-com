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

function appiappi_sanitize_checkbox( $value ) {
	return (bool) $value;
}

/**
 * Clamps a decimal range-control value (the network overlay's speed and
 * density multipliers, the title stroke width) to a sane [0.1, 5] window
 * so a stray/tampered value can never send the underlying calculation
 * somewhere absurd (zero, negative, a runaway node count, an unreadable
 * stroke).
 */
function appiappi_sanitize_decimal_multiplier( $value ) {
	$value = (float) $value;
	return max( 0.1, min( 5, $value ) );
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

	// ---- Page header backgrounds ----
	// Per page: an optional background image, an optional background
	// colour, and a toggle for the animated geometric network overlay
	// (assets/js/page-header-network.js — slowly drifting, multi-colour
	// connected nodes drawn on top of whichever image/colour is set).
	// All three are independent per page and all optional; leaving
	// everything empty/off shows no background image at all (revised
	// 2026-09-07 — no default illustration anymore), just
	// --color-bg-subtle, with no overlay.
	$wp_customize->add_section( 'appiappi_page_backgrounds', array(
		'title'       => __( 'Page Header Backgrounds', 'appiappi' ),
		'description' => __( 'Per page: optionally pick a background image and/or a background colour, and optionally switch on an animated geometric overlay. No image shows at all until you upload one — it isn\'t replacing a default illustration.', 'appiappi' ),
		'priority'    => 45,
	) );

	// Overlay behaviour (speed/density/animated connections) is shared by
	// every page's overlay rather than set per page — added 2026-09-07 at
	// the user's explicit request ("لازم نیست این سه درخواست برای هر
	// برگه جدا باشه"). Each page's own on/off toggle (further down) is
	// still independent; only *how* the overlay moves once it's on is global.
	$wp_customize->add_setting( 'appiappi_pagebg_network_speed', array(
		'default'           => 1,
		'sanitize_callback' => 'appiappi_sanitize_decimal_multiplier',
	) );
	$wp_customize->add_control( 'appiappi_pagebg_network_speed', array(
		'label'       => __( 'Overlay Dot Speed (all pages)', 'appiappi' ),
		'description' => __( 'How fast the dots drift. 1 = normal.', 'appiappi' ),
		'section'     => 'appiappi_page_backgrounds',
		'type'        => 'range',
		'input_attrs' => array( 'min' => 0.25, 'max' => 3, 'step' => 0.25 ),
		'priority'    => 1,
	) );

	$wp_customize->add_setting( 'appiappi_pagebg_network_density', array(
		'default'           => 1,
		'sanitize_callback' => 'appiappi_sanitize_decimal_multiplier',
	) );
	$wp_customize->add_control( 'appiappi_pagebg_network_density', array(
		'label'       => __( 'Overlay Dot Density (all pages)', 'appiappi' ),
		'description' => __( 'How many dots appear. 1 = normal.', 'appiappi' ),
		'section'     => 'appiappi_page_backgrounds',
		'type'        => 'range',
		'input_attrs' => array( 'min' => 0.3, 'max' => 2.5, 'step' => 0.1 ),
		'priority'    => 2,
	) );

	$wp_customize->add_setting( 'appiappi_pagebg_network_glow_lines', array(
		'default'           => false,
		'sanitize_callback' => 'appiappi_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'appiappi_pagebg_network_glow_lines', array(
		'label'       => __( 'Animate Connection Lines (all pages)', 'appiappi' ),
		'description' => __( 'Makes the lines between nearby dots pulse and glow instead of staying a flat, static line — a livelier, more "floating in space" feel.', 'appiappi' ),
		'section'     => 'appiappi_page_backgrounds',
		'type'        => 'checkbox',
		'priority'    => 3,
	) );

	$page_background_pages = array(
		'services'        => __( 'Services', 'appiappi' ),
		'how_it_works'    => __( 'How It Works', 'appiappi' ),
		'portfolio'       => __( 'Portfolio', 'appiappi' ),
		'pricing'         => __( 'Pricing', 'appiappi' ),
		'about'           => __( 'About', 'appiappi' ),
		'contact'         => __( 'Contact', 'appiappi' ),
		'templates'       => __( 'Website Designs', 'appiappi' ),
		'template_single' => __( 'Website Design — Single Design Page', 'appiappi' ),
	);

	foreach ( $page_background_pages as $key => $label ) {
		$wp_customize->add_setting( 'appiappi_pagebg_' . $key, array(
			'default'           => '',
			'sanitize_callback' => 'esc_url_raw',
		) );
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'appiappi_pagebg_' . $key, array(
			/* translators: %s: page name */
			'label'   => sprintf( __( '%s — Background Image', 'appiappi' ), $label ),
			'section' => 'appiappi_page_backgrounds',
		) ) );

		$wp_customize->add_setting( 'appiappi_pagebg_color_' . $key, array(
			'default'           => '',
			'sanitize_callback' => 'sanitize_hex_color',
		) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'appiappi_pagebg_color_' . $key, array(
			/* translators: %s: page name */
			'label'   => sprintf( __( '%s — Background Colour', 'appiappi' ), $label ),
			'section' => 'appiappi_page_backgrounds',
		) ) );

		$wp_customize->add_setting( 'appiappi_pagebg_animated_' . $key, array(
			'default'           => false,
			'sanitize_callback' => 'appiappi_sanitize_checkbox',
		) );
		$wp_customize->add_control( 'appiappi_pagebg_animated_' . $key, array(
			/* translators: %s: page name */
			'label'   => sprintf( __( '%s — Animated Geometric Overlay', 'appiappi' ), $label ),
			'section' => 'appiappi_page_backgrounds',
			'type'    => 'checkbox',
		) );
	}

	// ---- Page title styling ----
	// The H1 (+ optional subtitle) inside every one of the same 8 page
	// headers above — one shared style, not per page (added 2026-09-07,
	// same "no need for this to be per page" preference as the overlay
	// speed/density controls). Text colour and the stroke/background box
	// are all independently optional: colour falls back to the theme's
	// existing dark heading colour, the stroke only ever renders once its
	// own toggle is on (so switching it off doesn't lose the saved colour/
	// width), and the background box only appears once a colour is chosen
	// for it — no box, no shadow, until then.
	$wp_customize->add_section( 'appiappi_page_title_style', array(
		'title'       => __( 'Page Title Styling', 'appiappi' ),
		'description' => __( 'Applies to the H1 (and subtitle) on every page header above — one shared style, not set per page.', 'appiappi' ),
		'priority'    => 45,
	) );

	$wp_customize->add_setting( 'appiappi_pagetitle_color', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'appiappi_pagetitle_color', array(
		'label'       => __( 'Title Text Colour', 'appiappi' ),
		'description' => __( 'Leave unset to keep the theme\'s default heading colour.', 'appiappi' ),
		'section'     => 'appiappi_page_title_style',
		'priority'    => 1,
	) ) );

	$wp_customize->add_setting( 'appiappi_pagetitle_stroke_enabled', array(
		'default'           => false,
		'sanitize_callback' => 'appiappi_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'appiappi_pagetitle_stroke_enabled', array(
		'label'    => __( 'Enable Text Stroke (Outline)', 'appiappi' ),
		'section'  => 'appiappi_page_title_style',
		'type'     => 'checkbox',
		'priority' => 2,
	) );

	$wp_customize->add_setting( 'appiappi_pagetitle_stroke_color', array(
		'default'           => '#000000',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'appiappi_pagetitle_stroke_color', array(
		'label'    => __( 'Stroke Colour', 'appiappi' ),
		'section'  => 'appiappi_page_title_style',
		'priority' => 3,
	) ) );

	$wp_customize->add_setting( 'appiappi_pagetitle_stroke_width', array(
		'default'           => 1.5,
		'sanitize_callback' => 'appiappi_sanitize_decimal_multiplier',
	) );
	$wp_customize->add_control( 'appiappi_pagetitle_stroke_width', array(
		'label'       => __( 'Stroke Width (px)', 'appiappi' ),
		'section'     => 'appiappi_page_title_style',
		'type'        => 'range',
		'input_attrs' => array( 'min' => 0.5, 'max' => 5, 'step' => 0.5 ),
		'priority'    => 4,
	) );

	$wp_customize->add_setting( 'appiappi_pagetitle_bg_color', array(
		'default'           => '',
		'sanitize_callback' => 'sanitize_hex_color',
	) );
	$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'appiappi_pagetitle_bg_color', array(
		'label'       => __( 'Title Background Box Colour', 'appiappi' ),
		'description' => __( 'A rounded, shadowed box behind the title (and subtitle). Leave unset to show no box at all.', 'appiappi' ),
		'section'     => 'appiappi_page_title_style',
		'priority'    => 5,
	) ) );

	$wp_customize->add_setting( 'appiappi_pagetitle_bg_opacity', array(
		'default'           => 70,
		'sanitize_callback' => 'absint',
	) );
	$wp_customize->add_control( 'appiappi_pagetitle_bg_opacity', array(
		'label'       => __( 'Title Background Opacity (%)', 'appiappi' ),
		'description' => __( 'Only matters once a background colour is set above — 0 is fully see-through, 100 is fully solid.', 'appiappi' ),
		'section'     => 'appiappi_page_title_style',
		'type'        => 'range',
		'input_attrs' => array( 'min' => 0, 'max' => 100, 'step' => 5 ),
		'priority'    => 6,
	) );

	// ---- Website Design — single page ----
	// The "can't find your design? contact us" note shown in the summary
	// box on every design's single page (single-appiappi_template.php) —
	// one fixed, sitewide note rather than per-design, since it's the
	// same message regardless of which design a visitor is looking at.
	$wp_customize->add_section( 'appiappi_template_single_settings', array(
		'title'    => __( 'Website Design — Single Page', 'appiappi' ),
		'priority' => 47,
	) );

	$wp_customize->add_setting( 'appiappi_template_missing_note', array(
		'default'           => appiappi_default_missing_design_note(),
		'sanitize_callback' => 'wp_kses_post',
	) );
	$wp_customize->add_control( 'appiappi_template_missing_note', array(
		'label'       => __( 'Missing-Design Note', 'appiappi' ),
		'description' => __( 'Shown in the summary box on every design\'s single page. Basic HTML (like a link) is allowed. Leave empty to hide it entirely.', 'appiappi' ),
		'section'     => 'appiappi_template_single_settings',
		'type'        => 'textarea',
	) );

	// ---- Footer ----
	$wp_customize->add_section( 'appiappi_footer', array(
		'title'    => __( 'Footer', 'appiappi' ),
		'priority' => 46,
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

	// Only output a --page-header-bg-{key}/-color-{key} line for pages
	// where the admin actually set something — pages.css's own var()
	// fallbacks (the theme's default SVG, --color-bg-subtle) already
	// handle the rest, so there's nothing to override for those.
	$page_background_keys = array( 'services', 'how_it_works', 'portfolio', 'pricing', 'about', 'contact', 'templates', 'template_single' );
	$page_background_image_overrides = array();
	$page_background_color_overrides = array();
	foreach ( $page_background_keys as $key ) {
		$css_key = str_replace( '_', '-', $key );

		$url = get_theme_mod( 'appiappi_pagebg_' . $key, '' );
		if ( $url ) {
			$page_background_image_overrides[ $css_key ] = $url;
		}

		$color = get_theme_mod( 'appiappi_pagebg_color_' . $key, '' );
		if ( $color ) {
			$page_background_color_overrides[ $css_key ] = $color;
		}
	}

	$title_color        = get_theme_mod( 'appiappi_pagetitle_color', '' );
	$title_stroke_color = get_theme_mod( 'appiappi_pagetitle_stroke_color', '#000000' );
	$title_stroke_width = (float) get_theme_mod( 'appiappi_pagetitle_stroke_width', 1.5 );
	$title_bg_color     = get_theme_mod( 'appiappi_pagetitle_bg_color', '' );
	$title_bg_opacity   = (int) get_theme_mod( 'appiappi_pagetitle_bg_opacity', 70 );
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
			<?php foreach ( $page_background_image_overrides as $css_key => $url ) : ?>
			--page-header-bg-<?php echo esc_html( $css_key ); ?>: url('<?php echo esc_url( $url ); ?>');
			<?php endforeach; ?>
			<?php foreach ( $page_background_color_overrides as $css_key => $color ) : ?>
			--page-header-bg-color-<?php echo esc_html( $css_key ); ?>: <?php echo esc_html( $color ); ?>;
			<?php endforeach; ?>
			<?php if ( $title_color ) : ?>
			--page-title-color: <?php echo esc_html( $title_color ); ?>;
			<?php endif; ?>
			--page-title-stroke-color: <?php echo esc_html( $title_stroke_color ); ?>;
			--page-title-stroke-width: <?php echo esc_html( $title_stroke_width ); ?>px;
			<?php if ( $title_bg_color ) : ?>
			--page-title-bg-color: <?php echo esc_html( $title_bg_color ); ?>;
			<?php endif; ?>
			--page-title-bg-opacity: <?php echo esc_html( $title_bg_opacity ); ?>%;
		}
	</style>
	<?php
}
add_action( 'wp_head', 'appiappi_customizer_css_vars', 5 );
