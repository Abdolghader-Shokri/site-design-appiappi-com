<?php
/**
 * Core theme setup: supports, menus, image sizes.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 60,
		'width'       => 200,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'automatic-feed-links' );

	register_nav_menus( array(
		'primary' => __( 'Primary Navigation', 'appiappi' ),
		'footer'  => __( 'Footer Navigation', 'appiappi' ),
	) );

	add_image_size( 'appiappi-card', 640, 480, true );
	add_image_size( 'appiappi-hero', 1200, 900, true );
}
add_action( 'after_setup_theme', 'appiappi_setup' );

/**
 * WordPress core CSS from wp-block-library etc. is not needed on a
 * non-block, non-Gutenberg-frontend theme like this one.
 */
function appiappi_dequeue_core_assets() {
	wp_dequeue_style( 'wp-block-library' );
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'global-styles' );
}
add_action( 'wp_enqueue_scripts', 'appiappi_dequeue_core_assets', 20 );

/**
 * Theme favicon (the maple-leaf mark used in the logo, as an SVG).
 * Skipped automatically if the admin sets a Site Icon in
 * Appearance > Customize > Site Identity — that takes precedence.
 */
function appiappi_favicon() {
	if ( has_site_icon() ) {
		return;
	}
	printf(
		'<link rel="icon" type="image/svg+xml" href="%s">' . "\n",
		esc_url( get_template_directory_uri() . '/assets/images/favicon.svg' )
	);
}
add_action( 'wp_head', 'appiappi_favicon', 1 );
