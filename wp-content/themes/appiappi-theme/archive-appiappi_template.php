<?php
/**
 * Website Designs archive (/templates/) — the full browsable library,
 * per MASTER_PROMPT.md § Website Template Library. Reuses the exact
 * same data-mapping functions the homepage teaser and the [appiappi_templates]
 * shortcode use (appiappi_showcase_get_templates()/_get_categories()/_get_styles()
 * from the appiappi-template-showcase plugin), so results are always
 * consistent — this template just asks for ALL designs (count = -1)
 * instead of a homepage-sized sample, and always shows the sidebar.
 *
 * Category filtering: `?appiappi_category=<slug>` (same convention as
 * the homepage teaser) does a real tax_query via a full page load. Style
 * checkboxes + search are live client-side JS (assets/js/main.js) since
 * every design is already in the DOM here.
 */

get_header();

$category_filter = isset( $_GET['appiappi_category'] ) ? sanitize_title( wp_unslash( $_GET['appiappi_category'] ) ) : '';

$templates  = function_exists( 'appiappi_showcase_get_templates' ) ? appiappi_showcase_get_templates( -1, $category_filter ) : appiappi_get_featured_templates();
$categories = function_exists( 'appiappi_showcase_get_categories' ) ? appiappi_showcase_get_categories( $category_filter ) : appiappi_get_template_categories();
$styles     = function_exists( 'appiappi_showcase_get_styles' ) ? appiappi_showcase_get_styles() : appiappi_get_template_styles();
?>

<main id="main-content">
	<?php appiappi_breadcrumbs(); ?>
	<?php appiappi_page_header( __( 'Every professionally curated website design in one place — filter by style, search, or browse by industry.', 'appiappi' ) ); ?>

	<section class="section">
		<div class="container">
			<?php echo appiappi_render_template_showcase( $templates, $categories, $styles, true ); ?>
		</div>
	</section>

	<?php get_template_part( 'template-parts/sections/final-cta' ); ?>
</main>

<?php
get_footer();
