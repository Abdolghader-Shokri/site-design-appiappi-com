<?php
/**
 * Registers the `appiappi_faq` post type (title = question, native
 * editor content = answer, so answers can have paragraphs/links without
 * a custom rich-text meta field) and a flat `appiappi_faq_category`
 * taxonomy for grouping (e.g. "Pricing", "Hosting", "Cancellation").
 */

defined( 'ABSPATH' ) || exit;

function appiappi_faq_register_cpt() {
	register_post_type( 'appiappi_faq', array(
		'labels' => array(
			'name'          => __( 'FAQs', 'appiappi-faq' ),
			'singular_name' => __( 'FAQ', 'appiappi-faq' ),
			'add_new_item'  => __( 'Add New FAQ', 'appiappi-faq' ),
			'edit_item'     => __( 'Edit FAQ', 'appiappi-faq' ),
			'all_items'     => __( 'FAQs', 'appiappi-faq' ),
			'menu_name'     => __( 'FAQs', 'appiappi-faq' ),
			'not_found'     => __( 'No FAQs yet.', 'appiappi-faq' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'show_in_rest'    => false,
		'menu_icon'       => 'dashicons-editor-help',
		'supports'        => array( 'title', 'editor', 'page-attributes' ),
		'capability_type' => 'post',
		'map_meta_cap'    => true,
	) );

	register_taxonomy( 'appiappi_faq_category', 'appiappi_faq', array(
		'labels' => array(
			'name'          => __( 'FAQ Categories', 'appiappi-faq' ),
			'singular_name' => __( 'FAQ Category', 'appiappi-faq' ),
		),
		'hierarchical'      => false,
		'public'            => false,
		'show_ui'           => true,
		'show_in_rest'      => false,
		'show_admin_column' => true,
	) );
}
add_action( 'init', 'appiappi_faq_register_cpt' );
