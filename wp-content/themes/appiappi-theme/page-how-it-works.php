<?php
/**
 * Template Name: How It Works Page
 * Auto-applies to a page with the slug "how-it-works".
 *
 * Each step is What We Do / What You Provide / Your Benefit — content
 * lives in appiappi_get_how_it_works_steps() (inc/template-tags.php),
 * not hard-coded here.
 */

get_header();
$steps = appiappi_get_how_it_works_steps();
?>

<main id="main-content">
	<?php appiappi_breadcrumbs(); ?>
	<?php appiappi_page_header( __( 'From choosing a design to ongoing growth — here is exactly what happens, what we need from you, and why each step matters.', 'appiappi' ) ); ?>

	<section class="section">
		<div class="container">
			<div class="step-detail-list">
				<?php foreach ( $steps as $index => $step ) : ?>
					<article class="step-detail">
						<div class="step-detail__header">
							<span class="step__number"><?php echo esc_html( $index + 1 ); ?></span>
							<h2>
								<?php
								printf(
									/* translators: 1: step number, 2: step title */
									esc_html__( 'Step %1$d: %2$s', 'appiappi' ),
									$index + 1,
									esc_html( $step['title'] )
								);
								?>
							</h2>
						</div>

						<div class="step-detail__group">
							<p class="step-detail__label"><?php esc_html_e( 'What We Do', 'appiappi' ); ?></p>
							<p><?php echo esc_html( $step['we_do'] ); ?></p>
						</div>
						<div class="step-detail__group">
							<p class="step-detail__label"><?php esc_html_e( 'What You Provide', 'appiappi' ); ?></p>
							<p><?php echo esc_html( $step['you_provide'] ); ?></p>
						</div>
						<div class="step-detail__group step-detail__group--benefit">
							<p class="step-detail__label"><?php esc_html_e( 'Your Benefit', 'appiappi' ); ?></p>
							<p><?php echo esc_html( $step['benefit'] ); ?></p>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="section section--dark">
		<div class="container final-cta">
			<h2><?php esc_html_e( 'Ready to Get Started?', 'appiappi' ); ?></h2>
			<p><?php esc_html_e( 'Browse our Website Designs, pick the one that fits your business, and let\'s take it from there — six clear steps, and a real team behind every one of them.', 'appiappi' ); ?></p>
			<div class="final-cta__actions">
				<a href="<?php echo esc_url( home_url( '/templates/' ) ); ?>" class="btn btn-primary"><?php esc_html_e( 'Choose Your Design', 'appiappi' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>" class="btn btn-outline-inverse"><?php esc_html_e( 'View Our Plans', 'appiappi' ); ?></a>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
