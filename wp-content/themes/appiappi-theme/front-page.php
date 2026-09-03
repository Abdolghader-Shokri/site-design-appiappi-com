<?php
/**
 * Homepage. Section order: Hero → Pricing → Featured Designs →
 * Trust Bar → Final CTA. Additional sections (How It Works, Services
 * teaser, Portfolio, FAQ) are added here as those page systems are
 * built in Phase 2 — see PROJECT_MASTER.md.
 */

get_header();
?>

<main id="main-content">
	<?php
	get_template_part( 'template-parts/sections/hero' );
	get_template_part( 'template-parts/sections/pricing-preview' );
	get_template_part( 'template-parts/sections/templates-preview' );
	get_template_part( 'template-parts/sections/trust-bar' );
	get_template_part( 'template-parts/sections/final-cta' );
	?>
</main>

<?php
get_footer();
