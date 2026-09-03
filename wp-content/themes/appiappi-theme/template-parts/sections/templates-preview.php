<?php
/**
 * Homepage "Featured Website Designs" teaser — reads
 * appiappi_get_featured_templates() (see inc/template-tags.php for the
 * Phase 3 CPT migration note). The full browsable library with search
 * and filters lives on the dedicated Templates archive page (Phase 3).
 */

$templates = appiappi_get_featured_templates();
?>
<section class="section section--subtle" id="templates">
	<div class="container">
		<div class="section-heading">
			<span class="section-heading__eyebrow"><?php esc_html_e( 'Website Designs', 'appiappi' ); ?></span>
			<h2><?php esc_html_e( 'Our Featured Website Designs', 'appiappi' ); ?></h2>
			<p><?php esc_html_e( 'Professionally selected designs for Canadian businesses — pick one as your starting point.', 'appiappi' ); ?></p>
		</div>

		<div class="template-grid">
			<?php foreach ( $templates as $template ) : ?>
				<div class="card template-card">
					<div class="template-card__media">
						<span class="badge badge-dark template-card__category"><?php echo esc_html( $template['category'] ); ?></span>
					</div>
					<div class="template-card__body">
						<h3 class="template-card__name"><?php echo esc_html( $template['name'] ); ?></h3>
						<p class="template-card__desc"><?php echo esc_html( $template['desc'] ); ?></p>
						<div class="template-card__meta">
							<span class="template-card__price"><?php echo esc_html( $template['price'] ); ?></span>
							<span class="template-card__rating"><?php echo appiappi_icon( 'star' ); ?> <?php echo esc_html( $template['rating'] ); ?></span>
						</div>
						<div class="template-card__actions">
							<a href="<?php echo esc_url( $template['details_url'] ); ?>" class="btn btn-secondary btn-sm"><?php esc_html_e( 'View Details', 'appiappi' ); ?></a>
							<a href="<?php echo esc_url( $template['demo_url'] ); ?>" class="btn btn-primary btn-sm"><?php esc_html_e( 'Live Demo', 'appiappi' ); ?></a>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<div class="templates-preview__footer">
			<a href="<?php echo esc_url( home_url( '/templates/' ) ); ?>" class="btn btn-secondary">
				<?php esc_html_e( 'Browse All Designs', 'appiappi' ); ?> <?php echo appiappi_icon( 'chevron-right' ); ?>
			</a>
		</div>
	</div>
</section>
