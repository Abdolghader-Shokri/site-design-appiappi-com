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

/**
 * Maps one `appiappi_template` post to the array shape every renderer
 * expects. Shared by the shortcode's query loop AND the theme's
 * archive-appiappi_template.php / single-appiappi_template.php (which
 * run the native WP loop rather than a second query) — see § 13 in
 * PROJECT_MASTER.md.
 */
function appiappi_showcase_map_post( $post ) {
	$categories  = get_the_terms( $post, 'appiappi_template_category' );
	$category    = ( $categories && ! is_wp_error( $categories ) ) ? $categories[0] : null;
	$details_url = get_post_meta( $post->ID, '_appiappi_template_details_url', true );

	// A design's category can itself be a child term (a sub-category
	// under a top-level one, e.g. Real Estate > Farmhouse Projects) —
	// the taxonomy is hierarchical specifically to support this without
	// a separate field. If it has a parent, that parent is "group" and
	// the term itself is "subgroup"; otherwise it's just "group".
	$group    = $category ? $category->name : __( 'Design', 'appiappi-template-showcase' );
	$subgroup = '';
	if ( $category && $category->parent ) {
		$parent = get_term( $category->parent, 'appiappi_template_category' );
		if ( $parent && ! is_wp_error( $parent ) ) {
			$group    = $parent->name;
			$subgroup = $category->name;
		}
	}

	$price       = get_post_meta( $post->ID, '_appiappi_template_price', true );
	$price_value = get_post_meta( $post->ID, '_appiappi_template_price_value', true );
	if ( '' === $price_value && function_exists( 'appiappi_showcase_parse_price_value' ) ) {
		$price_value = appiappi_showcase_parse_price_value( $price );
	}

	$gallery_raw = get_post_meta( $post->ID, '_appiappi_template_gallery', true );
	$gallery     = $gallery_raw ? array_values( array_filter( array_map( 'trim', explode( "\n", $gallery_raw ) ) ) ) : array();
	$image       = get_the_post_thumbnail_url( $post, 'appiappi-card' );
	$images      = $image ? array_merge( array( $image ), $gallery ) : $gallery;

	return array(
		'id'           => $post->ID,
		'name'         => get_the_title( $post ),
		'category'     => $group,
		'category_slug'=> $category ? $category->slug : '',
		'subgroup'     => $subgroup,
		'desc'         => get_post_meta( $post->ID, '_appiappi_template_desc', true ),
		'price'        => $price,
		'price_value'  => (float) $price_value,
		'rating'       => get_post_meta( $post->ID, '_appiappi_template_rating', true ),
		'rating_count' => get_post_meta( $post->ID, '_appiappi_template_rating_count', true ),
		'image'        => $image,
		'image_large'  => get_the_post_thumbnail_url( $post, 'appiappi-hero' ),
		'images'       => $images,
		'vendor'       => get_post_meta( $post->ID, '_appiappi_template_vendor', true ),
		'source_url'   => get_post_meta( $post->ID, '_appiappi_template_source_url', true ),
		'demo_url'     => get_post_meta( $post->ID, '_appiappi_template_demo_url', true ) ?: '#',
		'details_url'  => $details_url ?: get_permalink( $post ),
	);
}

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

	$posts     = get_posts( $args );
	$templates = array();
	foreach ( $posts as $post ) {
		$templates[] = appiappi_showcase_map_post( $post );
	}

	return $templates;
}

/**
 * Top-level category terms only (parent = 0) — a design assigned to a
 * child term still shows up under its parent's filter link, but child
 * terms themselves are sub-categories for the card subtitle
 * (appiappi_showcase_map_post()'s group/subgroup), not separate
 * sidebar filter options.
 *
 * @param string      $active_slug Category slug to mark 'active'.
 * @param string|null $base_url    Where the category links should
 *                                  point. Null (default) uses the
 *                                  current request URL — correct when
 *                                  called from the homepage teaser or
 *                                  the /templates/ archive itself (the
 *                                  original behaviour). The single
 *                                  design detail page passes
 *                                  home_url('/templates/') explicitly,
 *                                  since add_query_arg() with no base
 *                                  would otherwise build links against
 *                                  *that* design's own permalink.
 */
function appiappi_showcase_get_categories( $active_slug = '', $base_url = null ) {
	$terms = get_terms( array(
		'taxonomy'   => 'appiappi_template_category',
		'hide_empty' => false,
		'parent'     => 0,
	) );

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	$categories = array(
		array(
			'icon'   => 'grid',
			'label'  => __( 'All Categories', 'appiappi-template-showcase' ),
			'active' => empty( $active_slug ),
			'url'    => null === $base_url ? remove_query_arg( 'appiappi_category' ) : $base_url,
		),
	);

	foreach ( $terms as $term ) {
		$categories[] = array(
			'icon'   => get_term_meta( $term->term_id, 'icon', true ) ?: 'grid',
			'label'  => $term->name,
			'active' => $active_slug === $term->slug,
			// add_query_arg()'s "use the current URL" default only
			// kicks in when the 3rd argument isn't passed at all
			// (func_num_args()-based) — passing null explicitly still
			// counts, so the two cases have to be genuinely separate
			// calls rather than a null-coalescing 3rd argument.
			'url'    => null === $base_url
				? add_query_arg( 'appiappi_category', $term->slug )
				: add_query_arg( 'appiappi_category', $term->slug, $base_url ),
		);
	}

	return $categories;
}

function appiappi_showcase_shortcode( $atts ) {
	$atts = shortcode_atts( array(
		'count'        => 3,
		'category'     => '',
		'show_sidebar' => '1',
	), $atts, 'appiappi_templates' );

	$templates  = appiappi_showcase_get_templates( (int) $atts['count'], sanitize_title( $atts['category'] ) );
	$categories = appiappi_showcase_get_categories( sanitize_title( $atts['category'] ) );
	$show_sidebar = filter_var( $atts['show_sidebar'], FILTER_VALIDATE_BOOLEAN );

	if ( function_exists( 'appiappi_render_template_showcase' ) ) {
		return appiappi_render_template_showcase( $templates, $categories, $show_sidebar );
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
