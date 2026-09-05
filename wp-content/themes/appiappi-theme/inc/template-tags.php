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
function appiappi_get_pricing_plans( $homepage_only = false ) {
	$plans = array(
		array(
			'id'       => 'starter',
			'icon'     => 'rocket',
			'name'     => __( 'Starter', 'appiappi' ),
			'tagline'  => __( 'Get Online, Fast.', 'appiappi' ),
			'audience' => __( 'Perfect for new businesses that need a professional online presence right away.', 'appiappi' ),
			'value_driver' => __( 'Get online in days, not weeks — without cutting corners on quality.', 'appiappi' ),
			'group'    => 'launch',
			'homepage_visible' => true,
			'price'    => '199',
			'period'   => __( 'one-time', 'appiappi' ),
			'note'     => __( 'Your professional foundation, live fast.', 'appiappi' ),
			'color'    => 'var(--color-plan-starter)',
			'featured' => false,
			'features' => array(
				__( 'Professional WordPress Environment Setup', 'appiappi' ),
				__( 'Curated Theme &amp; Demo Content Installation', 'appiappi' ),
				__( 'SSL Encryption &amp; Secure Domain Connection', 'appiappi' ),
				__( 'Essential Plugin Configuration', 'appiappi' ),
				__( 'Launch-Ready Technical Foundation', 'appiappi' ),
			),
			'cta_text' => __( 'Choose Starter', 'appiappi' ),
			'cta_url'  => '#contact',
		),
		array(
			'id'       => 'business',
			'icon'     => 'pencil',
			'name'     => __( 'Business', 'appiappi' ),
			'tagline'  => __( 'Your Brand, Fully Realized.', 'appiappi' ),
			'audience' => __( 'Perfect for established businesses ready to fully align their website with their brand.', 'appiappi' ),
			'value_driver' => __( 'Move beyond a generic template — launch a site that actually represents your business.', 'appiappi' ),
			'group'    => 'launch',
			'homepage_visible' => true,
			'price'    => '399',
			'period'   => __( 'one-time', 'appiappi' ),
			'note'     => __( 'A website that works as hard as you do.', 'appiappi' ),
			'color'    => 'var(--color-plan-business)',
			'featured' => false,
			'features' => array(
				__( 'Everything in Starter', 'appiappi' ),
				__( 'Full Brand &amp; Visual Customization', 'appiappi' ),
				__( 'Strategic Content Placement — Services, About, Contact', 'appiappi' ),
				__( 'Custom Navigation &amp; Site Architecture', 'appiappi' ),
				__( 'Client-Provided Asset Integration — Photos, Copy, Brand Colours', 'appiappi' ),
				__( 'Foundational On-Page SEO Structure', 'appiappi' ),
			),
			'cta_text' => __( 'Choose Business', 'appiappi' ),
			'cta_url'  => '#contact',
		),
		array(
			'id'       => 'professional',
			'icon'     => 'diamond',
			'name'     => __( 'Professional', 'appiappi' ),
			'tagline'  => __( 'The Premium Standard.', 'appiappi' ),
			'audience' => __( 'Perfect for businesses that want a high-performance launch with a full year of hosting included.', 'appiappi' ),
			'value_driver' => __( 'Launch faster, rank better, and skip a full year of hosting decisions entirely.', 'appiappi' ),
			'group'    => 'launch',
			'homepage_visible' => true,
			'price'    => '699',
			'period'   => __( 'one-time', 'appiappi' ),
			'note'     => __( 'Performance-driven, fully managed from day one.', 'appiappi' ),
			'color'    => 'var(--color-plan-professional)',
			'featured' => true,
			'badge'    => __( 'Most Popular', 'appiappi' ),
			'features' => array(
				__( 'Everything in Business', 'appiappi' ),
				__( 'Premium Theme License Included', 'appiappi' ),
				__( '1 Year of Managed, High-Performance Hosting', 'appiappi' ),
				__( 'Custom Logo &amp; Visual Identity Design', 'appiappi' ),
				__( 'Advanced Speed &amp; Core Web Vitals Optimisation', 'appiappi' ),
				__( 'Security Hardening &amp; Automated Backup Configuration', 'appiappi' ),
				__( 'Search Console &amp; Analytics Integration', 'appiappi' ),
			),
			'cta_text' => __( 'Choose Professional', 'appiappi' ),
			'cta_url'  => '#contact',
		),
		array(
			'id'       => 'growth',
			'icon'     => 'crown',
			'name'     => __( 'Growth', 'appiappi' ),
			'tagline'  => __( 'Always-On Protection &amp; Performance.', 'appiappi' ),
			'audience' => __( 'Perfect for businesses that want their website professionally maintained without lifting a finger.', 'appiappi' ),
			'value_driver' => __( 'Eliminate downtime risk and technical debt — for one predictable monthly investment.', 'appiappi' ),
			'group'    => 'growth',
			'homepage_visible' => true,
			'price'    => '599',
			'period'   => __( '/ month', 'appiappi' ),
			'note'     => __( 'Your website, managed and monitored every month.', 'appiappi' ),
			'color'    => 'var(--color-plan-growth)',
			'featured' => false,
			'features' => array(
				__( 'Fully Managed Hosting &amp; Uptime Monitoring', 'appiappi' ),
				__( 'Proactive Security Monitoring &amp; Malware Scanning', 'appiappi' ),
				__( 'Automated Software &amp; Plugin Updates', 'appiappi' ),
				__( 'Scheduled Backups with Tested Restore Points', 'appiappi' ),
				__( 'Ongoing Content &amp; Website Changes', 'appiappi' ),
				__( 'Foundational Monthly SEO Monitoring', 'appiappi' ),
			),
			'cta_text' => __( 'Choose Growth', 'appiappi' ),
			'cta_url'  => '#contact',
		),
		array(
			'id'       => 'seo-growth',
			'icon'     => 'trending-up',
			'name'     => __( 'SEO Growth', 'appiappi' ),
			'tagline'  => __( 'Aggressive Organic Growth.', 'appiappi' ),
			'audience' => __( 'Perfect for established businesses ready to actively compete for search visibility in their market.', 'appiappi' ),
			'value_driver' => __( 'Scale your organic traffic predictably — with a dedicated strategy behind it every month.', 'appiappi' ),
			'group'    => 'growth',
			'homepage_visible' => false,
			'price'    => '899',
			'period'   => __( '/ month', 'appiappi' ),
			'note'     => __( "Turn your website into a lead-generation engine.", 'appiappi' ),
			'color'    => 'var(--color-plan-seo-growth)',
			'featured' => false,
			'features' => array(
				__( 'Everything in Growth', 'appiappi' ),
				__( 'Advanced Keyword &amp; Competitor Research', 'appiappi' ),
				__( 'Monthly Content Marketing Strategy &amp; Execution', 'appiappi' ),
				__( 'Local SEO &amp; Google Business Profile Optimisation', 'appiappi' ),
				__( 'Advanced Technical SEO Audits', 'appiappi' ),
				__( 'Conversion Rate Optimisation Review', 'appiappi' ),
				__( 'Detailed Monthly Performance Reporting', 'appiappi' ),
			),
			'cta_text' => __( 'Choose SEO Growth', 'appiappi' ),
			'cta_url'  => '#contact',
		),
	);

	if ( $homepage_only ) {
		$plans = array_values( array_filter( $plans, function ( $plan ) {
			return ! empty( $plan['homepage_visible'] );
		} ) );
	}

	return $plans;
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
 * TODO(Phase 1.5 follow-up): fallback content for the hero when the
 * appiappi-hero-slider plugin isn't active (or is active but has zero
 * published slides) — see appiappi_render_hero_slides() below.
 */
