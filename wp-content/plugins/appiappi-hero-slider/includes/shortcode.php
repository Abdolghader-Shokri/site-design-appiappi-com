<?php
/**
 * [appiappi_hero_slider] shortcode. Queries published `appiappi_slide`
 * posts (ordered by menu_order), maps each to the same array shape the
 * theme's appiappi_get_hero_slides() placeholder used, and hands off to
 * the theme's shared appiappi_render_hero_slides() so markup is never
 * duplicated. If the plugin is active but has zero published slides yet,
 * it falls back to the theme's own single default slide rather than
 * showing an empty hero.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_hero_get_slides() {
	$posts = get_posts( array(
		'post_type'      => 'appiappi_slide',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	) );

	$slides = array();
	foreach ( $posts as $post ) {
		$slides[] = array(
			'headline'    => get_the_title( $post ),
			'subheadline' => get_post_meta( $post->ID, '_appiappi_slide_subheadline', true ),
			'image'       => get_the_post_thumbnail_url( $post, 'appiappi-hero' ) ?: '',
			'image_alt'   => get_post_meta( $post->ID, '_appiappi_slide_image_alt', true ),
			'cta_text'    => get_post_meta( $post->ID, '_appiappi_slide_cta_text', true ) ?: __( 'Get Started', 'appiappi-hero-slider' ),
			'cta_url'     => get_post_meta( $post->ID, '_appiappi_slide_cta_url', true ) ?: '#',
		);
	}
	return $slides;
}

function appiappi_hero_shortcode() {
	$slides = appiappi_hero_get_slides();

	if ( empty( $slides ) && function_exists( 'appiappi_get_hero_slides' ) ) {
		$slides = appiappi_get_hero_slides();
	}

	if ( function_exists( 'appiappi_render_hero_slides' ) ) {
		return appiappi_render_hero_slides( $slides );
	}

	// Minimal fallback if the Appiappi theme (which owns the hero markup) isn't active.
	if ( empty( $slides ) ) {
		return '';
	}
	return '<h1>' . esc_html( $slides[0]['headline'] ) . '</h1><p>' . esc_html( $slides[0]['subheadline'] ) . '</p>';
}
add_shortcode( 'appiappi_hero_slider', 'appiappi_hero_shortcode' );
