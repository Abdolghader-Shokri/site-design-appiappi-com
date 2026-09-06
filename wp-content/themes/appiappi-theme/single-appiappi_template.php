<?php
/**
 * Website Design detail page (/templates/{slug}/). Uses
 * appiappi_showcase_map_post() from the appiappi-template-showcase
 * plugin so the same data shape/logic is used everywhere this design
 * appears (grid card, archive, here).
 *
 * "Choose This Design" starts the selection workflow (§ Website Template
 * Library selection workflow in MASTER_PROMPT.md): it links to the
 * Contact page with `?design=` (and a default recommended `?plan=`) —
 * the appiappi-contact plugin reads those and shows a "You selected…"
 * banner + carries them into the Lead. It intentionally does NOT
 * purchase or commit to anything by itself.
 *
 * Added 2026-09-06: the same Categories sidebar module shown on the
 * /templates/ archive (appiappi_showcase_get_categories(), reusing the
 * .templates-layout/.templates-sidebar markup so it's visually
 * identical, current design's category marked active), a "Back" button
 * (tries history.back() when the visitor actually came from this site,
 * falling back to a plain link to /templates/ otherwise — e.g. if they
 * arrived directly or JS is off), and the same decorative
 * page-header--templates background as the archive.
 */

get_header();

while ( have_posts() ) :
	the_post();
	$template   = function_exists( 'appiappi_showcase_map_post' ) ? appiappi_showcase_map_post( get_post() ) : null;
	$categories = function_exists( 'appiappi_showcase_get_categories' )
		? appiappi_showcase_get_categories( $template['category_slug'] ?? '', home_url( '/templates/' ) )
		: appiappi_get_template_categories();
	?>
	<main id="main-content">
		<?php appiappi_breadcrumbs(); ?>
		<header class="page-header page-header--templates">
			<div class="container">
				<?php if ( $template && $template['category'] ) : ?>
					<span class="badge badge-primary" style="margin-bottom: var(--space-3);"><?php echo esc_html( $template['category'] ); ?></span>
				<?php endif; ?>
				<h1><?php the_title(); ?></h1>
			</div>
		</header>

		<article class="section">
			<div class="container single-template-detail">
				<div class="templates-layout">
					<?php if ( $categories ) : ?>
						<aside class="templates-sidebar">
							<div class="card templates-sidebar__card">
								<div class="templates-sidebar__group">
									<p class="templates-sidebar__title"><?php esc_html_e( 'Categories', 'appiappi' ); ?></p>
									<ul class="templates-sidebar__categories">
										<?php foreach ( $categories as $category ) : ?>
											<li class="<?php echo ! empty( $category['active'] ) ? 'is-active' : ''; ?>">
												<a href="<?php echo esc_url( $category['url'] ?? '#' ); ?>"><?php echo appiappi_icon( $category['icon'] ); ?><span><?php echo esc_html( $category['label'] ); ?></span></a>
											</li>
										<?php endforeach; ?>
									</ul>
								</div>
							</div>
						</aside>
					<?php endif; ?>

					<div class="templates-main">
						<div class="single-post">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="single-post__media">
									<?php the_post_thumbnail( 'appiappi-hero' ); ?>
								</div>
							<?php endif; ?>

							<div class="single-post__content">
								<?php if ( get_the_content() ) : ?>
									<?php the_content(); ?>
								<?php elseif ( $template && $template['desc'] ) : ?>
									<p><?php echo esc_html( $template['desc'] ); ?></p>
								<?php endif; ?>

								<?php if ( $template ) : ?>
									<p>
										<?php if ( $template['rating'] ) : ?>
											<?php echo appiappi_render_star_rating( $template['rating'] ); ?> <?php echo esc_html( $template['rating'] ); ?>
											<?php if ( $template['rating_count'] ) : ?>(<?php echo esc_html( $template['rating_count'] ); ?>)<?php endif; ?>
											&nbsp;·&nbsp;
										<?php endif; ?>
										<?php if ( $template['price'] ) : ?>
											<strong><?php echo esc_html( $template['price'] ); ?></strong>
										<?php endif; ?>
									</p>

									<?php if ( $template['vendor'] ) : ?>
										<p class="description">
											<?php
											printf(
												/* translators: %s original theme vendor/marketplace name */
												esc_html__( 'Original design by %s. This is the starting point — we customize it with your logo, colours, content, services and SEO structure.', 'appiappi' ),
												esc_html( $template['vendor'] )
											);
											?>
											<?php if ( $template['source_url'] ) : ?>
												<a href="<?php echo esc_url( $template['source_url'] ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View original theme source', 'appiappi' ); ?></a>
											<?php endif; ?>
										</p>
									<?php else : ?>
										<p class="description"><?php esc_html_e( 'This is the starting design. We customize it with your logo, colours, content, services and SEO structure.', 'appiappi' ); ?></p>
									<?php endif; ?>
								<?php endif; ?>
							</div>

							<div class="hero__actions" style="margin-top: var(--space-8);">
								<a href="<?php echo esc_url( home_url( '/templates/' ) ); ?>" class="btn btn-secondary" onclick="if (window.history.length > 1) { window.history.back(); return false; }"><?php esc_html_e( 'Back', 'appiappi' ); ?></a>
								<?php if ( $template && $template['demo_url'] && '#' !== $template['demo_url'] ) : ?>
									<a href="<?php echo esc_url( $template['demo_url'] ); ?>" class="btn btn-secondary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Live Demo', 'appiappi' ); ?></a>
								<?php endif; ?>
								<a href="<?php echo esc_url( add_query_arg( array( 'design' => rawurlencode( get_the_title() ), 'plan' => 'professional' ), home_url( '/contact/' ) ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Choose This Design', 'appiappi' ); ?></a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</article>

		<?php get_template_part( 'template-parts/sections/final-cta' ); ?>
	</main>
	<?php
endwhile;

get_footer();
