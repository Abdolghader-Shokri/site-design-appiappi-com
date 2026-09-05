<?php
/**
 * [appiappi_pricing] shortcode. Queries published `appiappi_plan` posts
 * ordered by menu_order, maps each to the same array shape the theme's
 * appiappi_get_pricing_plans() placeholder used, and hands off to the
 * theme's shared appiappi_render_pricing_cards() renderer so markup
 * never has to be duplicated between theme and plugin.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_pricing_get_plans() {
	$posts = get_posts( array(
		'post_type'      => 'appiappi_plan',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	) );

	$color_map = array(
		'starter'      => 'var(--color-plan-starter)',
		'business'     => 'var(--color-plan-business)',
		'professional' => 'var(--color-plan-professional)',
		'growth'       => 'var(--color-plan-growth)',
		'seo-growth'   => 'var(--color-plan-seo-growth)',
	);

	$plans = array();
	foreach ( $posts as $post ) {
		$color_key  = get_post_meta( $post->ID, '_appiappi_plan_color', true );
		$features   = get_post_meta( $post->ID, '_appiappi_plan_features', true );
		$homepage_visible_raw = get_post_meta( $post->ID, '_appiappi_plan_homepage_visible', true );

		$plans[] = array(
			'id'               => $post->post_name,
			'icon'             => get_post_meta( $post->ID, '_appiappi_plan_icon', true ) ?: 'rocket',
			'name'             => get_the_title( $post ),
			'tagline'          => get_post_meta( $post->ID, '_appiappi_plan_tagline', true ),
			'audience'         => get_post_meta( $post->ID, '_appiappi_plan_audience', true ),
			'value_driver'     => get_post_meta( $post->ID, '_appiappi_plan_value_driver', true ),
			'group'            => get_post_meta( $post->ID, '_appiappi_plan_group', true ) ?: 'launch',
			'homepage_visible' => ( '' === $homepage_visible_raw ) ? true : ( '1' === $homepage_visible_raw ),
			'description' => $post->post_content ? apply_filters( 'the_content', $post->post_content ) : '',
			'price'    => get_post_meta( $post->ID, '_appiappi_plan_price', true ),
			'period'   => get_post_meta( $post->ID, '_appiappi_plan_period', true ),
			'note'     => get_post_meta( $post->ID, '_appiappi_plan_note', true ),
			'color'    => isset( $color_map[ $color_key ] ) ? $color_map[ $color_key ] : $color_map['business'],
			'featured' => (bool) get_post_meta( $post->ID, '_appiappi_plan_featured', true ),
			'badge'    => get_post_meta( $post->ID, '_appiappi_plan_badge', true ),
			'features' => $features ? array_filter( array_map( 'trim', explode( "\n", $features ) ) ) : array(),
			'cta_text' => get_post_meta( $post->ID, '_appiappi_plan_cta_text', true ) ?: __( 'Choose Plan', 'appiappi-pricing-plans' ),
			'cta_url'  => get_post_meta( $post->ID, '_appiappi_plan_cta_url', true ) ?: '#contact',
		);
	}

	return $plans;
}

function appiappi_pricing_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'homepage_only'   => '0',
		'group'           => '',
		'show_description' => '0',
		'link_to_pricing' => '0',
	), $atts, 'appiappi_pricing' );

	$plans = appiappi_pricing_get_plans();

	if ( filter_var( $atts['homepage_only'], FILTER_VALIDATE_BOOLEAN ) ) {
		$plans = array_values( array_filter( $plans, function ( $plan ) {
			return ! empty( $plan['homepage_visible'] );
		} ) );
	}

	if ( $atts['group'] ) {
		$group = sanitize_key( $atts['group'] );
		$plans = array_values( array_filter( $plans, function ( $plan ) use ( $group ) {
			return $plan['group'] === $group;
		} ) );
	}

	if ( empty( $plans ) ) {
		return is_user_logged_in() && current_user_can( 'edit_posts' )
			? '<p>' . esc_html__( 'No pricing plans published yet. Add some under Pricing Plans in wp-admin.', 'appiappi-pricing-plans' ) . '</p>'
			: '';
	}

	if ( function_exists( 'appiappi_render_pricing_cards' ) ) {
		return appiappi_render_pricing_cards(
			$plans,
			filter_var( $atts['show_description'], FILTER_VALIDATE_BOOLEAN ),
			filter_var( $atts['link_to_pricing'], FILTER_VALIDATE_BOOLEAN )
		);
	}

	// Minimal fallback if the Appiappi theme (which owns the card markup) isn't active.
	$out = '<ul>';
	foreach ( $plans as $plan ) {
		$out .= sprintf( '<li>%s — $%s %s</li>', esc_html( $plan['name'] ), esc_html( $plan['price'] ), esc_html( $plan['period'] ) );
	}
	$out .= '</ul>';
	return $out;
}
add_shortcode( 'appiappi_pricing', 'appiappi_pricing_shortcode' );
