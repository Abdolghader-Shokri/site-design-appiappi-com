<?php
/**
 * Registers the `appiappi_service` post type. Not public — no front-end
 * template of its own, only ever shown via the [appiappi_services]
 * shortcode on the theme's Services page. `page-attributes` support
 * gives admins a drag-orderable "Order" field (menu_order) so services
 * can be sequenced deliberately rather than by publish date. No native
 * `editor` support — every field (including the "Hook") is rendered as
 * plain escaped text on the Services page, so a plain textarea in the
 * Service Details meta box (not the rich-text editor) is the honest fit.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_services_register_cpt() {
	register_post_type( 'appiappi_service', array(
		'labels' => array(
			'name'          => __( 'Services', 'appiappi-services' ),
			'singular_name' => __( 'Service', 'appiappi-services' ),
			'add_new_item'  => __( 'Add New Service', 'appiappi-services' ),
			'edit_item'     => __( 'Edit Service', 'appiappi-services' ),
			'all_items'     => __( 'Services', 'appiappi-services' ),
			'menu_name'     => __( 'Services', 'appiappi-services' ),
			'not_found'     => __( 'No services yet.', 'appiappi-services' ),
		),
		'public'             => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_nav_menus'  => true,
		'show_in_rest'       => false,
		'menu_icon'          => 'dashicons-hammer',
		'supports'           => array( 'title', 'page-attributes' ),
		'capability_type'    => 'post',
		'map_meta_cap'       => true,
	) );
}
add_action( 'init', 'appiappi_services_register_cpt' );
