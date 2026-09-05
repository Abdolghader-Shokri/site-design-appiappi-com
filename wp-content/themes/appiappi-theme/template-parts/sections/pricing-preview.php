<?php
/**
 * Homepage pricing section. Uses the appiappi-pricing-plans plugin's
 * [appiappi_pricing] shortcode when that plugin is active; otherwise
 * falls back to appiappi_get_pricing_plans() placeholder data. Either
 * way the cards are drawn by the shared appiappi_render_pricing_cards()
 * so markup never drifts between the two — see inc/template-tags.php.
 */
?>
<section class="section" id="pricing">
	<div class="container">
		<div class="section-heading">
			<span class="section-heading__eyebrow"><?php esc_html_e( 'Plans', 'appiappi' ); ?></span>
			<h2><?php esc_html_e( 'Website Design & Support Plans', 'appiappi' ); ?></h2>
			<p><?php esc_html_e( 'Choose the plan that fits your business and let our team take it from there.', 'appiappi' ); ?></p>
		</div>

		<?php if ( shortcode_exists( 'appiappi_pricing' ) ) : ?>
			<?php echo do_shortcode( '[appiappi_pricing homepage_only="1"]' ); ?>
		<?php else : ?>
			<?php echo appiappi_render_pricing_cards( appiappi_get_pricing_plans( true ) ); ?>
		<?php endif; ?>
	</div>
</section>
