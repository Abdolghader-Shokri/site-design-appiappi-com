<?php
/**
 * Template helper functions: nav fallback, inline icon set, and
 * placeholder content providers for sections whose real data source
 * (Custom Post Types) doesn't exist yet.
 *
 * IMPORTANT: appiappi_get_pricing_plans() and appiappi_get_featured_templates()
 * return hard-coded arrays ONLY as a temporary placeholder for Phase 1.
 * Per the project rule "no hard-coded business data", these MUST be
 * replaced with real queries once the Pricing (Phase 2) and Template
 * Library (Phase 3) custom post types exist. Every call site already
 * reads through these two functions, so swapping the data source later
 * requires no template changes — see PROJECT_MASTER.md.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Minimal inline icon set (no icon font/CDN dependency). Add new icons
 * here as needed; keep them simple, stroke-based, and on-brand.
 */
function appiappi_icon( $name, $class = '' ) {
	$icons = array(
		'monitor'    => '<rect x="3" y="4" width="18" height="12" rx="2"></rect><line x1="8" y1="20" x2="16" y2="20"></line><line x1="12" y1="16" x2="12" y2="20"></line>',
		'rocket'     => '<path d="M12 2c2.5 2 4 5.5 4 9 0 2.2-.8 4.2-2 5.6L12 19l-2-2.4C8.8 15.2 8 13.2 8 11c0-3.5 1.5-7 4-9z"></path><circle cx="12" cy="10" r="1.6"></circle><path d="M8.5 15.5l-2.5 3M15.5 15.5l2.5 3"></path>',
		'bar-chart'  => '<line x1="3" y1="20" x2="21" y2="20"></line><line x1="6" y1="20" x2="6" y2="12"></line><line x1="12" y1="20" x2="12" y2="7"></line><line x1="18" y1="20" x2="18" y2="15"></line>',
		'headset'    => '<path d="M4 13v-1a8 8 0 0 1 16 0v1"></path><rect x="3" y="13" width="4" height="6" rx="1.5"></rect><rect x="17" y="13" width="4" height="6" rx="1.5"></rect><path d="M20 19v1a3 3 0 0 1-3 3h-3"></path>',
		'shield'     => '<path d="M12 3l7 3v6c0 4.5-3 7.7-7 9-4-1.3-7-4.5-7-9V6l7-3z"></path><polyline points="9 12 11 14 15 10"></polyline>',
		'refresh'    => '<path d="M4 12a8 8 0 0 1 8-8c2.5 0 4.7 1.2 6 3"></path><polyline points="16 3 18 7 14 7"></polyline><path d="M20 12a8 8 0 0 1-8 8c-2.5 0-4.7-1.2-6-3"></path><polyline points="8 21 6 17 10 17"></polyline>',
		'trending-up'=> '<polyline points="3 17 9 11 13 15 21 6"></polyline><polyline points="15 6 21 6 21 12"></polyline>',
		'check'      => '<polyline points="5 13 10 18 19 7"></polyline>',
		'star'       => '<path d="M12 2.5l2.9 6.2 6.6.6-5 4.6 1.5 6.6L12 17l-5.9 3.5 1.5-6.6-5-4.6 6.6-.6z"></path>',
		'phone'      => '<rect x="7" y="2" width="10" height="20" rx="2"></rect><line x1="11" y1="18" x2="13" y2="18"></line>',
		'mail'       => '<rect x="3" y="5" width="18" height="14" rx="2"></rect><polyline points="3 7 12 13 21 7"></polyline>',
		'map-pin'    => '<path d="M12 22s7-7.4 7-12a7 7 0 1 0-14 0c0 4.6 7 12 7 12z"></path><circle cx="12" cy="10" r="2.5"></circle>',
		'leaf'       => '<path d="M12 2l1.3 5.8L18 5l-2 5 5 1-5 1.3 2 5-4.7-2.6L12 19l-1.3-4.3L6 17.3l2-5-5-1 5-1.3-2-5 4.7 2.8z" fill="currentColor" stroke="none"></path><line x1="12" y1="19" x2="12" y2="22"></line>',
		'briefcase'  => '<rect x="3" y="7" width="18" height="13" rx="2"></rect><path d="M8 7V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="3" y1="13" x2="21" y2="13"></line>',
		'home'       => '<path d="M4 11l8-7 8 7"></path><path d="M6 10v9a1 1 0 0 0 1 1h4v-6h2v6h4a1 1 0 0 0 1-1v-9"></path>',
		'heart'      => '<path d="M12 20s-7-4.5-9.5-9A5 5 0 0 1 12 6a5 5 0 0 1 9.5 5c-2.5 4.5-9.5 9-9.5 9z"></path>',
		'shopping-bag' => '<path d="M6 8h12l-1 12H7L6 8z"></path><path d="M9 8V6a3 3 0 0 1 6 0v2"></path>',
		'hammer'     => '<path d="M14 6l4 4-8 8-4-4 8-8z"></path><path d="M17 3l4 4-2 2-4-4 2-2z"></path><line x1="6" y1="18" x2="3" y2="21"></line>',
		'scale'      => '<line x1="12" y1="3" x2="12" y2="21"></line><path d="M5 7h14"></path><path d="M5 7l-3 6a3 3 0 0 0 6 0L5 7z"></path><path d="M19 7l-3 6a3 3 0 0 0 6 0l-3-6z"></path>',
		'grid'       => '<rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect>',
		'menu'       => '<line x1="4" y1="7" x2="20" y2="7"></line><line x1="4" y1="12" x2="20" y2="12"></line><line x1="4" y1="17" x2="20" y2="17"></line>',
		'close'      => '<line x1="6" y1="6" x2="18" y2="18"></line><line x1="18" y1="6" x2="6" y2="18"></line>',
		'search'     => '<circle cx="11" cy="11" r="7"></circle><line x1="21" y1="21" x2="16.5" y2="16.5"></line>',
		'chevron-right' => '<polyline points="9 6 15 12 9 18"></polyline>',
		'pencil'     => '<path d="M4 20l4.5-1 10-10a2.1 2.1 0 0 0-3-3l-10 10L4 20z"></path><line x1="14.5" y1="6.5" x2="17.5" y2="9.5"></line>',
		'diamond'    => '<path d="M6 9l3-6h6l3 6-6 12z"></path><line x1="3" y1="9" x2="21" y2="9"></line>',
		'crown'      => '<path d="M4 18h16l1.5-9-5 3-4.5-6-4.5 6-5-3L4 18z"></path><line x1="4" y1="21" x2="20" y2="21"></line>',
		'facebook'   => '<circle cx="12" cy="12" r="10"></circle><path d="M13.5 8.5h-1a1.5 1.5 0 0 0-1.5 1.5v1.5H9.5v2.5H11V19h2.5v-5h1.8l.3-2.5H13.5V10c0-.4.3-.7.7-.7h1.3V8.5z" fill="currentColor" stroke="none"></path>',
		'linkedin'   => '<rect x="2" y="2" width="20" height="20" rx="4"></rect><circle cx="7" cy="7.5" r="1.3" fill="currentColor" stroke="none"></circle><line x1="7" y1="11" x2="7" y2="17"></line><line x1="11" y1="11" x2="11" y2="17"></line><path d="M11 14a2.5 2.5 0 0 1 5 0v3"></path>',
		'instagram'  => '<rect x="2" y="2" width="20" height="20" rx="5"></rect><circle cx="12" cy="12" r="4.5"></circle><circle cx="17.2" cy="6.8" r="1" fill="currentColor" stroke="none"></circle>',
		'youtube'    => '<rect x="2" y="5" width="20" height="14" rx="4"></rect><polygon points="10 9 16 12 10 15" fill="currentColor" stroke="none"></polygon>',
	);

	if ( ! isset( $icons[ $name ] ) ) {
		return '';
	}

	return sprintf(
		'<svg class="icon %s" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%s</svg>',
		esc_attr( $class ),
		$icons[ $name ]
	);
}

