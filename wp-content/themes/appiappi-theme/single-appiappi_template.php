<?php
/**
 * Website Design detail page (/templates/{slug}/). Uses
 * appiappi_showcase_map_post() from the appiappi-template-showcase
 * plugin so the same data shape/logic is used everywhere this design
 * appears (grid card, archive, here).
 *
 * "Choose This Design" starts the checkout flow (added 2026-09-06,
 * appiappi-checkout plugin): it links to the Pricing page with
 * `?design_id={post ID}` — that page adds the design's real price
 * (looked up server-side from the ID, never trusted from the URL) as a
 * line item on every plan, and each plan's own button opens the Stripe
 * checkout modal already carrying that design along.
 *
 * Added 2026-09-06: the same Categories sidebar module shown on the
 * /templates/ archive (appiappi_showcase_get_categories(), reusing the
 * .templates-layout/.templates-sidebar markup so it's visually
 * identical, current design's category marked active), a "Back" button
 * (tries history.back() when the visitor actually came from this site,
 * falling back to a plain link to /templates/ otherwise — e.g. if they
 * arrived directly or JS is off), and its own decorative page-header
 * background (page-header--template-single, configured separately from
 * the archive's under Customizer → Page Header Backgrounds — "Website
 * Design — Single Design Page").
 *
 * Revised 2026-09-06: the content column left a large empty gap on the
 * right at desktop widths (it was capped at 760px for readability inside
 * a much wider main column). Rather than just widening the text, that
 * space now holds a `.template-summary` box — rating, short description,
 * price, the action buttons (moved here from a row below the content),
 * and an admin-editable "can't find your design?" note (Customizer →
 * Website Design — Single Page) — sitting beside the media+content
 * column as a sticky sidebar (`.single-post` becomes a 2-column grid at
 * ≥1024px). Below that breakpoint everything is a single column again,
 * and DOM order (media, then the summary box, then the content) puts the
 * summary box right under the featured image and above the description,
 * as requested. The media area also gained the same prev/next image
 * carousel the grid cards use (`.template-card__media`/`__image`/`__nav`,
 * driven by the same main.js IIFE via `data-carousel-interval`) — the
 * single page previously only ever showed the featured image, not the
 * rest of a multi-image design's gallery.
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
		<header class="page-header page-header--template-single">
			<?php appiappi_page_header_network_canvas( 'template-single' ); ?>
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
							<?php
							$gallery_images     = ( $template && ! empty( $template['images'] ) ) ? $template['images'] : array_filter( array( $template['image'] ?? '' ) );
							$carousel_interval  = (int) get_option( 'appiappi_templates_carousel_interval', 3000 );
							?>
							<?php if ( $gallery_images ) : ?>
								<div class="single-post__media template-card__media"<?php echo count( $gallery_images ) > 1 ? ' data-carousel-interval="' . esc_attr( $carousel_interval ) . '"' : ''; ?>>
									<?php foreach ( $gallery_images as $index => $image_url ) : ?>
										<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" class="template-card__image <?php echo 0 === $index ? 'is-active' : ''; ?>" data-carousel-slide="<?php echo esc_attr( $index ); ?>">
									<?php endforeach; ?>
									<?php if ( count( $gallery_images ) > 1 ) : ?>
										<button type="button" class="template-card__nav template-card__nav--prev" data-carousel-prev aria-label="<?php esc_attr_e( 'Previous image', 'appiappi' ); ?>"><?php echo appiappi_icon( 'chevron-left' ); ?></button>
										<button type="button" class="template-card__nav template-card__nav--next" data-carousel-next aria-label="<?php esc_attr_e( 'Next image', 'appiappi' ); ?>"><?php echo appiappi_icon( 'chevron-right' ); ?></button>
									<?php endif; ?>
								</div>
							<?php endif; ?>

							<?php if ( $template ) : ?>
								<aside class="template-summary card">
									<?php if ( $template['rating'] ) : ?>
										<p class="template-summary__rating">
											<?php echo appiappi_render_star_rating( $template['rating'] ); ?>
											<span><?php echo esc_html( $template['rating'] ); ?><?php if ( $template['rating_count'] ) : ?> (<?php echo esc_html( $template['rating_count'] ); ?>)<?php endif; ?></span>
										</p>
									<?php endif; ?>

									<?php if ( $template['desc'] ) : ?>
										<p class="template-summary__desc"><?php echo esc_html( $template['desc'] ); ?></p>
									<?php endif; ?>

									<?php if ( $template['price'] ) : ?>
										<p class="template-summary__price"><?php echo esc_html( $template['price'] ); ?></p>
									<?php endif; ?>

									<div class="template-summary__actions">
										<a href="<?php echo esc_url( home_url( '/templates/' ) ); ?>" class="btn btn-secondary" onclick="if (window.history.length > 1) { window.history.back(); return false; }"><?php esc_html_e( 'Back', 'appiappi' ); ?></a>
										<?php if ( $template['demo_url'] && '#' !== $template['demo_url'] ) : ?>
											<a href="<?php echo esc_url( $template['demo_url'] ); ?>" class="btn btn-secondary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Live Demo', 'appiappi' ); ?></a>
										<?php endif; ?>
										<a href="<?php echo esc_url( add_query_arg( array( 'design_id' => (int) $template['id'] ), home_url( '/pricing/' ) ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Choose This Design', 'appiappi' ); ?></a>
									</div>

									<?php $missing_note = get_theme_mod( 'appiappi_template_missing_note', appiappi_default_missing_design_note() ); ?>
									<?php if ( $missing_note ) : ?>
										<p class="template-summary__note"><?php echo wp_kses_post( $missing_note ); ?></p>
									<?php endif; ?>
								</aside>
							<?php endif; ?>

							<div class="single-post__content">
								<?php if ( get_the_content() ) : ?>
									<?php the_content(); ?>
								<?php elseif ( $template && $template['desc'] ) : ?>
									<p><?php echo esc_html( $template['desc'] ); ?></p>
								<?php endif; ?>

								<?php if ( $template ) : ?>
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
