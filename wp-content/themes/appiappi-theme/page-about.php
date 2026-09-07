<?php
/**
 * Template Name: About Page
 * Auto-applies to a page with the slug "about". Body content is the
 * native WordPress editor content (the_content()) — intentionally NOT
 * hard-coded, since this is genuine editorial copy the business owner
 * should be able to edit without touching code.
 */

get_header();
?>

<main id="main-content">
	<?php appiappi_breadcrumbs(); ?>
	<?php appiappi_page_header( __( 'Get to know Appiappi — who we are, what we do, and why Canadian businesses trust us to manage their website long after launch.', 'appiappi' ), 'about' ); ?>

	<article class="section">
		<div class="container single-post about-page-content">
			<div class="single-post__content">
				<?php
				if ( have_posts() ) {
					while ( have_posts() ) {
						the_post();
						the_content();
					}
				}
				?>
			</div>
		</div>
	</article>

	<?php get_template_part( 'template-parts/sections/trust-bar' ); ?>
	<?php get_template_part( 'template-parts/sections/final-cta' ); ?>
</main>

<?php
get_footer();