/**
 * Primary nav fallback, shown until a real menu is assigned in
 * Appearance > Menus. Targets are best-guess slugs for pages that
 * Phase 2/3 will create.
 */
function appiappi_nav_fallback( $args ) {
	$items = array(
		'/'             => __( 'Home', 'appiappi' ),
		'/templates/'   => __( 'Website Designs', 'appiappi' ),
		'/services/'    => __( 'Services', 'appiappi' ),
		'/how-it-works/'=> __( 'How It Works', 'appiappi' ),
		'/portfolio/'   => __( 'Portfolio', 'appiappi' ),
		'/pricing/'     => __( 'Pricing', 'appiappi' ),
		'/about/'       => __( 'About', 'appiappi' ),
		'/contact/'     => __( 'Contact', 'appiappi' ),
	);

	$container_class = isset( $args->menu_class ) ? $args->menu_class : '';
	echo '<ul class="' . esc_attr( $container_class ) . '">';
	foreach ( $items as $url => $label ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( home_url( $url ) ), esc_html( $label ) );
	}
	echo '</ul>';
}

/**
 * TODO(Phase 2): replace with a query against the Pricing Plan CPT so
 * the business owner can add/edit/reorder/hide plans from wp-admin.
 */
function appiappi_get_pricing_plans() {
	return array(
		array(
			'id'       => 'starter',
			'icon'     => 'rocket',
			'name'     => __( 'Starter', 'appiappi' ),
			'price'    => '199',
			'period'   => __( 'one-time', 'appiappi' ),
			'note'     => __( 'Launch a new site fast.', 'appiappi' ),
			'color'    => 'var(--color-plan-starter)',
			'featured' => false,
			'features' => array(
				__( 'WordPress installation', 'appiappi' ),
				__( 'Theme &amp; demo install', 'appiappi' ),
				__( 'SSL &amp; domain connection', 'appiappi' ),
				__( 'Essential plugin setup', 'appiappi' ),
			),
			'cta_text' => __( 'Choose Starter', 'appiappi' ),
			'cta_url'  => '#contact',
		),
		array(
			'id'       => 'business',
			'icon'     => 'pencil',
			'name'     => __( 'Business', 'appiappi' ),
			'price'    => '399',
			'period'   => __( 'one-time', 'appiappi' ),
			'note'     => __( 'Fully customized for your business.', 'appiappi' ),
			'color'    => 'var(--color-plan-business)',
			'featured' => false,
			'features' => array(
				__( 'Everything in Starter', 'appiappi' ),
				__( 'Full theme customization', 'appiappi' ),
				__( 'Content &amp; contact forms', 'appiappi' ),
				__( 'Basic on-page SEO', 'appiappi' ),
			),
			'cta_text' => __( 'Choose Business', 'appiappi' ),
			'cta_url'  => '#contact',
		),
		array(
			'id'       => 'professional',
			'icon'     => 'diamond',
			'name'     => __( 'Professional', 'appiappi' ),
			'price'    => '699',
			'period'   => __( 'one-time', 'appiappi' ),
			'note'     => __( 'Premium theme + 1 year hosting.', 'appiappi' ),
			'color'    => 'var(--color-plan-professional)',
			'featured' => true,
			'badge'    => __( 'Most Popular', 'appiappi' ),
			'features' => array(
				__( 'Everything in Business', 'appiappi' ),
				__( 'Premium theme license included', 'appiappi' ),
				__( '1 year managed hosting', 'appiappi' ),
				__( 'Custom logo &amp; speed/security setup', 'appiappi' ),
			),
			'cta_text' => __( 'Choose Professional', 'appiappi' ),
			'cta_url'  => '#contact',
		),
		array(
			'id'       => 'growth',
			'icon'     => 'crown',
			'name'     => __( 'Growth', 'appiappi' ),
			'price'    => '599',
			'period'   => __( '/ month', 'appiappi' ),
			'note'     => __( 'Ongoing management &amp; SEO.', 'appiappi' ),
			'color'    => 'var(--color-plan-growth)',
			'featured' => false,
			'features' => array(
				__( 'Managed hosting &amp; maintenance', 'appiappi' ),
				__( 'Ongoing on-page &amp; technical SEO', 'appiappi' ),
				__( 'Content updates', 'appiappi' ),
				__( 'Monthly SEO reporting', 'appiappi' ),
			),
			'cta_text' => __( 'Choose Growth', 'appiappi' ),
			'cta_url'  => '#contact',
		),
	);
}