function appiappi_get_hero_slides() {
	return array(
		array(
			'headline'    => __( 'Your Website. Professionally Managed. Every Day.', 'appiappi' ),
			'subheadline' => __( 'Get a professionally designed website, managed hosting, ongoing SEO, content updates and dedicated support — all from one trusted Canadian team.', 'appiappi' ),
			'image'       => get_template_directory_uri() . '/assets/images/hero-placeholder.svg',
			'image_alt'   => '',
			'cta_text'    => __( 'Explore Website Designs', 'appiappi' ),
			'cta_url'     => home_url( '/templates/' ),
		),
	);
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
				<?php if ( ! empty( $plan['tagline'] ) ) : ?>
					<p class="pricing-card__tagline"><?php echo esc_html( $plan['tagline'] ); ?></p>
				<?php endif; ?>

				<p class="pricing-card__price">
					<span class="pricing-card__price-amount">$<?php echo esc_html( $plan['price'] ); ?></span>
					<span class="pricing-card__price-period"><?php echo esc_html( $plan['period'] ); ?></span>
				</p>
				<p class="pricing-card__note"><?php echo esc_html( $plan['note'] ); ?></p>
				<?php if ( ! empty( $plan['audience'] ) ) : ?>
					<p class="pricing-card__audience"><?php echo esc_html( $plan['audience'] ); ?></p>
				<?php endif; ?>

				<ul class="pricing-card__features">
					<?php foreach ( $plan['features'] as $feature ) : ?>
						<li><?php echo appiappi_icon( 'check' ); ?><span><?php echo wp_kses_post( $feature ); ?></span></li>
					<?php endforeach; ?>
				</ul>

				<?php if ( ! empty( $plan['value_driver'] ) ) : ?>
					<p class="pricing-card__value-driver"><?php echo appiappi_icon( 'trending-up' ); ?><span><?php echo esc_html( $plan['value_driver'] ); ?></span></p>
				<?php endif; ?>

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
					<input type="search" id="templates-search" placeholder="<?php esc_attr_e( 'Search templates…', 'appiappi' ); ?>" aria-label="<?php esc_attr_e( 'Search templates', 'appiappi' ); ?>">
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
					<div class="templates-sidebar__group" id="templates-styles">
						<p class="templates-sidebar__title"><?php esc_html_e( 'Style', 'appiappi' ); ?></p>
						<?php foreach ( $styles as $style ) : ?>
							<label class="templates-sidebar__checkbox">
								<input type="checkbox" class="templates-style-filter" value="<?php echo esc_attr( $style ); ?>"> <?php echo esc_html( $style ); ?>
							</label>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</aside>
		<?php endif; ?>

		<div class="templates-main">
			<div class="templates-main__toolbar">
				<p class="templates-main__count" id="templates-count" data-singular="<?php esc_attr_e( 'Showing %d design', 'appiappi' ); ?>" data-plural="<?php esc_attr_e( 'Showing %d designs', 'appiappi' ); ?>">
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
				<div class="template-grid" id="templates-grid">
					<?php foreach ( $templates as $template ) : ?>
						<div class="card template-card" data-category="<?php echo esc_attr( $template['category'] ); ?>" data-style="<?php echo esc_attr( $template['style'] ?? '' ); ?>" data-search="<?php echo esc_attr( strtolower( $template['name'] . ' ' . $template['desc'] . ' ' . $template['category'] ) ); ?>">
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
				<p id="templates-empty" class="templates-empty-state" hidden><?php esc_html_e( 'No designs match your search/filters. Try clearing them.', 'appiappi' ); ?></p>
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

/**
 * Shared hero markup (content column + rotating slides + visual column).
 * Same pattern as the pricing/template-showcase renderers: the theme's
 * own placeholder (appiappi_get_hero_slides()) and the
 * appiappi-hero-slider plugin's [appiappi_hero_slider] shortcode both
 * build a $slides array in this shape and render through this one
 * function.
 *
 * The eyebrow, the 4 feature chips, and the "View Our Plans" secondary
 * CTA are constant across every slide (they're standing value props, not
 * per-slide marketing copy) and are simply repeated inside each slide's
 * markup — harmless since only one slide is visible at a time. Only the
 * headline, subheadline, image and primary CTA actually change per slide.
 * Dots (and the auto-advance/pause-on-hover behaviour in assets/js/main.js)
 * only appear when there's more than one slide.
 *
 * @param array $slides Each item: headline, subheadline, image,
 *                       image_alt, cta_text, cta_url.
 */
function appiappi_render_hero_slides( array $slides ) {
	if ( empty( $slides ) ) {
		return '';
	}

	$rating = appiappi_get_google_rating();
	$multi  = count( $slides ) > 1;

	$chips = array(
		array( 'icon' => 'monitor',   'label' => __( 'Professional Templates', 'appiappi' ) ),
		array( 'icon' => 'rocket',    'label' => __( 'Fast Launch', 'appiappi' ) ),
		array( 'icon' => 'bar-chart', 'label' => __( 'SEO & Optimization', 'appiappi' ) ),
		array( 'icon' => 'headset',   'label' => __( 'Daily Support', 'appiappi' ) ),
	);

	ob_start();
	?>
	<div class="hero__content">
		<span class="hero__eyebrow"><?php echo appiappi_icon( 'leaf', '' ); ?> <?php esc_html_e( 'Canadian Web Design & SEO', 'appiappi' ); ?></span>

		<div class="hero__slides" aria-live="polite">
			<?php foreach ( $slides as $index => $slide ) : ?>
				<div class="hero-slide <?php echo 0 === $index ? 'is-active' : ''; ?>" data-hero-slide="<?php echo esc_attr( $index ); ?>">
					<h1 class="hero__title"><?php echo esc_html( $slide['headline'] ); ?></h1>
					<p class="hero__lede"><?php echo esc_html( $slide['subheadline'] ); ?></p>

					<ul class="chip-list">
						<?php foreach ( $chips as $chip ) : ?>
							<li class="chip">
								<span class="chip__icon"><?php echo appiappi_icon( $chip['icon'] ); ?></span>
								<span class="chip__label"><?php echo esc_html( $chip['label'] ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>

					<div class="hero__actions">
						<a href="<?php echo esc_url( $slide['cta_url'] ); ?>" class="btn btn-primary"><?php echo esc_html( $slide['cta_text'] ); ?></a>
						<a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" class="btn btn-secondary"><?php esc_html_e( 'View Our Plans', 'appiappi' ); ?></a>
					</div>
				</div>
			<?php endforeach; ?>
		</div>

		<?php if ( $multi ) : ?>
			<div class="hero-dots" role="tablist" aria-label="<?php esc_attr_e( 'Hero slides', 'appiappi' ); ?>">
				<?php foreach ( $slides as $index => $slide ) : ?>
					<button
						type="button"
						class="<?php echo 0 === $index ? 'is-active' : ''; ?>"
						data-hero-dot="<?php echo esc_attr( $index ); ?>"
						aria-label="<?php echo esc_attr( sprintf( /* translators: %d slide number */ __( 'Show slide %d', 'appiappi' ), $index + 1 ) ); ?>"
					></button>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>

	<div class="hero__visual">
		<div class="hero__visual-frame">
			<?php foreach ( $slides as $index => $slide ) : ?>
				<?php $image = $slide['image'] ?: get_template_directory_uri() . '/assets/images/hero-placeholder.svg'; ?>
				<img
					class="<?php echo 0 === $index ? 'is-active' : ''; ?>"
					data-hero-slide-image="<?php echo esc_attr( $index ); ?>"
					src="<?php echo esc_url( $image ); ?>"
					alt="<?php echo esc_attr( $slide['image_alt'] ); ?>"
					<?php echo 0 === $index ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"'; ?>
				>
			<?php endforeach; ?>
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
	<?php
	return ob_get_clean();
}

/**
 * Consistent inner-page header band (title + optional subtitle). Used by
 * every non-homepage page template. Title comes from the_title() so the
 * H1 stays admin-editable per page.
 */
function appiappi_page_header( $subtitle = '' ) {
	?>
	<header class="page-header">
		<div class="container">
			<h1><?php echo esc_html( single_post_title( '', false ) ); ?></h1>
			<?php if ( $subtitle ) : ?>
				<p><?php echo esc_html( $subtitle ); ?></p>
			<?php endif; ?>
		</div>
	</header>
	<?php
}

/**
 * Services page content. Fixed offering descriptions, not "business
 * data" in the pricing/contact-info sense — a static array here (rather
 * than a CPT) matches how the trust bar's items are handled.
 */
function appiappi_get_services() {
	return array(
		array(
			'icon'    => 'monitor',
			'name'    => __( 'Website Design', 'appiappi' ),
			'desc'    => __( 'Professional, responsive websites built around your business and your customers.', 'appiappi' ),
			'hook'    => __( 'Your website is the first impression most customers will ever have of your business — we design it to convert visitors into calls, bookings and customers, not just look good on a screen.', 'appiappi' ),
			'breakdown' => array(
				__( 'Custom design tailored to your brand, services and target customer', 'appiappi' ),
				__( 'Fully responsive layouts tested across mobile, tablet and desktop', 'appiappi' ),
				__( 'Conversion-focused structure — clear calls-to-action, trust signals, service pages', 'appiappi' ),
				__( 'Accessibility best practices built in from the start', 'appiappi' ),
				__( 'Professional copywriting guidance for key pages', 'appiappi' ),
				__( 'Launch-ready technical setup — SSL, domain connection, essential plugins', 'appiappi' ),
			),
			'closing' => __( "A website built like this isn't a one-time project — it's the foundational business infrastructure everything else on this page builds on. Let's build yours properly, the first time.", 'appiappi' ),
		),
		array(
			'icon'    => 'refresh',
			'name'    => __( 'Website Management', 'appiappi' ),
			'desc'    => __( 'Ongoing updates, maintenance and changes so your site keeps working, safely.', 'appiappi' ),
			'hook'    => __( "Software doesn't stand still, and neither should your website — we handle every update, patch and change behind the scenes so it keeps running safely without ever landing on your to-do list.", 'appiappi' ),
			'breakdown' => array(
				__( 'Regular WordPress core, theme and plugin updates', 'appiappi' ),
				__( 'Pre-update staging tests to catch conflicts before they go live', 'appiappi' ),
				__( 'Ongoing malware and vulnerability monitoring', 'appiappi' ),
				__( 'Scheduled backups with tested restore points', 'appiappi' ),
				__( 'Uptime monitoring with proactive alerts', 'appiappi' ),
				__( 'Small content and layout changes handled on request', 'appiappi' ),
			),
			'closing' => __( "Think of us as the technical team you don't have to hire — quietly keeping things running while you focus on your business.", 'appiappi' ),
		),
		array(
			'icon'    => 'trending-up',
			'name'    => __( 'SEO', 'appiappi' ),
			'desc'    => __( 'Technical SEO, on-page SEO, keyword optimisation and ongoing improvements.', 'appiappi' ),
			'hook'    => __( "Ranking on Google isn't luck — it's the result of deliberate, ongoing technical and content work, and we handle all of it so your business shows up when local customers are searching.", 'appiappi' ),
			'breakdown' => array(
				__( 'Technical SEO audits — site speed, crawlability, mobile usability', 'appiappi' ),
				__( 'Schema markup for services, reviews and local business data', 'appiappi' ),
				__( 'On-page optimisation — titles, meta descriptions, header structure', 'appiappi' ),
				__( 'Local citation auditing and Google Business Profile alignment', 'appiappi' ),
				__( 'Keyword research tied to real customer search behaviour', 'appiappi' ),
				__( "Quarterly strategy reviews with reporting on what's actually changed", 'appiappi' ),
			),
			'closing' => __( "SEO isn't a checkbox — it's a quarter-over-quarter relationship. We stay on it so your ranking doesn't quietly slip the moment someone stops paying attention.", 'appiappi' ),
		),
		array(
			'icon'    => 'pencil',
			'name'    => __( 'Content Management', 'appiappi' ),
			'desc'    => __( 'Content updates, image updates, service pages and business information.', 'appiappi' ),
			'hook'    => __( 'Your website should always reflect where your business actually is today — new services, new pricing, new team members — and we make sure it does, without you needing to learn WordPress.', 'appiappi' ),
			'breakdown' => array(
				__( 'Text and image updates across any page, on request', 'appiappi' ),
				__( 'New service page creation as your offering grows', 'appiappi' ),
				__( 'Business information updates — hours, locations, contact details', 'appiappi' ),
				__( 'Image optimisation and compression for fast load times', 'appiappi' ),
				__( 'Seasonal or promotional content updates', 'appiappi' ),
				__( 'Content reviewed for SEO impact before publishing', 'appiappi' ),
			),
			'closing' => __( "A website that's never updated starts to look abandoned. We keep yours current, so it keeps working as hard as you do.", 'appiappi' ),
		),
		array(
			'icon'    => 'shield',
			'name'    => __( 'Managed Hosting', 'appiappi' ),
			'desc'    => __( 'Reliable hosting, backups, security and technical management.', 'appiappi' ),
			'hook'    => __( 'A slow or insecure host quietly costs you customers every day — our managed hosting is built for speed, security and reliability, monitored continuously so problems get caught before your customers ever notice.', 'appiappi' ),
			'breakdown' => array(
				__( 'Performance-optimised hosting infrastructure', 'appiappi' ),
				__( 'Daily automated backups stored securely off-site', 'appiappi' ),
				__( 'SSL certificate management and renewal', 'appiappi' ),
				__( 'Firewall and malware monitoring', 'appiappi' ),
				__( 'Server-level security hardening', 'appiappi' ),
				__( 'Downtime alerts and rapid incident response', 'appiappi' ),
			),
			'closing' => __( 'Hosting is infrastructure, not an afterthought. We treat it that way — so you never have to think about it at all.', 'appiappi' ),
		),
		array(
			'icon'    => 'headset',
			'name'    => __( 'Website Support', 'appiappi' ),
			'desc'    => __( 'A dedicated managed support process whenever you need help.', 'appiappi' ),
			'hook'    => __( "When something needs fixing or changing, you shouldn't have to figure out who to call — you have a dedicated support process and a real team that knows your website, ready when you need them.", 'appiappi' ),
			'breakdown' => array(
				__( 'Direct access to a dedicated support process — not a ticket black hole', 'appiappi' ),
				__( 'Priority response for Growth-plan customers', 'appiappi' ),
				__( 'Troubleshooting for technical issues, big or small', 'appiappi' ),
				__( 'Guidance on using and updating your own site', 'appiappi' ),
				__( 'Coordination across hosting, content and SEO when issues overlap', 'appiappi' ),
				__( 'Clear communication — no jargon, no guesswork', 'appiappi' ),
			),
			'closing' => __( "Support shouldn't feel like a favour. It's part of the partnership — and it's there every time you need it.", 'appiappi' ),
		),
	);
}

/**
 * How It Works page content. Same "fixed offering, static array" logic
 * as appiappi_get_services().
 */
function appiappi_get_how_it_works_steps() {
	return array(
		array(
			'title'       => __( 'Choose Your Design', 'appiappi' ),
			'we_do'       => __( 'We maintain a curated library of professionally designed website templates, organized by industry (construction, legal, dental, restaurants, professional services and more) and by style (modern, minimal, bold, classic), so you can quickly find designs that already suit businesses like yours.', 'appiappi' ),
			'you_provide' => __( "Just your preference. Browse the library and pick the one design that feels right for your business — that's it.", 'appiappi' ),
			'benefit'     => __( 'Starting from a proven, professionally built design means you skip the most expensive and time-consuming part of building a website from scratch, while still ending up with something that looks like your brand, not a generic template.', 'appiappi' ),
		),
		array(
			'title'       => __( 'Choose Your Plan', 'appiappi' ),
			'we_do'       => __( 'We offer a clear range of plans, from a one-time Starter launch to our ongoing Growth plan, each scoped so you know exactly what\'s included before you commit.', 'appiappi' ),
			'you_provide' => __( "A sense of what you need right now — a simple one-time launch, a fully customized business site, or a website that's actively managed and grown every month.", 'appiappi' ),
			'benefit'     => __( "You're not locked into a one-size-fits-all package. You choose the level of involvement and ongoing support that fits your budget and stage of business, and can move to a bigger plan later as you grow.", 'appiappi' ),
		),
		array(
			'title'       => __( 'Provide Your Business Information', 'appiappi' ),
			'we_do'       => __( 'We give you a simple, guided way to hand over everything we need — no technical knowledge required.', 'appiappi' ),
			'you_provide' => __( "Your logo files and brand colours (if you have them), a description of your services, basic business details (hours, location, contact info), any existing content or photos you'd like used, and a sense of who your ideal customer is.", 'appiappi' ),
			'benefit'     => __( 'The more we know about your business up front, the more your finished website actually sounds and looks like you — not a generic template with your name pasted on top.', 'appiappi' ),
		),
		array(
			'title'       => __( 'We Customize Your Website', 'appiappi' ),
			'we_do'       => __( "Our team applies your branding across the chosen design, writes and places your services and content, adjusts the site's structure around what your business actually offers, and sets up the basics of on-page SEO from day one.", 'appiappi' ),
			'you_provide' => __( 'Nothing further at this stage beyond answering any follow-up questions — this is where we do the work.', 'appiappi' ),
			'benefit'     => __( 'You get a website that looks and feels like your brand, structured around your real services, without spending your own time learning design or web development.', 'appiappi' ),
		),
		array(
			'title'       => __( 'We Launch Your Website', 'appiappi' ),
			'we_do'       => __( 'We handle every technical step behind going live — server configuration, connecting your domain, DNS setup, and installing your SSL certificate so your site is secure from day one.', 'appiappi' ),
			'you_provide' => __( "Access to your domain registrar if you already own a domain, or a decision on a new domain name if you don't.", 'appiappi' ),
			'benefit'     => __( "A stress-free launch with no technical guesswork on your end, and a website that's secure and reliable the moment it goes live.", 'appiappi' ),
		),
		array(
			'title'       => __( 'We Manage & Optimize It', 'appiappi' ),
			'we_do'       => __( "On the Growth plan, we monitor your site's security around the clock, run regular backups, keep WordPress and every plugin updated, check performance and speed, manage your ongoing SEO (technical fixes, keyword tracking, monthly reporting), and make content updates whenever your business changes.", 'appiappi' ),
			'you_provide' => __( "Just let us know when something in your business changes — a new service, updated hours, a promotion — and we handle the rest.", 'appiappi' ),
			'benefit'     => __( 'Long-term peace of mind. Your website keeps working, stays secure, and keeps improving in search results, without ever becoming "that thing you have to deal with."', 'appiappi' ),
		),
	);
}

/**
 * TODO(Phase 2 follow-up): fallback content for the FAQ page/section
 * when the appiappi-faq plugin isn't active — see appiappi_render_faq().
 */
function appiappi_get_faqs() {
	$faqs = array(
		__( 'What is included?', 'appiappi' )                         => __( 'Every plan includes a professionally customized website. Growth also adds ongoing hosting, maintenance, SEO and support — see the Pricing page for full details per plan.', 'appiappi' ),
		__( 'Who owns my domain?', 'appiappi' )                        => __( 'You do. We can assist with connecting or registering a domain, but it stays registered in your name.', 'appiappi' ),
		__( 'Who hosts my website?', 'appiappi' )                      => __( 'Professional and Growth plans include managed hosting through us. Starter and Business can be hosted anywhere you choose.', 'appiappi' ),
		__( 'Can I choose my website design?', 'appiappi' )            => __( 'Yes — browse our curated Website Designs library and pick the one that fits your business.', 'appiappi' ),
		__( 'Can you customize the design?', 'appiappi' )              => __( 'Yes. Every plan (except Starter) includes customization with your branding, content, colours and images.', 'appiappi' ),
		__( 'Is SEO included?', 'appiappi' )                           => __( 'Basic on-page SEO is included from the Business plan up. Ongoing SEO management is part of the Growth plan.', 'appiappi' ),
		__( 'How does monthly support work?', 'appiappi' )             => __( 'Growth customers get ongoing website changes, content updates and technical support as part of their monthly plan.', 'appiappi' ),
		__( 'What happens if I cancel?', 'appiappi' )                  => __( 'You keep your website. We can help transfer hosting and files to a provider of your choice.', 'appiappi' ),
		__( 'Can I change my website later?', 'appiappi' )             => __( 'Yes — Growth customers can request changes anytime; other plans can request paid updates as needed.', 'appiappi' ),
		__( 'Do you support e-commerce?', 'appiappi' )                 => __( 'Our current focus is service-based Canadian businesses. Contact us to discuss e-commerce needs.', 'appiappi' ),
		__( 'How long does a website take?', 'appiappi' )              => __( 'Timelines vary by plan and how quickly we receive your content — most launches take a few weeks.', 'appiappi' ),
		__( 'What happens to my website if I leave?', 'appiappi' )     => __( 'Your website and content remain yours. We\'ll help you migrate to a new host or team.', 'appiappi' ),
	);

	$out = array();
	foreach ( $faqs as $question => $answer ) {
		$out[] = array( 'question' => $question, 'answer' => wpautop( $answer ) );
	}
	return $out;
}

/**
 * Shared FAQ accordion markup. Renders a `.faq-list` from an array
 * shaped like appiappi_get_faqs()'s return value — same pattern as the
 * other section renderers: the theme's placeholder and the
 * appiappi-faq plugin's [appiappi_faq] shortcode both render through
 * this one function. Toggle behaviour lives in assets/js/main.js.
 *
 * @param array $faqs Each item: question, answer (may contain HTML).
 */
function appiappi_render_faq( array $faqs ) {
	if ( empty( $faqs ) ) {
		return '';
	}

	ob_start();
	?>
	<div class="faq-list">
		<?php foreach ( $faqs as $index => $faq ) : ?>
			<div class="faq-item">
				<button type="button" class="faq-item__question" aria-expanded="false" aria-controls="faq-answer-<?php echo esc_attr( $index ); ?>">
					<span><?php echo esc_html( $faq['question'] ); ?></span>
					<?php echo appiappi_icon( 'close' ); ?>
				</button>
				<div class="faq-item__answer" id="faq-answer-<?php echo esc_attr( $index ); ?>">
					<div class="faq-item__answer-inner"><?php echo wp_kses_post( $faq['answer'] ); ?></div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * TODO(Phase 2 follow-up): fallback content for the Portfolio page when
 * the appiappi-portfolio plugin isn't active. Clearly marked as concept
 * projects — never fabricate real client results, per project rules.
 */
function appiappi_get_portfolio_projects() {
	return array(
		array(
			'title'        => __( 'Concept: Regional Construction Company', 'appiappi' ),
			'industry'     => __( 'Construction', 'appiappi' ),
			'client'       => '',
			'location'     => '',
			'desc'         => __( 'An illustrative example of a construction-industry launch on the Professional plan.', 'appiappi' ),
			'image'        => '',
			'external_url' => '',
			'is_concept'   => true,
		),
		array(
			'title'        => __( 'Concept: Local Law Practice', 'appiappi' ),
			'industry'     => __( 'Legal', 'appiappi' ),
			'client'       => '',
			'location'     => '',
			'desc'         => __( 'An illustrative example of a law-firm site with ongoing Growth-plan management.', 'appiappi' ),
			'image'        => '',
			'external_url' => '',
			'is_concept'   => true,
		),
		array(
			'title'        => __( 'Concept: Dental Clinic', 'appiappi' ),
			'industry'     => __( 'Dental & Medical', 'appiappi' ),
			'client'       => '',
			'location'     => '',
			'desc'         => __( 'An illustrative example of a healthcare-practice launch on the Business plan.', 'appiappi' ),
			'image'        => '',
			'external_url' => '',
			'is_concept'   => true,
		),
	);
}

/**
 * Shared portfolio-grid markup. Renders a `.portfolio-grid` from an
 * array shaped like appiappi_get_portfolio_projects()'s return value —
 * same pattern as the other renderers.
 */
function appiappi_render_portfolio_grid( array $projects ) {
	if ( empty( $projects ) ) {
		return '';
	}

	ob_start();
	?>
	<div class="portfolio-grid">
		<?php foreach ( $projects as $project ) : ?>
			<div class="card portfolio-card">
				<div class="portfolio-card__media">
					<?php if ( ! empty( $project['image'] ) ) : ?>
						<img src="<?php echo esc_url( $project['image'] ); ?>" alt="<?php echo esc_attr( $project['title'] ); ?>" loading="lazy">
					<?php endif; ?>
					<?php if ( ! empty( $project['is_concept'] ) ) : ?>
						<span class="badge badge-dark portfolio-card__concept-badge"><?php esc_html_e( 'Concept', 'appiappi' ); ?></span>
					<?php endif; ?>
				</div>
				<div class="portfolio-card__body">
					<?php if ( ! empty( $project['industry'] ) ) : ?>
						<p class="portfolio-card__meta"><?php echo esc_html( $project['industry'] ); ?><?php echo ! empty( $project['location'] ) ? ' · ' . esc_html( $project['location'] ) : ''; ?></p>
					<?php endif; ?>
					<h3><?php echo esc_html( $project['title'] ); ?></h3>
					<p><?php echo esc_html( $project['desc'] ); ?></p>
					<?php if ( ! empty( $project['external_url'] ) ) : ?>
						<p style="margin-top: var(--space-3);"><a href="<?php echo esc_url( $project['external_url'] ); ?>" class="btn btn-secondary btn-sm" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'View Website', 'appiappi' ); ?></a></p>
					<?php endif; ?>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Blog pagination, styled to match the design system (paginate_links()
 * wrapped in .appiappi-pagination rather than WP core's default markup).
 */
function appiappi_pagination() {
	global $wp_query;

	$links = paginate_links( array(
		'total'   => $wp_query->max_num_pages,
		'current' => max( 1, get_query_var( 'paged' ) ),
		'mid_size'  => 1,
		'prev_text' => __( '&larr; Previous', 'appiappi' ),
		'next_text' => __( 'Next &rarr;', 'appiappi' ),
	) );

	if ( $links ) {
		echo '<nav class="appiappi-pagination" aria-label="' . esc_attr__( 'Posts navigation', 'appiappi' ) . '">' . wp_kses_post( $links ) . '</nav>';
	}
}
