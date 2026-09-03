<?php
/**
 * Homepage pricing preview — reads appiappi_get_pricing_plans()
 * (see inc/template-tags.php for the Phase 2 CPT migration note).
 */

$plans = appiappi_get_pricing_plans();
?>
<section class="section" id="pricing">
	<div class="container">
		<div class="section-heading">
			<span class="section-heading__eyebrow"><?php esc_html_e( 'Plans', 'appiappi' ); ?></span>
			<h2><?php esc_html_e( 'Website Design & Support Plans', 'appiappi' ); ?></h2>
			<p><?php esc_html_e( 'Choose the plan that fits your business and let our team take it from there.', 'appiappi' ); ?></p>
		</div>

		<div class="pricing-grid">
			<?php foreach ( $plans as $plan ) : ?>
				<div class="pricing-card <?php echo ! empty( $plan['featured'] ) ? 'pricing-card--featured' : ''; ?>" style="--plan-color: <?php echo esc_attr( $plan['color'] ); ?>">
					<?php if ( ! empty( $plan['badge'] ) ) : ?>
						<span class="pricing-card__badge"><?php echo esc_html( $plan['badge'] ); ?></span>
					<?php endif; ?>

					<span class="pricing-card__icon"><?php echo appiappi_icon( $plan['icon'] ); ?></span>
					<h3 class="pricing-card__name"><?php echo esc_html( $plan['name'] ); ?></h3>

					<p class="pricing-card__price">
						<span class="pricing-card__price-amount">$<?php echo esc_html( $plan['price'] ); ?></span>
						<span class="pricing-card__price-period"><?php echo esc_html( $plan['period'] ); ?></span>
					</p>
					<p class="pricing-card__note"><?php echo esc_html( $plan['note'] ); ?></p>

					<ul class="pricing-card__features">
						<?php foreach ( $plan['features'] as $feature ) : ?>
							<li><?php echo appiappi_icon( 'check' ); ?><span><?php echo wp_kses_post( $feature ); ?></span></li>
						<?php endforeach; ?>
					</ul>

					<a href="<?php echo esc_url( $plan['cta_url'] ); ?>" class="btn <?php echo ! empty( $plan['featured'] ) ? 'btn-primary' : 'btn-secondary'; ?> btn-block">
						<?php echo esc_html( $plan['cta_text'] ); ?>
					</a>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
