<?php
/**
 * Homepage hero: headline, feature chips, CTAs, visual + rating card.
 * The rating card never fabricates a review count — see
 * appiappi_get_google_rating() in inc/template-tags.php.
 */

$rating = appiappi_get_google_rating();

$chips = array(
	array( 'icon' => 'monitor',   'label' => __( 'Professional Templates', 'appiappi' ) ),
	array( 'icon' => 'rocket',    'label' => __( 'Fast Launch', 'appiappi' ) ),
	array( 'icon' => 'bar-chart', 'label' => __( 'SEO & Optimization', 'appiappi' ) ),
	array( 'icon' => 'headset',   'label' => __( 'Daily Support', 'appiappi' ) ),
);
?>
<section class="hero">
	<div class="container hero__inner">
		<div class="hero__content">
			<span class="hero__eyebrow"><?php echo appiappi_icon( 'leaf', '' ); ?> <?php esc_html_e( 'Canadian Web Design & SEO', 'appiappi' ); ?></span>
			<h1 class="hero__title"><?php esc_html_e( 'Your Website. Professionally Managed. Every Day.', 'appiappi' ); ?></h1>
			<p class="hero__lede"><?php esc_html_e( 'Get a professionally designed website, managed hosting, ongoing SEO, content updates and dedicated support — all from one trusted Canadian team.', 'appiappi' ); ?></p>

			<ul class="chip-list">
				<?php foreach ( $chips as $chip ) : ?>
					<li class="chip">
						<span class="chip__icon"><?php echo appiappi_icon( $chip['icon'] ); ?></span>
						<span class="chip__label"><?php echo esc_html( $chip['label'] ); ?></span>
					</li>
				<?php endforeach; ?>
			</ul>

			<div class="hero__actions">
				<a href="<?php echo esc_url( home_url( '/templates/' ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Explore Website Designs', 'appiappi' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" class="btn btn-secondary"><?php esc_html_e( 'View Our Plans', 'appiappi' ); ?></a>
			</div>
		</div>

		<div class="hero__visual">
			<div class="hero__visual-frame">
				<img src="<?php echo esc_url( get_template_directory_uri() . '/assets/images/hero-placeholder.svg' ); ?>" alt="<?php esc_attr_e( 'Canadian city skyline', 'appiappi' ); ?>" loading="eager" fetchpriority="high">
			</div>

			<div class="rating-card <?php echo $rating ? '' : 'rating-card--placeholder'; ?>">
				<span class="rating-card__icon" aria-hidden="true"><?php echo appiappi_icon( 'star' ); ?></span>
				<span>
					<span class="rating-card__score"><?php echo $rating ? esc_html( $rating['score'] ) : esc_html__( '—', 'appiappi' ); ?></span>
					<span class="rating-card__stars" aria-hidden="true">★★★★★</span>
					<span class="rating-card__caption">
						<?php
						echo $rating
							? esc_html( sprintf( /* translators: %d review count */ __( 'Based on %d+ reviews', 'appiappi' ), $rating['count'] ) )
							: esc_html__( 'Google Reviews — coming soon', 'appiappi' );
						?>
					</span>
				</span>
			</div>
		</div>
	</div>
</section>
