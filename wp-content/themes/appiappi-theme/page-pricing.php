<?php
/**
 * Template Name: Pricing Page
 * Auto-applies to a page with the slug "pricing". Reuses the same
 * pricing-card markup/data source as the homepage preview
 * (appiappi_render_pricing_cards() + the [appiappi_pricing] shortcode)
 * plus the FAQ accordion, per MASTER_PROMPT.md § Pricing Page.
 *
 * Plans are grouped into Launch Tiers (one-time setup) and Growth Tiers
 * (monthly subscription) via each plan's `group` meta — see the
 * "How Our Pricing Works" section below for why that split exists.
 * Rewritten 2026-09-06 per the user's pricing-strategist brief; the FAQ
 * section at the end is intentionally untouched.
 */

get_header();

$has_plugin = shortcode_exists( 'appiappi_pricing' );
?>

<main id="main-content">
	<?php appiappi_breadcrumbs(); ?>
	<?php appiappi_page_header( __( 'Two kinds of investment: a one-time Launch to get built properly, and an ongoing Growth partnership to keep performing. Here is exactly what each covers.', 'appiappi' ) ); ?>

	<section class="section" id="pricing">
		<div class="container">
			<div class="section-heading">
				<span class="section-heading__eyebrow"><?php esc_html_e( 'Launch Tiers', 'appiappi' ); ?></span>
				<h2><?php esc_html_e( 'One-Time Setup', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'A professionally built website, delivered once and yours to keep. Choose the level of customization your business needs.', 'appiappi' ); ?></p>
			</div>

			<?php if ( $has_plugin ) : ?>
				<?php echo do_shortcode( '[appiappi_pricing group="launch" show_description="1"]' ); ?>
			<?php else : ?>
				<?php
				$launch_plans = array_values( array_filter( appiappi_get_pricing_plans(), function ( $plan ) {
					return 'launch' === ( $plan['group'] ?? 'launch' );
				} ) );
				echo appiappi_render_pricing_cards( $launch_plans, true );
				?>
			<?php endif; ?>
		</div>
	</section>

	<section class="section section--subtle">
		<div class="container">
			<div class="section-heading">
				<span class="section-heading__eyebrow"><?php esc_html_e( 'Growth Tiers', 'appiappi' ); ?></span>
				<h2><?php esc_html_e( 'Monthly Managed Growth', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'Ongoing management, security and SEO — the work that keeps a website performing well after launch, for one predictable monthly investment.', 'appiappi' ); ?></p>
			</div>

			<?php if ( $has_plugin ) : ?>
				<?php echo do_shortcode( '[appiappi_pricing group="growth" show_description="1"]' ); ?>
			<?php else : ?>
				<?php
				$growth_plans = array_values( array_filter( appiappi_get_pricing_plans(), function ( $plan ) {
					return 'growth' === ( $plan['group'] ?? 'launch' );
				} ) );
				echo appiappi_render_pricing_cards( $growth_plans, true );
				?>
			<?php endif; ?>
		</div>
	</section>

	<section class="section">
		<div class="container">
			<div class="section-heading">
				<span class="section-heading__eyebrow"><?php esc_html_e( 'How It Works', 'appiappi' ); ?></span>
				<h2><?php esc_html_e( 'How Our Pricing Works', 'appiappi' ); ?></h2>
			</div>
			<div class="pricing-explainer">
				<div class="pricing-explainer__col">
					<h3><?php esc_html_e( 'Setup Fees cover the build.', 'appiappi' ); ?></h3>
					<p><?php esc_html_e( 'A one-time Launch Tier fee covers everything involved in designing, customizing and technically launching your website — the work that only needs to happen once. You own the result outright, with no ongoing obligation.', 'appiappi' ); ?></p>
				</div>
				<div class="pricing-explainer__col">
					<h3><?php esc_html_e( 'Monthly Subscriptions cover the growth.', 'appiappi' ); ?></h3>
					<p><?php esc_html_e( 'A Growth Tier covers the ongoing work that keeps a website secure, fast and improving after launch: managed hosting, security monitoring, backups, content updates and — on SEO Growth — active organic growth strategy. This is where a website stops being a one-time project and becomes a business asset.', 'appiappi' ); ?></p>
				</div>
			</div>
			<p class="pricing-explainer__note"><?php esc_html_e( 'Most clients start with a Launch Tier to get online properly, then move to a Growth Tier once their site is live. Professional plan clients already get a full year of managed hosting included before any monthly commitment is needed.', 'appiappi' ); ?></p>
		</div>
	</section>

	<section class="section section--subtle">
		<div class="container">
			<div class="section-heading">
				<span class="section-heading__eyebrow"><?php esc_html_e( 'FAQ', 'appiappi' ); ?></span>
				<h2><?php esc_html_e( 'Pricing Questions', 'appiappi' ); ?></h2>
			</div>
			<?php if ( shortcode_exists( 'appiappi_faq' ) ) : ?>
				<?php echo do_shortcode( '[appiappi_faq]' ); ?>
			<?php else : ?>
				<?php echo appiappi_render_faq( appiappi_get_faqs() ); ?>
			<?php endif; ?>
		</div>
	</section>

	<?php get_template_part( 'template-parts/sections/final-cta' ); ?>
</main>

<?php
get_footer();
