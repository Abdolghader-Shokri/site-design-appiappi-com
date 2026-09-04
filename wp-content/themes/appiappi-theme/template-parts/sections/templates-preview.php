<?php
/**
 * Homepage "Featured Website Designs" section. Uses the
 * appiappi-template-showcase plugin's [appiappi_templates] shortcode
 * when that plugin is active; otherwise falls back to
 * appiappi_get_featured_templates() / appiappi_get_template_categories() /
 * appiappi_get_template_styles() placeholder data. Either way the
 * sidebar + grid are drawn by the shared
 * appiappi_render_template_showcase() so markup never drifts between
 * the two — see inc/template-tags.php.
 *
 * The category links in the sidebar work via a plain ?appiappi_category=
 * query string + a full page reload (read below, passed to the
 * shortcode's `category` attribute) — real filtering, no JS needed. The
 * style checkboxes and the search box are still visual-only; live/AJAX
 * filtering is out of scope for this pass.
 */
$appiappi_category_filter = isset( $_GET['appiappi_category'] ) ? sanitize_title( wp_unslash( $_GET['appiappi_category'] ) ) : '';
?>
<section class="section section--subtle" id="templates">
	<div class="container">
		<div class="section-heading">
			<span class="section-heading__eyebrow"><?php esc_html_e( 'Website Designs', 'appiappi' ); ?></span>
			<h2><?php esc_html_e( 'Our Featured Website Designs', 'appiappi' ); ?></h2>
			<p><?php esc_html_e( 'Professionally selected designs for Canadian businesses — pick one as your starting point.', 'appiappi' ); ?></p>
		</div>

		<?php if ( shortcode_exists( 'appiappi_templates' ) ) : ?>
			<?php echo do_shortcode( sprintf( '[appiappi_templates category="%s"]', esc_attr( $appiappi_category_filter ) ) ); ?>
		<?php else : ?>
			<?php
			echo appiappi_render_template_showcase(
				appiappi_get_featured_templates(),
				appiappi_get_template_categories(),
				appiappi_get_template_styles()
			);
			?>
		<?php endif; ?>
	</div>
</section>