/**
 * TODO(Phase 3): replace with a query against the Website Template CPT
 * (with taxonomy for industry/category) so designs can be managed from
 * wp-admin without touching code.
 */
function appiappi_get_featured_templates() {
	return array(
		array(
			'name'     => __( 'Construction Pro', 'appiappi' ),
			'category' => __( 'Construction', 'appiappi' ),
			'style'    => __( 'Bold', 'appiappi' ),
			'desc'     => __( 'Bold, image-led design for builders and contractors.', 'appiappi' ),
			'price'    => '$59',
			'rating'   => '4.9',
			'rating_count' => 128,
			'image'    => '',
			'demo_url'    => '#',
			'details_url' => '#',
		),
		array(
			'name'     => __( 'Justice Law', 'appiappi' ),
			'category' => __( 'Legal', 'appiappi' ),
			'style'    => __( 'Classic', 'appiappi' ),
			'desc'     => __( 'Sharp, trustworthy design for law firms and consultants.', 'appiappi' ),
			'price'    => '$69',
			'rating'   => '4.8',
			'rating_count' => 96,
			'image'    => '',
			'demo_url'    => '#',
			'details_url' => '#',
		),
		array(
			'name'     => __( 'Dental Clinic', 'appiappi' ),
			'category' => __( 'Dental &amp; Medical', 'appiappi' ),
			'style'    => __( 'Modern', 'appiappi' ),
			'desc'     => __( 'Warm, clean design for clinics and healthcare practices.', 'appiappi' ),
			'price'    => '$49',
			'rating'   => '4.9',
			'rating_count' => 87,
			'image'    => '',
			'demo_url'    => '#',
			'details_url' => '#',
		),
	);
}

