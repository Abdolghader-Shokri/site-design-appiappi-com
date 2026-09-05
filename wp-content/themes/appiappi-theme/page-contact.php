<?php
/**
 * Template Name: Contact Page
 * Auto-applies to a page with the slug "contact". Uses the
 * appiappi-contact plugin's [appiappi_contact_form] shortcode when
 * active. If the plugin isn't active, shows a simple mailto/phone
 * fallback rather than a fake, non-working copy of the form.
 */

get_header();

$phone   = get_theme_mod( 'appiappi_phone' );
$email   = get_theme_mod( 'appiappi_email' );
$address = get_theme_mod( 'appiappi_address' );
?>

<main id="main-content">
	<?php appiappi_breadcrumbs(); ?>
	<?php appiappi_page_header( __( 'Questions about a plan, a design, or your project? We would love to hear from you.', 'appiappi' ) ); ?>

	<section class="section">
		<div class="container contact-layout">
			<div class="card contact-info-card">
				<h3><?php esc_html_e( 'Contact Information', 'appiappi' ); ?></h3>
				<ul>
					<?php if ( $address ) : ?>
						<li><?php echo appiappi_icon( 'map-pin' ); ?><span><?php echo esc_html( $address ); ?></span></li>
					<?php endif; ?>
					<?php if ( $phone ) : ?>
						<li><?php echo appiappi_icon( 'phone' ); ?><a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a></li>
					<?php endif; ?>
					<?php if ( $email ) : ?>
						<li><?php echo appiappi_icon( 'mail' ); ?><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></li>
					<?php endif; ?>
					<?php if ( ! $address && ! $phone && ! $email ) : ?>
						<li><?php esc_html_e( 'Add contact details in Customizer > Contact Information.', 'appiappi' ); ?></li>
					<?php endif; ?>
				</ul>
			</div>

			<div class="card contact-form-card">
				<?php if ( shortcode_exists( 'appiappi_contact_form' ) ) : ?>
					<?php echo do_shortcode( '[appiappi_contact_form]' ); ?>
				<?php else : ?>
					<p>
						<?php
						if ( $email ) {
							printf(
								/* translators: %s email address */
								esc_html__( 'Our contact form is being set up — in the meantime, email us at %s.', 'appiappi' ),
								'<a href="mailto:' . esc_attr( $email ) . '">' . esc_html( $email ) . '</a>'
							);
						} else {
							esc_html_e( 'Our contact form is being set up. Please check back soon.', 'appiappi' );
						}
						?>
					</p>
				<?php endif; ?>
			</div>
		</div>
	</section>
</main>

<?php
get_footer();
