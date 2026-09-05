<?php
/**
 * Template Name: Services Page
 * Also auto-applies to a page with the slug "services" via the
 * page-services.php template-hierarchy convention.
 */

get_header();
$services = appiappi_get_services();
?>

<main id="main-content">
	<?php appiappi_page_header( __( 'Website design, hosting, SEO and support — everything your business website needs, in one place.', 'appiappi' ) ); ?>

	<section class="section">
		<div class="container">
			<div class="service-grid">
				<?php foreach ( $services as $service ) : ?>
					<div class="card service-card">
						<span class="service-card__icon"><?php echo appiappi_icon( $service['icon'] ); ?></span>
						<h3><?php echo esc_html( $service['name'] ); ?></h3>
						<p><?php echo esc_html( $service['desc'] ); ?></p>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php get_template_part( 'template-parts/sections/final-cta' ); ?>
</main>

<?php
get_footer();