/**
 * TODO(Phase 3): sidebar category list — presentational only until the
 * Template Showcase plugin's `appiappi_template_category` taxonomy exists.
 * Clicking a category does not yet filter results.
 */
function appiappi_get_template_categories() {
	return array(
		array( 'icon' => 'grid',          'label' => __( 'All Categories', 'appiappi' ), 'active' => true ),
		array( 'icon' => 'hammer',        'label' => __( 'Construction & Contracting', 'appiappi' ) ),
		array( 'icon' => 'scale',         'label' => __( 'Legal', 'appiappi' ) ),
		array( 'icon' => 'heart',         'label' => __( 'Dental & Medical', 'appiappi' ) ),
		array( 'icon' => 'home',          'label' => __( 'Real Estate', 'appiappi' ) ),
		array( 'icon' => 'shopping-bag',  'label' => __( 'Restaurant & Retail', 'appiappi' ) ),
		array( 'icon' => 'briefcase',     'label' => __( 'Professional Services', 'appiappi' ) ),
	);
}

/**
 * TODO(Phase 3): sidebar style checkboxes — presentational only, same
 * caveat as appiappi_get_template_categories().
 */
function appiappi_get_template_styles() {
	return array(
		__( 'Modern', 'appiappi' ),
		__( 'Minimal', 'appiappi' ),
		__( 'Bold', 'appiappi' ),
		__( 'Classic', 'appiappi' ),
	);
}

/**
 * TODO(Phase 1 follow-up): wire to a real Google review count once the
 * business has reviews. Returns null (renders as a neutral placeholder)
 * until then — never fabricate a rating. See DEVELOPMENT_LOG.md.
 */
