<?php
/**
 * Single blog post.
 */

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<main id="main-content">
		<?php appiappi_breadcrumbs(); ?>
		<header class="page-header">
			<div class="container">
				<h1><?php the_title(); ?></h1>
			</div>
		</header>

		<article class="section">
			<div class="container single-post">
				<p class="single-post__meta">
					<?php
					printf(
						/* translators: 1: date, 2: author name */
						esc_html__( 'Published %1$s by %2$s', 'appiappi' ),
						esc_html( get_the_date() ),
						esc_html( get_the_author() )
					);
					?>
				</p>

				<?php if ( has_post_thumbnail() ) : ?>
					<div class="single-post__media">
						<?php the_post_thumbnail( 'appiappi-hero' ); ?>
					</div>
				<?php endif; ?>

				<div class="single-post__content">
					<?php the_content(); ?>
				</div>
			</div>
		</article>

		<?php get_template_part( 'template-parts/sections/final-cta' ); ?>
	</main>
	<?php
endwhile;

get_footer();
