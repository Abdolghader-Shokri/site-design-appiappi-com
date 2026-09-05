<?php
/**
 * One post card for the blog index/archive grid. Used inside the loop
 * in home.php and archive.php (the_post() must already have run).
 */
?>
<article <?php post_class( 'post-card' ); ?>>
	<a href="<?php the_permalink(); ?>">
		<div class="post-card__media">
			<?php if ( has_post_thumbnail() ) : ?>
				<?php the_post_thumbnail( 'appiappi-card' ); ?>
			<?php endif; ?>
		</div>
	</a>
	<p class="post-card__meta">
		<?php echo esc_html( get_the_date() ); ?>
		<?php
		$categories = get_the_category();
		if ( $categories ) {
			echo ' · ' . esc_html( $categories[0]->name );
		}
		?>
	</p>
	<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
	<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
</article>
