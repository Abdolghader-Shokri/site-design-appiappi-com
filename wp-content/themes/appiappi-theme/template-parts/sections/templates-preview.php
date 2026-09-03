<?php
/**
 * Homepage "Featured Website Designs" section — full sidebar + filter
 * layout matching design-reference/appiappiSimple.png. Data comes from
 * appiappi_get_featured_templates() / appiappi_get_template_categories() /
 * appiappi_get_template_styles() in inc/template-tags.php — all marked
 * TODO(Phase 3) to be replaced by the Template Showcase plugin's CPT +
 * taxonomy (see PROJECT_MASTER.md / MASTER_PROMPT.md).
 *
 * IMPORTANT: the sidebar (search box, category list, style checkboxes)
 * is presentational only right now — nothing here actually filters the
 * grid. Live filtering is real functionality that belongs to the future
 * Template Showcase plugin, not this placeholder theme section.
 */

$templates  = appiappi_get_featured_templates();
$categories = appiappi_get_template_categories();
$styles     = appiappi_get_template_styles();
?>
<section class="section section--subtle" id="templates">
	<div class="container">
		<div class="section-heading">
			<span class="section-heading__eyebrow"><?php esc_html_e( 'Website Designs', 'appiappi' ); ?></span>
			<h2><?php esc_html_e( 'Our Featured Website Designs', 'appiappi' ); ?></h2>
			<p><?php esc_html_e( 'Professionally selected designs for Canadian businesses — pick one as your starting point.', 'appiappi' ); ?></p>
		</div>

		<div class="templates-layout">
			<aside class="templates-sidebar">
				<div class="card templates-sidebar__card">
					<div class="templates-sidebar__search">
						<?php echo appiappi_icon( 'search' ); ?>
						<input type="search" placeholder="<?php esc_attr_e( 'Search templates…', 'appiappi' ); ?>" aria-label="<?php esc_attr_e( 'Search templates', 'appiappi' ); ?>">
					</div>

					<div class="templates-sidebar__group">
						<p class="templates-sidebar__title"><?php esc_html_e( 'Categories', 'appiappi' ); ?></p>
						<ul class="templates-sidebar__categories">
							<?php foreach ( $categories as $category ) : ?>
								<li class="<?php echo ! empty( $category['active'] ) ? 'is-active' : ''; ?>">
									<a href="#"><?php echo appiappi_icon( $category['icon'] ); ?><span><?php echo esc_html( $category['label'] ); ?></span></a>
								</li>
							<?php endforeach; ?>
						</ul>
					</div>

					<div class="templates-sidebar__group">
						<p class="templates-sidebar__title"><?php esc_html_e( 'Style', 'appiappi' ); ?></p>
						<?php foreach ( $styles as $style ) : ?>
							<label class="templates-sidebar__checkbox">
								<input type="checkbox"> <?php echo esc_html( $style ); ?>
							</label>
						<?php endforeach; ?>
					</div>
				</div>
			</aside>

			<div class="templates-main">
				<div class="templates-main__toolbar">
					<p class="templates-main__count">
						<?php
						printf(
							/* translators: %d number of designs shown */
							esc_html( _n( 'Showing %d design', 'Showing %d designs', count( $templates ), 'appiappi' ) ),
							count( $templates )
						);
						?>
					</p>
					<div class="templates-main__nav" aria-hidden="true">
						<button type="button" tabindex="-1"><?php echo appiappi_icon( 'chevron-right', '' ); ?></button>
					</div>
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
									<span class="template-card__rating">
										<?php echo appiappi_icon( 'star' ); ?> <?php echo esc_html( $template['rating'] ); ?>
										<?php if ( ! empty( $template['rating_count'] ) ) : ?>
											(<?php echo esc_html( $template['rating_count'] ); ?>)
										<?php endif; ?>
									</span>
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
		</div>
	</div>
</section>
