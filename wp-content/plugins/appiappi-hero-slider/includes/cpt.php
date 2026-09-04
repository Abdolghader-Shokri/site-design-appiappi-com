<?php
/**
 * Registers the `appiappi_slide` post type. The post Title is the slide's
 * headline; the Featured Image is the slide's visual — kept consistent
 * with how appiappi-template-showcase uses the Featured Image, rather
 * than a separate custom image-URL field.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_hero_register_cpt() {
	register_post_type( 'appiappi_slide', array(
		'labels' => array(
			'name'          => __( 'Hero Slides', 'appiappi-hero-slider' ),
			'singular_name' => __( 'Hero Slide', 'appiappi-hero-slider' ),
			'add_new_item'  => __( 'Add New Slide', 'appiappi-hero-slider' ),
			'edit_item'     => __( 'Edit Slide', 'appiappi-hero-slider' ),
			'all_items'     => __( 'Hero Slides', 'appiappi-hero-slider' ),
			'menu_name'     => __( 'Hero Slides', 'appiappi-hero-slider' ),
			'not_found'     => __( 'No slides yet.', 'appiappi-hero-slider' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'show_in_rest'    => false,
		'menu_icon'       => 'dashicons-images-alt2',
		'supports'        => array( 'title', 'thumbnail', 'page-attributes' ),
		'capability_type' => 'post',
		'map_meta_cap'    => true,
	) );
}
add_action( 'init', 'appiappi_hero_register_cpt' );

function appiappi_hero_admin_columns( $columns ) {
	$columns['appiappi_cta'] = __( 'CTA', 'appiappi-hero-slider' );
	return $columns;
}
add_filter( 'manage_appiappi_slide_posts_columns', 'appiappi_hero_admin_columns' );

function appiappi_hero_admin_column_content( $column, $post_id ) {
	if ( 'appiappi_cta' === $column ) {
		$text = get_post_meta( $post_id, '_appiappi_slide_cta_text', true );
		echo $text ? esc_html( $text ) : '—';
	}
}
add_action( 'manage_appiappi_slide_posts_custom_column', 'appiappi_hero_admin_column_content', 10, 2 );
