<?php
/**
 * Registers the `appiappi_project` post type (title = project
 * name, native editor content = description, Featured Image = main
 * project image) and a flat `appiappi_portfolio_industry` taxonomy.
 *
 * Scope note: before/after image pairs and a full screenshot gallery
 * (both in the original spec) are deferred — the single Featured Image
 * covers the MVP. Add a gallery meta field (array of attachment IDs)
 * later if needed; not built now to avoid over-scoping this pass.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_portfolio_register_cpt() {
	register_post_type( 'appiappi_project', array(
		'labels' => array(
			'name'          => __( 'Portfolio Projects', 'appiappi-portfolio' ),
			'singular_name' => __( 'Portfolio Project', 'appiappi-portfolio' ),
			'add_new_item'  => __( 'Add New Project', 'appiappi-portfolio' ),
			'edit_item'     => __( 'Edit Project', 'appiappi-portfolio' ),
			'all_items'     => __( 'Portfolio', 'appiappi-portfolio' ),
			'menu_name'     => __( 'Portfolio', 'appiappi-portfolio' ),
			'not_found'     => __( 'No projects yet.', 'appiappi-portfolio' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'show_in_rest'    => false,
		'menu_icon'       => 'dashicons-portfolio',
		'supports'        => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		'capability_type' => 'post',
		'map_meta_cap'    => true,
	) );

	register_taxonomy( 'appiappi_portfolio_industry', 'appiappi_project', array(
		'labels' => array(
			'name'          => __( 'Industries', 'appiappi-portfolio' ),
			'singular_name' => __( 'Industry', 'appiappi-portfolio' ),
		),
		'hierarchical'      => false,
		'public'            => false,
		'show_ui'           => true,
		'show_in_rest'      => false,
		'show_admin_column' => true,
	) );
}
add_action( 'init', 'appiappi_portfolio_register_cpt' );
