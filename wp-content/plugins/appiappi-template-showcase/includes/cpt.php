<?php
/**
 * Registers the `appiappi_template` post type and its two taxonomies:
 * `appiappi_template_category` (hierarchical, e.g. Construction, Legal)
 * and `appiappi_template_style` (flat, e.g. Modern, Minimal).
 */

defined( 'ABSPATH' ) || exit;

function appiappi_showcase_register_cpt() {
	register_post_type( 'appiappi_template', array(
		'labels' => array(
			'name'          => __( 'Website Designs', 'appiappi-template-showcase' ),
			'singular_name' => __( 'Website Design', 'appiappi-template-showcase' ),
			'add_new_item'  => __( 'Add New Design', 'appiappi-template-showcase' ),
			'edit_item'     => __( 'Edit Design', 'appiappi-template-showcase' ),
			'all_items'     => __( 'Website Designs', 'appiappi-template-showcase' ),
			'menu_name'     => __( 'Website Designs', 'appiappi-template-showcase' ),
			'not_found'     => __( 'No designs yet.', 'appiappi-template-showcase' ),
		),
		'public'          => true,
		'publicly_queryable' => true,
		'has_archive'     => 'templates',
		'rewrite'         => array( 'slug' => 'templates', 'with_front' => false ),
		'show_ui'         => true,
		'show_in_menu'    => true,
		'show_in_nav_menus' => true,
		'show_in_rest'    => false,
		'menu_icon'       => 'dashicons-layout',
		'supports'        => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
		'capability_type' => 'post',
		'map_meta_cap'    => true,
	) );

	register_taxonomy( 'appiappi_template_category', 'appiappi_template', array(
		'labels' => array(
			'name'          => __( 'Categories', 'appiappi-template-showcase' ),
			'singular_name' => __( 'Category', 'appiappi-template-showcase' ),
		),
		'hierarchical'      => true,
		'public'            => false,
		'show_ui'           => true,
		'show_in_rest'      => false,
		'show_admin_column' => true,
	) );

	register_taxonomy( 'appiappi_template_style', 'appiappi_template', array(
		'labels' => array(
			'name'          => __( 'Styles', 'appiappi-template-showcase' ),
			'singular_name' => __( 'Style', 'appiappi-template-showcase' ),
		),
		'hierarchical'      => false,
		'public'            => false,
		'show_ui'           => true,
		'show_in_rest'      => false,
		'show_admin_column' => true,
	) );
}
add_action( 'init', 'appiappi_showcase_register_cpt' );

/**
 * Paginates the /templates/ archive (the "Website Designs" page) using
 * the admin-configured columns × rows-per-page (Website Designs →
 * Display Settings), and applies the `?appiappi_category=` filter used
 * by the sidebar's category links to the main query itself — so
 * archive-appiappi_template.php can run a normal have_posts()/the_post()
 * loop and appiappi_pagination() (which reads the main $wp_query) works
 * for free, exactly like the blog archive.
 */
function appiappi_showcase_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'appiappi_template' ) ) {
		return;
	}

	$columns = max( 1, min( 4, (int) get_option( 'appiappi_templates_columns', 3 ) ) );
	$rows    = max( 1, (int) get_option( 'appiappi_templates_rows_per_page', 4 ) );
	$query->set( 'posts_per_page', $columns * $rows );
	$query->set( 'orderby', 'menu_order' );
	$query->set( 'order', 'ASC' );

	$category_filter = isset( $_GET['appiappi_category'] ) ? sanitize_title( wp_unslash( $_GET['appiappi_category'] ) ) : '';
	if ( $category_filter ) {
		$query->set( 'tax_query', array( array(
			'taxonomy' => 'appiappi_template_category',
			'field'    => 'slug',
			'terms'    => $category_filter,
		) ) );
	}
}
add_action( 'pre_get_posts', 'appiappi_showcase_archive_query' );

function appiappi_showcase_admin_columns( $columns ) {
	$columns['appiappi_price']  = __( 'Price', 'appiappi-template-showcase' );
	$columns['appiappi_rating'] = __( 'Rating', 'appiappi-template-showcase' );
	return $columns;
}
add_filter( 'manage_appiappi_template_posts_columns', 'appiappi_showcase_admin_columns' );

function appiappi_showcase_admin_column_content( $column, $post_id ) {
	if ( 'appiappi_price' === $column ) {
		$price = get_post_meta( $post_id, '_appiappi_template_price', true );
		echo $price ? esc_html( $price ) : '—';
	}
	if ( 'appiappi_rating' === $column ) {
		$rating = get_post_meta( $post_id, '_appiappi_template_rating', true );
		echo $rating ? esc_html( $rating ) . ' ★' : '—';
	}
}
add_action( 'manage_appiappi_template_posts_custom_column', 'appiappi_showcase_admin_column_content', 10, 2 );
