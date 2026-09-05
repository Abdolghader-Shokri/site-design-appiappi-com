<?php
/**
 * Template Name: Services Page
 * Also auto-applies to a page with the slug "services" via the
 * page-services.php template-hierarchy convention.
 *
 * Each service is a Hook (short benefit statement) + a Precision
 * Breakdown (4-6 concrete sub-tasks) + a closing line bridging
 * "service" to "partner" — content lives in appiappi_get_services()
 * (inc/template-tags.php), not hard-coded here.
 */

get_header();
$services = appiappi_get_services();
?>

<main id="main-content">
	<?php appiappi_breadcrumbs(); ?>
	<?php appiappi_page_header( __( "Your website isn't a brochure — it's foundational business infrastructure. Here's exactly what goes into building, running and growing it.", 'appiappi' ) ); ?>

	<section class="section">
		<div class="container">
			<div class="service-list">
				<?php foreach ( $services as $service ) : ?>
					<article class="service-block">
						<div class="service-block__header">
							<span class="service-card__icon"><?php echo appiappi_icon( $service['icon'] ); ?></span>
							<h2><?php echo esc_html( $service['name'] ); ?></h2>
						</div>

						<p class="service-block__hook"><?php echo esc_html( $service['hook'] ); ?></p>

						<ul class="service-block__breakdown">
							<?php foreach ( $service['breakdown'] as $item ) : ?>
								<li><?php echo appiappi_icon( 'check' ); ?><span><?php echo esc_html( $item ); ?></span></li>
							<?php endforeach; ?>
						</ul>

						<p class="service-block__closing"><?php echo esc_html( $service['closing'] ); ?></p>
					</article>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/sections/final-cta' ); ?>
</main>

<?php
get_footer();
