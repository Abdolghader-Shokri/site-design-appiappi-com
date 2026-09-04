<?php
/**
 * Registers the `appiappi_plan` post type. Plans are ordered via the
 * native "Order" field (menu_order) — drag order isn't built in, but the
 * Order number field is enough for a handful of plans.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_pricing_register_cpt() {
	register_post_type( 'appiappi_plan', array(
		'labels' => array(
			'name'               => __( 'Pricing Plans', 'appiappi-pricing-plans' ),
			'singular_name'      => __( 'Pricing Plan', 'appiappi-pricing-plans' ),
			'add_new_item'       => __( 'Add New Plan', 'appiappi-pricing-plans' ),
			'edit_item'          => __( 'Edit Plan', 'appiappi-pricing-plans' ),
			'all_items'          => __( 'Pricing Plans', 'appiappi-pricing-plans' ),
			'menu_name'          => __( 'Pricing Plans', 'appiappi-pricing-plans' ),
			'not_found'          => __( 'No plans yet.', 'appiappi-pricing-plans' ),
		),
		'public'             => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_rest'       => false,
		'menu_icon'          => 'dashicons-money-alt',
		'supports'           => array( 'title', 'page-attributes' ),
		'capability_type'    => 'post',
		'map_meta_cap'       => true,
	) );
}
add_action( 'init', 'appiappi_pricing_register_cpt' );

/**
 * Friendlier admin list: show price/featured/order at a glance instead
 * of just the title.
 */
function appiappi_pricing_admin_columns( $columns ) {
	$columns['appiappi_price']    = __( 'Price', 'appiappi-pricing-plans' );
	$columns['appiappi_featured'] = __( 'Featured', 'appiappi-pricing-plans' );
	return $columns;
}
add_filter( 'manage_appiappi_plan_posts_columns', 'appiappi_pricing_admin_columns' );

function appiappi_pricing_admin_column_content( $column, $post_id ) {
	if ( 'appiappi_price' === $column ) {
		$price  = get_post_meta( $post_id, '_appiappi_plan_price', true );
		$period = get_post_meta( $post_id, '_appiappi_plan_period', true );
		echo $price ? '$' . esc_html( $price ) . ' ' . esc_html( $period ) : '—';
	}
	if ( 'appiappi_featured' === $column ) {
		echo get_post_meta( $post_id, '_appiappi_plan_featured', true ) ? '★' : '—';
	}
}
add_action( 'manage_appiappi_plan_posts_custom_column', 'appiappi_pricing_admin_column_content', 10, 2 );
