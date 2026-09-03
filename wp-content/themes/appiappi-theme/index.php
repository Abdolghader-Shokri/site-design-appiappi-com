<?php
/**
 * Fallback template (WordPress template hierarchy requires index.php
 * to exist). Dedicated templates (front-page.php, page.php, single.php,
 * archive.php…) are added as their content systems are built in later
 * phases — see PROJECT_MASTER.md.
 */

get_header();
?>

<main id="main-content" class="section container">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class(); ?>>
				<h1><?php the_title(); ?></h1>
				<div class="entry-content"><?php the_content(); ?></div>
			</article>
		<?php endwhile; ?>
	<?php else : ?>
		<p><?php esc_html_e( 'Nothing found.', 'appiappi' ); ?></p>
	<?php endif; ?>
</main>

<?php
get_footer();
