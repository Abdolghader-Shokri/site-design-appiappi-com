<?php
/**
 * [appiappi_faq category="" limit="-1"] shortcode. Queries published
 * `appiappi_faq` posts (ordered by menu_order), maps each to
 * {question, answer}, and hands off to the theme's shared
 * appiappi_render_faq() so markup is never duplicated.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_faq_get_items( $category_slug = '', $limit = -1 ) {
	$args = array(
		'post_type'      => 'appiappi_faq',
		'post_status'    => 'publish',
		'posts_per_page' => $limit,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	);

	if ( $category_slug ) {
		$args['tax_query'] = array( array(
			'taxonomy' => 'appiappi_faq_category',
			'field'    => 'slug',
			'terms'    => $category_slug,
		) );
	}

	$posts = get_posts( $args );
	$faqs  = array();
	foreach ( $posts as $post ) {
		$faqs[] = array(
			'question' => get_the_title( $post ),
			'answer'   => apply_filters( 'the_content', $post->post_content ),
		);
	}
	return $faqs;
}

function appiappi_faq_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'category' => '',
		'limit'    => -1,
	), $atts, 'appiappi_faq' );

	$faqs = appiappi_faq_get_items( sanitize_title( $atts['category'] ), (int) $atts['limit'] );

	if ( function_exists( 'appiappi_render_faq' ) ) {
		return appiappi_render_faq( $faqs );
	}

	if ( empty( $faqs ) ) {
		return '';
	}
	$out = '<dl>';
	foreach ( $faqs as $faq ) {
		$out .= '<dt>' . esc_html( $faq['question'] ) . '</dt><dd>' . wp_kses_post( $faq['answer'] ) . '</dd>';
	}
	$out .= '</dl>';
	return $out;
}
add_shortcode( 'appiappi_faq', 'appiappi_faq_shortcode' );
