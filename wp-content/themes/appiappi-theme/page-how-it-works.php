<?php
/**
 * Template Name: How It Works Page
 * Auto-applies to a page with the slug "how-it-works".
 */

get_header();
$steps = appiappi_get_how_it_works_steps();
?>

<main id="main-content">
	<?php appiappi_breadcrumbs(); ?>
	<?php appiappi_page_header( __( 'From choosing a design to ongoing growth — here is exactly what happens.', 'appiappi' ) ); ?>

	<section class="section">
		<div class="container">
			<ol class="steps-list">
				<?php foreach ( $steps as $index => $step ) : ?>
					<li class="step">
						<span class="step__number"><?php echo esc_html( $index + 1 ); ?></span>
						<div class="step__body">
							<h3><?php echo esc_html( $step['title'] ); ?></h3>
							<p><?php echo esc_html( $step['desc'] ); ?></p>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>
	</section>

	<?php get_template_part( 'template-parts/sections/final-cta' ); ?>
</main>

<?php
get_footer();
