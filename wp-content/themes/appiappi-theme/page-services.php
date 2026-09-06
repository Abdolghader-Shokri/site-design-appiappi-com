<?php
/**
 * Template Name: Services Page
 * Also auto-applies to a page with the slug "services" via the
 * page-services.php template-hierarchy convention.
 *
 * Each service is a Hook (short benefit statement) + a Precision
 * Breakdown (however many concrete sub-tasks) + a closing line
 * bridging "service" to "partner". Uses the appiappi-services plugin's
 * [appiappi_services] shortcode via shortcode_exists() when active,
 * falling back to the theme's appiappi_get_services() placeholder
 * otherwise — both paths render through the shared
 * appiappi_render_services() in inc/template-tags.php, so markup never
 * duplicates and the footer's per-service links (site-footer.php) work
 * against either source.
 */

get_header();
?>

<main id="main-content">
	<?php appiappi_breadcrumbs(); ?>
	<?php appiappi_page_header( __( "Your website isn't a brochure — it's foundational business infrastructure. Here's exactly what goes into building, running and growing it.", 'appiappi' ), 'services' ); ?>

	<section class="section">
		<div class="container">
			<?php if ( shortcode_exists( 'appiappi_services' ) ) : ?>
				<?php echo do_shortcode( '[appiappi_services]' ); ?>
			<?php else : ?>
				<?php echo appiappi_render_services( appiappi_get_services() ); ?>
			<?php endif; ?>
		</div>
	</section>

	<?php get_template_part( 'template-parts/sections/final-cta' ); ?>
</main>

<?php
get_footer();