function appiappi_get_google_rating() {
	return null;
}

/**
 * Shared pricing-card markup. Renders a `.pricing-grid` of cards from an
 * array shaped like appiappi_get_pricing_plans()'s return value.
 *
 * This is the single source of truth for pricing-card HTML: the theme's
 * own placeholder (template-parts/sections/pricing-preview.php) and the
 * appiappi-pricing-plans plugin's [appiappi_pricing] shortcode both call
 * this function with their own data, so the markup never has to be kept
 * in sync in two places. See PROJECT_MASTER.md § Pricing System.
 *
 * @param array $plans Each item: id, icon, name, price, period, note,
 *                      color (a CSS colour/var()), featured (bool),
 *                      badge, features (array of strings), cta_text,
 *                      cta_url.
 */
function appiappi_render_pricing_cards( array $plans ) {
	if ( empty( $plans ) ) {
		return '';
	}

	ob_start();
	?>
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
	<?php
	return ob_get_clean();
}

/**
 * Shared template-showcase markup (sidebar + grid). Same pattern as
 * appiappi_render_pricing_cards(): the theme's own placeholder and the
 * appiappi-template-showcase plugin's [appiappi_templates] shortcode
 * both build a data array in this shape and render through this one
 * function, so markup never drifts between the two.
 *
 * @param array $templates  Each item: name, category, style, desc,
 *                           price, rating, rating_count, image (URL or
 *                           empty), demo_url, details_url.
 * @param array $categories Each item: icon, label, active (bool),
 *                           optional url.
 * @param array $styles       Array of style label strings.
 * @param bool  $show_sidebar Whether to render the filter sidebar at all
 *                             (a shortcode instance placed in a narrow
 *                             spot can opt out and show just the grid).
 */
function appiappi_render_template_showcase( array $templates, array $categories, array $styles, $show_sidebar = true ) {
	ob_start();
	?>
	<div class="templates-layout <?php echo $show_sidebar ? '' : 'templates-layout--no-sidebar'; ?>">
		<?php if ( $show_sidebar ) : ?>
		<aside class="templates-sidebar">
			<div class="card templates-sidebar__card">
				<div class="templates-sidebar__search">
					<?php echo appiappi_icon( 'search' ); ?>
					<input type="search" placeholder="<?php esc_attr_e( 'Search templates…', 'appiappi' ); ?>" aria-label="<?php esc_attr_e( 'Search templates', 'appiappi' ); ?>">
				</div>

				<?php if ( $categories ) : ?>
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
				<?php endif; ?>

				<?php if ( $styles ) : ?>
					<div class="templates-sidebar__group">
						<p class="templates-sidebar__title"><?php esc_html_e( 'Style', 'appiappi' ); ?></p>
						<?php foreach ( $styles as $style ) : ?>
							<label class="templates-sidebar__checkbox">
								<input type="checkbox"> <?php echo esc_html( $style ); ?>
							</label>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</aside>
		<?php endif; ?>

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

			<?php if ( empty( $templates ) ) : ?>
				<p><?php esc_html_e( 'No website designs published yet.', 'appiappi' ); ?></p>
			<?php else : ?>
				<div class="template-grid">
					<?php foreach ( $templates as $template ) : ?>
						<div class="card template-card">
							<div class="template-card__media">
								<?php if ( ! empty( $template['image'] ) ) : ?>
									<img src="<?php echo esc_url( $template['image'] ); ?>" alt="<?php echo esc_attr( $template['name'] ); ?>" loading="lazy">
								<?php endif; ?>
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
			<?php endif; ?>

			<div class="templates-preview__footer">
				<a href="<?php echo esc_url( home_url( '/templates/' ) ); ?>" class="btn btn-secondary">
					<?php esc_html_e( 'Browse All Designs', 'appiappi' ); ?> <?php echo appiappi_icon( 'chevron-right' ); ?>
				</a>
			</div>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
