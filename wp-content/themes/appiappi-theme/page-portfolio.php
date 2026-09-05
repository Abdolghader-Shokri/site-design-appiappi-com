<?php
/**
 * Template Name: Portfolio Page
 * Auto-applies to a page with the slug "portfolio". Uses the
 * appiappi-portfolio plugin's [appiappi_portfolio] shortcode when
 * active, else the theme's placeholder — both via
 * appiappi_render_portfolio_grid().
 */

get_header();
?>

<main id="main-content">
	<?php appiappi_breadcrumbs(); ?>
	<?php appiappi_page_header( __( 'A look at websites we have designed, launched and managed for Canadian businesses.', 'appiappi' ) ); ?>

	<section class="section">
		<div class="container">
			<?php if ( shortcode_exists( 'appiappi_portfolio' ) ) : ?>
				<?php echo do_shortcode( '[appiappi_portfolio count="9"]' ); ?>
			<?php else : ?>
				<?php echo appiappi_render_portfolio_grid( appiappi_get_portfolio_projects() ); ?>
			<?php endif; ?>
		</div>
	</section>

	<?php get_template_part( 'template-parts/sections/final-cta' ); ?>
</main>

<?php
get_footer();
