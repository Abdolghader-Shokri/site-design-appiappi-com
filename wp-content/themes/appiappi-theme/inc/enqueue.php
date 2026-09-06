<?php
/**
 * Styles & scripts. Ordered explicitly so tokens load before anything
 * that references them. Bump APPIAPPI_VERSION on every asset change
 * during development so browsers don't serve a stale cached copy.
 */

defined( 'ABSPATH' ) || exit;

define( 'APPIAPPI_VERSION', '0.2.0' );

function appiappi_enqueue_assets() {
	$theme_uri = get_template_directory_uri();

	wp_enqueue_style( 'appiappi-tokens', $theme_uri . '/assets/css/tokens.css', array(), APPIAPPI_VERSION );
	wp_enqueue_style( 'appiappi-base', $theme_uri . '/assets/css/base.css', array( 'appiappi-tokens' ), APPIAPPI_VERSION );
	wp_enqueue_style( 'appiappi-layout', $theme_uri . '/assets/css/layout.css', array( 'appiappi-base' ), APPIAPPI_VERSION );
	wp_enqueue_style( 'appiappi-components', $theme_uri . '/assets/css/components.css', array( 'appiappi-layout' ), APPIAPPI_VERSION );

	if ( is_front_page() ) {
		wp_enqueue_style( 'appiappi-home', $theme_uri . '/assets/css/home.css', array( 'appiappi-components' ), APPIAPPI_VERSION );
	} else {
		wp_enqueue_style( 'appiappi-pages', $theme_uri . '/assets/css/pages.css', array( 'appiappi-components' ), APPIAPPI_VERSION );
	}

	wp_enqueue_script( 'appiappi-main', $theme_uri . '/assets/js/main.js', array(), APPIAPPI_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'appiappi_enqueue_assets' );

/**
 * Google Fonts (Inter) with preconnect for faster first paint.
 * TODO(perf): self-host these files once the design is locked, to
 * remove the third-party request entirely — see DEVELOPMENT_LOG.md.
 */
function appiappi_preconnect_fonts( $hints, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$hints[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $hints;
}
add_filter( 'wp_resource_hints', 'appiappi_preconnect_fonts', 10, 2 );

function appiappi_enqueue_fonts() {
	wp_enqueue_style(
		'appiappi-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap',
		array(),
		null
	);
}
add_action( 'wp_enqueue_scripts', 'appiappi_enqueue_fonts' );
