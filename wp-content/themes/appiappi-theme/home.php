<?php
/**
 * Blog index. WordPress uses this template for the blog listing
 * whenever `page_for_posts` is set (Settings > Reading), regardless of
 * front-page.php owning the actual site root — see PROJECT_MASTER.md
 * for how Reading settings are configured for this site.
 */

get_header();
?>

<main id="main-content">
	<?php appiappi_breadcrumbs(); ?>
	<?php appiappi_page_header( __( 'News, guides and updates on websites, SEO and growing your business online.', 'appiappi' ) ); ?>

	<section class="section">
		<div class="container">
			<?php if ( have_posts() ) : ?>
				<div class="post-grid">
					<?php
					while ( have_posts() ) {
						the_post();
						get_template_part( 'template-parts/content/post-card' );
					}
					?>
				</div>
				<?php appiappi_pagination(); ?>
			<?php else : ?>
				<p><?php esc_html_e( 'No posts yet — check back soon.', 'appiappi' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
</main>

<?php
get_footer();
