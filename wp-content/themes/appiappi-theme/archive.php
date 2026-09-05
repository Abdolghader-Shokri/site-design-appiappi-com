<?php
/**
 * Category/tag/date archives. Same post-card grid as home.php (the
 * blog index), with the archive's own title via get_the_archive_title().
 */

get_header();
?>

<main id="main-content">
	<?php appiappi_breadcrumbs(); ?>
	<header class="page-header">
		<div class="container">
			<h1><?php echo wp_kses_post( get_the_archive_title() ); ?></h1>
			<?php if ( get_the_archive_description() ) : ?>
				<p><?php echo wp_kses_post( get_the_archive_description() ); ?></p>
			<?php endif; ?>
		</div>
	</header>

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
				<p><?php esc_html_e( 'Nothing found.', 'appiappi' ); ?></p>
			<?php endif; ?>
		</div>
	</section>
</main>

<?php
get_footer();
