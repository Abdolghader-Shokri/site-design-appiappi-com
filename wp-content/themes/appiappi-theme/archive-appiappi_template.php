<?php
/**
 * Website Designs archive (/templates/) — the full browsable library,
 * per MASTER_PROMPT.md § Website Template Library. Reuses the exact
 * same data-mapping functions the homepage teaser and the [appiappi_templates]
 * shortcode use (appiappi_showcase_map_post()/_get_categories() from the
 * appiappi-template-showcase plugin), so results are always consistent.
 *
 * Unlike the homepage teaser (a fixed small sample), this template runs
 * the native main query/loop so it paginates: the plugin's
 * appiappi_showcase_archive_query() (on pre_get_posts) sets
 * posts_per_page to the admin-configured columns × rows-per-page
 * (Website Designs → Display Settings, default 3 × 4 = 12) and applies
 * the `?appiappi_category=<slug>` filter, `?min_price=`/`?max_price=`
 * and `?sort=` (same convention as the homepage teaser) as a real
 * tax_query/meta_query/orderby on this same main query, so
 * appiappi_pagination() (which reads $wp_query directly) works exactly
 * like the blog archive. Search is live client-side JS
 * (assets/js/main.js) filtering within the current page's results —
 * price/sort/category are real, correctly-paginated filters instead.
 */

get_header();

$category_filter = isset( $_GET['appiappi_category'] ) ? sanitize_title( wp_unslash( $_GET['appiappi_category'] ) ) : '';
$categories       = function_exists( 'appiappi_showcase_get_categories' ) ? appiappi_showcase_get_categories( $category_filter ) : appiappi_get_template_categories();
$price_range      = function_exists( 'appiappi_showcase_get_price_range' ) ? appiappi_showcase_get_price_range() : null;

$templates = array();
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		$templates[] = function_exists( 'appiappi_showcase_map_post' ) ? appiappi_showcase_map_post( get_post() ) : array();
	}
	wp_reset_postdata();
}
$total = $GLOBALS['wp_query']->found_posts;
?>

<main id="main-content">
	<?php appiappi_breadcrumbs(); ?>
	<?php appiappi_page_header( __( 'Every professionally curated website design in one place — filter by price, search, or browse by industry.', 'appiappi' ), 'templates' ); ?>

	<section class="section">
		<div class="container">
			<?php echo appiappi_render_template_showcase( $templates, $categories, true, null, $total, $price_range ); ?>
			<?php appiappi_pagination(); ?>
		</div>
	</section>

	<?php get_template_part( 'template-parts/sections/final-cta' ); ?>
</main>

<?php
get_footer();
