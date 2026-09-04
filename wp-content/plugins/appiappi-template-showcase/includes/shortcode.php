<?php
/**
 * [appiappi_templates count="3" category="" show_sidebar="1"] shortcode.
 * Queries published `appiappi_template` posts, maps each to the same
 * array shape the theme's appiappi_get_featured_templates() placeholder
 * used, builds the sidebar's category/style lists from the real
 * taxonomies, and hands off to the theme's shared
 * appiappi_render_template_showcase() so markup is never duplicated.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_showcase_get_templates( $count = 3, $category_slug = '' ) {
	$args = array(
		'post_type'      => 'appiappi_template',
		'post_status'    => 'publish',
		'posts_per_page' => $count,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	);

	if ( $category_slug ) {
		$args['tax_query'] = array( array(
			'taxonomy' => 'appiappi_template_category',
			'field'    => 'slug',
			'terms'    => $category_slug,
		) );
	}

	$posts    = get_posts( $args );
	$defaults = array(
		'category' => __( 'Design', 'appiappi-template-showcase' ),
		'style'    => '',
	);

	$templates = array();
	foreach ( $posts as $post ) {
		$categories = get_the_terms( $post, 'appiappi_template_category' );
		$styles     = get_the_terms( $post, 'appiappi_template_style' );

		$templates[] = array(
			'name'         => get_the_title( $post ),
			'category'     => ( $categories && ! is_wp_error( $categories ) ) ? $categories[0]->name : $defaults['category'],
			'style'        => ( $styles && ! is_wp_error( $styles ) ) ? $styles[0]->name : $defaults['style'],
			'desc'         => get_post_meta( $post->ID, '_appiappi_template_desc', true ),
			'price'        => get_post_meta( $post->ID, '_appiappi_template_price', true ),
			'rating'       => get_post_meta( $post->ID, '_appiappi_template_rating', true ),
			'rating_count' => get_post_meta( $post->ID, '_appiappi_template_rating_count', true ),
			'image'        => get_the_post_thumbnail_url( $post, 'appiappi-card' ),
			'demo_url'     => get_post_meta( $post->ID, '_appiappi_template_demo_url', true ) ?: '#',
			'details_url'  => get_post_meta( $post->ID, '_appiappi_template_details_url', true ) ?: '#',
		);
	}

	return $templates;
}

function appiappi_showcase_get_categories( $active_slug = '' ) {
	$terms = get_terms( array(
		'taxonomy'   => 'appiappi_template_category',
		'hide_empty' => false,
	) );

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	$categories = array(
		array(
			'icon'   => 'grid',
			'label'  => __( 'All Categories', 'appiappi-template-showcase' ),
			'active' => empty( $active_slug ),
			'url'    => remove_query_arg( 'appiappi_category' ),
		),
	);

	foreach ( $terms as $term ) {
		$categories[] = array(
			'icon'   => get_term_meta( $term->term_id, 'icon', true ) ?: 'grid',
			'label'  => $term->name,
			'active' => $active_slug === $term->slug,
			'url'    => add_query_arg( 'appiappi_category', $term->slug ),
		);
	}

	return $categories;
}

function appiappi_showcase_get_styles() {
	$terms = get_terms( array(
		'taxonomy'   => 'appiappi_template_style',
		'hide_empty' => false,
	) );

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	return wp_list_pluck( $terms, 'name' );
}

function appiappi_showcase_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'count'        => 3,
		'category'     => '',
		'show_sidebar' => '1',
	), $atts, 'appiappi_templates' );

	$templates  = appiappi_showcase_get_templates( (int) $atts['count'], sanitize_title( $atts['category'] ) );
	$categories = appiappi_showcase_get_categories( sanitize_title( $atts['category'] ) );
	$styles     = appiappi_showcase_get_styles();
	$show_sidebar = filter_var( $atts['show_sidebar'], FILTER_VALIDATE_BOOLEAN );

	if ( function_exists( 'appiappi_render_template_showcase' ) ) {
		return appiappi_render_template_showcase( $templates, $categories, $styles, $show_sidebar );
	}

	// Minimal fallback if the Appiappi theme (which owns the showcase markup) isn't active.
	if ( empty( $templates ) ) {
		return is_user_logged_in() && current_user_can( 'edit_posts' )
			? '<p>' . esc_html__( 'No website designs published yet. Add some under Website Designs in wp-admin.', 'appiappi-template-showcase' ) . '</p>'
			: '';
	}
	$out = '<ul>';
	foreach ( $templates as $template ) {
		$out .= sprintf( '<li>%s — %s</li>', esc_html( $template['name'] ), esc_html( $template['price'] ) );
	}
	$out .= '</ul>';
	return $out;
}
add_shortcode( 'appiappi_templates', 'appiappi_showcase_shortcode' );
