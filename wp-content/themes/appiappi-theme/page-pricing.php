<?php
/**
 * Template Name: Pricing Page
 * Auto-applies to a page with the slug "pricing". Reuses the same
 * pricing-card markup/data source as the homepage preview
 * (appiappi_render_pricing_cards() + the [appiappi_pricing] shortcode)
 * plus the FAQ accordion, per MASTER_PROMPT.md § Pricing Page.
 */

get_header();
?>

<main id="main-content">
	<?php appiappi_page_header( __( 'Compare plans and find the right fit for your business — from a one-time launch to ongoing growth.', 'appiappi' ) ); ?>

	<section class="section" id="pricing">
		<div class="container">
			<?php if ( shortcode_exists( 'appiappi_pricing' ) ) : ?>
				<?php echo do_shortcode( '[appiappi_pricing]' ); ?>
			<?php else : ?>
				<?php echo appiappi_render_pricing_cards( appiappi_get_pricing_plans() ); ?>
			<?php endif; ?>
		</div>
	</section>

	<section class="section section--subtle">
		<div class="container">
			<div class="section-heading">
				<span class="section-heading__eyebrow"><?php esc_html_e( 'FAQ', 'appiappi' ); ?></span>
				<h2><?php esc_html_e( 'Pricing Questions', 'appiappi' ); ?></h2>
			</div>
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
