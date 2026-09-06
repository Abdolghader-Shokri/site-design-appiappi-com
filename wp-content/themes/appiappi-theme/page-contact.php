<?php
/**
 * Template Name: Contact Page
 * Auto-applies to a page with the slug "contact". Uses the
 * appiappi-contact plugin's [appiappi_contact_form] shortcode when
 * active. If the plugin isn't active, shows a simple mailto fallback
 * rather than a fake, non-working copy of the form.
 *
 * The info card next to the form (map + address + phone + support
 * email) is driven by its own Customizer section — "Contact Page Info
 * Box", separate from the sitewide "Contact Information" used by the
 * footer and schema.org data (inc/seo.php) — since this card's fields
 * (custom labels, a phone "links to" type, a Google Maps embed) only
 * apply here. Each row is independently optional; the whole card is
 * omitted (form goes full width) if every field is empty.
 */

get_header();

$map_embed      = get_theme_mod( 'appiappi_contact_map_embed' );
$map_embed_host = $map_embed ? wp_parse_url( $map_embed, PHP_URL_HOST ) : '';
$has_map        = $map_embed && $map_embed_host && preg_match( '/(^|\.)google\.com$/', $map_embed_host );

$address_label = get_theme_mod( 'appiappi_contact_address_label', __( 'Address', 'appiappi' ) );
$address_value = get_theme_mod( 'appiappi_contact_address_value' );
$has_address   = (bool) $address_value;

$phone_label = get_theme_mod( 'appiappi_contact_phone_label', __( 'Phone', 'appiappi' ) );
$phone_value = get_theme_mod( 'appiappi_contact_phone_value' );
$phone_type  = get_theme_mod( 'appiappi_contact_phone_type', 'call' );
$has_phone   = (bool) $phone_value;
$phone_href  = appiappi_contact_phone_href( $phone_value, $phone_type );

$support_email = get_theme_mod( 'appiappi_contact_support_email' );
$has_email     = (bool) $support_email;

$has_info_card = $has_map || $has_address || $has_phone || $has_email;
?>

<main id="main-content">
	<?php appiappi_breadcrumbs(); ?>
	<?php appiappi_page_header( __( 'Questions about a plan, a design, or your project? We would love to hear from you.', 'appiappi' ), 'contact' ); ?>

	<section class="section">
		<div class="container contact-layout <?php echo $has_info_card ? '' : 'contact-layout--form-only'; ?>">
			<?php if ( $has_info_card ) : ?>
				<div class="card contact-info-card">
					<?php if ( $has_map ) : ?>
						<div class="contact-info-card__map">
							<iframe src="<?php echo esc_url( $map_embed ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="<?php esc_attr_e( 'Our location', 'appiappi' ); ?>"></iframe>
						</div>
					<?php endif; ?>

					<?php if ( $has_address || $has_phone || $has_email ) : ?>
						<div class="contact-info-card__details">
							<h3><?php esc_html_e( 'Contact Information', 'appiappi' ); ?></h3>
							<ul>
								<?php if ( $has_address ) : ?>
									<li><?php echo appiappi_icon( 'map-pin' ); ?><span><strong><?php echo esc_html( $address_label ); ?>:</strong> <?php echo esc_html( $address_value ); ?></span></li>
								<?php endif; ?>
								<?php if ( $has_phone ) : ?>
									<li>
										<?php echo appiappi_icon( 'phone' ); ?>
										<span>
											<strong><?php echo esc_html( $phone_label ); ?>:</strong>
											<?php if ( $phone_href ) : ?>
												<a href="<?php echo esc_url( $phone_href ); ?>"><?php echo esc_html( $phone_value ); ?></a>
											<?php else : ?>
												<?php echo esc_html( $phone_value ); ?>
											<?php endif; ?>
										</span>
									</li>
								<?php endif; ?>
								<?php if ( $has_email ) : ?>
									<li><?php echo appiappi_icon( 'mail' ); ?><span><a href="mailto:<?php echo esc_attr( $support_email ); ?>"><?php echo esc_html( $support_email ); ?></a></span></li>
								<?php endif; ?>
							</ul>
						</div>
					<?php endif; ?>
				</div>
			<?php endif; ?>

			<div class="card contact-form-card">
				<?php if ( shortcode_exists( 'appiappi_contact_form' ) ) : ?>
					<?php echo do_shortcode( '[appiappi_contact_form]' ); ?>
				<?php else : ?>
					<p>
						<?php
						if ( $support_email ) {
							printf(
								/* translators: %s email address */
								esc_html__( 'Our contact form is being set up — in the meantime, email us at %s.', 'appiappi' ),
								'<a href="mailto:' . esc_attr( $support_email ) . '">' . esc_html( $support_email ) . '</a>'
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
