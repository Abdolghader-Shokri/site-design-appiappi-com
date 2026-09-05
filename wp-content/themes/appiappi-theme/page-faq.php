<?php
/**
 * Template Name: FAQ Page
 * Auto-applies to a page with the slug "faq". Uses the appiappi-faq
 * plugin's [appiappi_faq] shortcode when active, else the theme's
 * appiappi_get_faqs() placeholder — both via appiappi_render_faq().
 */

get_header();
?>

<main id="main-content">
	<?php appiappi_breadcrumbs(); ?>
	<?php appiappi_page_header( __( "Answers to the questions we hear most from Canadian business owners.", 'appiappi' ) ); ?>

	<section class="section">
		<div class="container">
			<?php if ( shortcode_exists( 'appiappi_faq' ) ) : ?>
				<?php echo do_shortcode( '[appiappi_faq]' ); ?>
			<?php else : ?>
				<?php echo appiappi_render_faq( appiappi_get_faqs() ); ?>
			<?php endif; ?>
		</div>
	</section>

	<?php get_template_part( 'template-parts/sections/final-cta' ); ?>
</main>

<?php
get_footer();
