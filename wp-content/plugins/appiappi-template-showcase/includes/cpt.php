<?php
/**
 * Registers the `appiappi_template` post type and its
 * `appiappi_template_category` taxonomy (hierarchical, e.g. Real Estate,
 * with optional child terms for a sub-category — see
 * appiappi_showcase_map_post()'s group/subgroup). The `appiappi_template_style`
 * taxonomy that used to live here was removed 2026-09-06 — no longer
 * a filterable dimension, per explicit request ("style لازم نیست باشه").
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
}
add_action( 'init', 'appiappi_showcase_register_cpt' );

/**
 * Extracts a plain numeric dollar value from a display price string
 * (e.g. "$59" -> 59.0, "$1,299.50" -> 1299.5). Used to keep
 * `_appiappi_template_price_value` (a real number, for orderby=meta_value_num
 * sorting and the price-range filter) in sync with the display string
 * wherever price gets set — this meta box's save handler and
 * price-sync.php's Envato sync both call it.
 */
function appiappi_showcase_parse_price_value( $price_string ) {
	$digits = preg_replace( '/[^0-9.]/', '', (string) $price_string );
	return $digits ? (float) $digits : 0.0;
}

/**
 * Global min/max of `_appiappi_template_price_value` across every
 * published design — powers the sidebar price-range filter's bounds.
 * A single SQL aggregate rather than looping every post in PHP, so
 * this stays fast even at a few thousand designs. Cached; invalidated
 * whenever a design is saved.
 */
function appiappi_showcase_get_price_range() {
	$cached = get_transient( 'appiappi_showcase_price_range' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;
	$row = $wpdb->get_row( $wpdb->prepare(
		"SELECT MIN(CAST(pm.meta_value AS DECIMAL(10,2))) AS min_price, MAX(CAST(pm.meta_value AS DECIMAL(10,2))) AS max_price
		 FROM {$wpdb->postmeta} pm
		 INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
		 WHERE pm.meta_key = %s AND p.post_type = %s AND p.post_status = 'publish'",
		'_appiappi_template_price_value',
		'appiappi_template'
	) );

	$range = array(
		'min' => ( $row && null !== $row->min_price ) ? (float) $row->min_price : 0.0,
		'max' => ( $row && null !== $row->max_price ) ? (float) $row->max_price : 0.0,
	);
	set_transient( 'appiappi_showcase_price_range', $range, DAY_IN_SECONDS );
	return $range;
}
add_action( 'save_post_appiappi_template', function () {
	delete_transient( 'appiappi_showcase_price_range' );
} );

/**
 * Paginates the /templates/ archive (the "Website Designs" page) using
 * the admin-configured columns × rows-per-page (Website Designs →
 * Display Settings), and applies the `?appiappi_category=` filter used
 * by the sidebar's category links, `?min_price=`/`?max_price=` (the
 * sidebar's price-range filter) and `?sort=price-asc|price-desc|rating-desc`
 * (the toolbar's sort controls) to the main query itself — so
 * archive-appiappi_template.php can run a normal have_posts()/the_post()
 * loop and appiappi_pagination() (which reads the main $wp_query) works
 * for free, exactly like the blog archive. Real server-side filtering/
 * sorting (not client-side JS on the current page only) is deliberate:
 * at a catalogue of hundreds/thousands of designs, "sort by price" or
 * "under $30" has to work across the whole, correctly paginated result
 * set, not just whichever 12 happen to be on the page already.
 */
function appiappi_showcase_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'appiappi_template' ) ) {
		return;
	}

	$columns = max( 1, min( 4, (int) get_option( 'appiappi_templates_columns', 3 ) ) );
	$rows    = max( 1, (int) get_option( 'appiappi_templates_rows_per_page', 4 ) );
	$query->set( 'posts_per_page', $columns * $rows );

	$category_filter = isset( $_GET['appiappi_category'] ) ? sanitize_title( wp_unslash( $_GET['appiappi_category'] ) ) : '';
	if ( $category_filter ) {
		$query->set( 'tax_query', array( array(
			'taxonomy' => 'appiappi_template_category',
			'field'    => 'slug',
			'terms'    => $category_filter,
		) ) );
	}

	$min_price = isset( $_GET['min_price'] ) ? (float) $_GET['min_price'] : null;
	$max_price = isset( $_GET['max_price'] ) ? (float) $_GET['max_price'] : null;
	if ( null !== $min_price || null !== $max_price ) {
		$price_clause = array( 'key' => '_appiappi_template_price_value', 'type' => 'NUMERIC' );
		if ( null !== $min_price && null !== $max_price ) {
			$price_clause['value']   = array( $min_price, $max_price );
			$price_clause['compare'] = 'BETWEEN';
		} elseif ( null !== $min_price ) {
			$price_clause['value']   = $min_price;
			$price_clause['compare'] = '>=';
		} else {
			$price_clause['value']   = $max_price;
			$price_clause['compare'] = '<=';
		}
		$query->set( 'meta_query', array( $price_clause ) );
	}

	// One combined ?sort= value (matches the sidebar/toolbar controls'
	// single <select>/toggle-link, rather than separate orderby+order
	// params for the UI to keep in sync).
	$sort = isset( $_GET['sort'] ) ? sanitize_key( $_GET['sort'] ) : '';
	switch ( $sort ) {
		case 'price-asc':
			$query->set( 'meta_key', '_appiappi_template_price_value' );
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'ASC' );
			break;
		case 'price-desc':
			$query->set( 'meta_key', '_appiappi_template_price_value' );
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'DESC' );
			break;
		case 'rating-desc':
			$query->set( 'meta_key', '_appiappi_template_rating' );
			$query->set( 'orderby', 'meta_value_num' );
			$query->set( 'order', 'DESC' );
			break;
		default:
			$query->set( 'orderby', 'menu_order' );
			$query->set( 'order', 'ASC' );
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
