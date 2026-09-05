<?php
/**
 * [appiappi_portfolio count="6" industry=""] shortcode. Queries
 * published `appiappi_project` posts, maps each to the shape
 * appiappi_get_portfolio_projects() used, and hands off to the theme's
 * shared appiappi_render_portfolio_grid() so markup is never duplicated.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_portfolio_get_projects( $count = 6, $industry_slug = '' ) {
	$args = array(
		'post_type'      => 'appiappi_project',
		'post_status'    => 'publish',
		'posts_per_page' => $count,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	);

	if ( $industry_slug ) {
		$args['tax_query'] = array( array(
			'taxonomy' => 'appiappi_portfolio_industry',
			'field'    => 'slug',
			'terms'    => $industry_slug,
		) );
	}

	$posts    = get_posts( $args );
	$projects = array();
	foreach ( $posts as $post ) {
		$industries = get_the_terms( $post, 'appiappi_portfolio_industry' );
		$projects[] = array(
			'title'         => get_the_title( $post ),
			'industry'      => ( $industries && ! is_wp_error( $industries ) ) ? $industries[0]->name : '',
			'client'        => get_post_meta( $post->ID, '_appiappi_portfolio_client', true ),
			'location'      => get_post_meta( $post->ID, '_appiappi_portfolio_location', true ),
			'desc'          => wp_trim_words( $post->post_content, 24 ),
			'image'         => get_the_post_thumbnail_url( $post, 'appiappi-card' ) ?: '',
			'external_url'  => get_post_meta( $post->ID, '_appiappi_portfolio_external_url', true ) ?: '',
			'is_concept'    => '1' === get_post_meta( $post->ID, '_appiappi_portfolio_is_concept', true ),
		);
	}
	return $projects;
}

function appiappi_portfolio_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'count'    => 6,
		'industry' => '',
	), $atts, 'appiappi_portfolio' );

	$projects = appiappi_portfolio_get_projects( (int) $atts['count'], sanitize_title( $atts['industry'] ) );

	if ( function_exists( 'appiappi_render_portfolio_grid' ) ) {
		return appiappi_render_portfolio_grid( $projects );
	}

	if ( empty( $projects ) ) {
		return '';
	}
	$out = '<ul>';
	foreach ( $projects as $project ) {
		$out .= '<li>' . esc_html( $project['title'] ) . '</li>';
	}
	$out .= '</ul>';
	return $out;
}
add_shortcode( 'appiappi_portfolio', 'appiappi_portfolio_shortcode' );
