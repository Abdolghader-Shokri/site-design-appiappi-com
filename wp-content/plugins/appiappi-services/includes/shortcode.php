<?php
/**
 * [appiappi_services] shortcode. Queries published `appiappi_service`
 * posts ordered by menu_order (the drag-orderable "Order" field), maps
 * each to the array shape appiappi_render_services() expects (same
 * shape as the theme's appiappi_get_services() placeholder), and hands
 * off to that shared theme function so markup is never duplicated.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Maps one `appiappi_service` post to the render-ready array shape.
 * `id` uses the post's slug (not the numeric ID) so footer anchor
 * links (site-footer.php) stay stable even if posts are re-imported.
 */
function appiappi_services_map_post( $post ) {
	return array(
		'id'        => $post->post_name,
		'icon'      => get_post_meta( $post->ID, '_appiappi_service_icon', true ) ?: 'monitor',
		'name'      => get_the_title( $post ),
		'hook'      => get_post_meta( $post->ID, '_appiappi_service_hook', true ),
		'breakdown' => array_values( array_filter( array_map( 'trim', explode( "\n", get_post_meta( $post->ID, '_appiappi_service_breakdown', true ) ) ) ) ),
		'closing'   => get_post_meta( $post->ID, '_appiappi_service_closing', true ),
	);
}

function appiappi_services_get_services() {
	$posts = get_posts( array(
		'post_type'      => 'appiappi_service',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order',
		'order'          => 'ASC',
	) );

	return array_map( 'appiappi_services_map_post', $posts );
}

function appiappi_services_shortcode() {
	$services = appiappi_services_get_services();

	if ( function_exists( 'appiappi_render_services' ) ) {
		return appiappi_render_services( $services );
	}

	// Minimal fallback if the Appiappi theme (which owns the service-block markup) isn't active.
	if ( empty( $services ) ) {
		return is_user_logged_in() && current_user_can( 'edit_posts' )
			? '<p>' . esc_html__( 'No services published yet. Add some under Services in wp-admin.', 'appiappi-services' ) . '</p>'
			: '';
	}
	$out = '<ul>';
	foreach ( $services as $service ) {
		$out .= sprintf( '<li>%s</li>', esc_html( $service['name'] ) );
	}
	$out .= '</ul>';
	return $out;
}
add_shortcode( 'appiappi_services', 'appiappi_services_shortcode' );
