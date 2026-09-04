<?php
/**
 * Homepage hero. Uses the appiappi-hero-slider plugin's
 * [appiappi_hero_slider] shortcode when active; otherwise falls back to
 * a single static slide from appiappi_get_hero_slides(). Either way
 * rendered through the shared appiappi_render_hero_slides() so markup
 * never drifts between the two — see inc/template-tags.php. The rating
 * card never fabricates a review count — see appiappi_get_google_rating().
 */
?>
<section class="hero">
	<div class="container hero__inner">
		<?php if ( shortcode_exists( 'appiappi_hero_slider' ) ) : ?>
			<?php echo do_shortcode( '[appiappi_hero_slider]' ); ?>
		<?php else : ?>
			<?php echo appiappi_render_hero_slides( appiappi_get_hero_slides() ); ?>
		<?php endif; ?>
	</div>
</section>
