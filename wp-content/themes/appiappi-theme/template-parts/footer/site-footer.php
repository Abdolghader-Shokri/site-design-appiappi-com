<?php
/**
 * Four-column footer: brand, quick links, services, contact + social.
 *
 * The Contact column reuses the exact same Customizer fields as the
 * Contact page's info box ("Contact Page Info Box") — address, phone
 * (with its label/"links to" type) and support email — minus the map,
 * so both places always show the same details without re-entering them.
 * If none of those are set, it falls back to just the General Public
 * Email (Settings → Appiappi Settings → Legal & Company Information);
 * if that's empty too, the whole column is omitted rather than showing
 * a "please configure this" placeholder in production.
 */

$footer_services = function_exists( 'appiappi_services_get_services' ) ? appiappi_services_get_services() : appiappi_get_services();

$footer_address_value = get_theme_mod( 'appiappi_contact_address_value' );
$footer_phone_value    = get_theme_mod( 'appiappi_contact_phone_value' );
$footer_phone_href     = appiappi_contact_phone_href( $footer_phone_value, get_theme_mod( 'appiappi_contact_phone_type', 'call' ) );
$footer_support_email  = get_theme_mod( 'appiappi_contact_support_email' );
$footer_has_contact_box = $footer_address_value || $footer_phone_value || $footer_support_email;
$footer_fallback_email  = $footer_has_contact_box ? '' : appiappi_get_setting( 'general_email' );

$social = array(
	'facebook'  => get_theme_mod( 'appiappi_social_facebook' ),
	'linkedin'  => get_theme_mod( 'appiappi_social_linkedin' ),
	'instagram' => get_theme_mod( 'appiappi_social_instagram' ),
	'youtube'   => get_theme_mod( 'appiappi_social_youtube' ),
);
?>
<footer class="site-footer">
	<div class="container">
		<div class="footer-grid">
			<div class="footer-col footer-col--brand">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-branding">
					<span aria-hidden="true" style="color:var(--color-maple)"><?php echo appiappi_icon( 'leaf' ); ?></span>
					<span class="site-branding__name"><?php bloginfo( 'name' ); ?></span>
				</a>
				<p><?php echo esc_html( get_theme_mod( 'appiappi_footer_tagline', __( 'We build, host and grow websites for Canadian businesses.', 'appiappi' ) ) ); ?></p>
				<?php if ( array_filter( $social ) ) : ?>
					<div class="footer-social">
						<?php foreach ( $social as $network => $url ) : ?>
							<?php if ( $url ) : ?>
								<a href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( ucfirst( $network ) ); ?>" target="_blank" rel="noopener noreferrer">
									<?php echo appiappi_icon( $network ); ?>
								</a>
							<?php endif; ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="footer-col">
				<p class="footer-col__title"><?php esc_html_e( 'Quick Links', 'appiappi' ); ?></p>
				<ul class="footer-col__list">
					<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'appiappi' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/templates/' ) ); ?>"><?php esc_html_e( 'Website Designs', 'appiappi' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/pricing/' ) ); ?>"><?php esc_html_e( 'Pricing', 'appiappi' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/portfolio/' ) ); ?>"><?php esc_html_e( 'Portfolio', 'appiappi' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact', 'appiappi' ); ?></a></li>
				</ul>
			</div>

			<?php if ( $footer_services ) : ?>
				<div class="footer-col">
					<p class="footer-col__title"><?php esc_html_e( 'Services', 'appiappi' ); ?></p>
					<ul class="footer-col__list">
						<?php foreach ( $footer_services as $service ) : ?>
							<li><a href="<?php echo esc_url( home_url( '/services/#service-' . $service['id'] ) ); ?>"><?php echo esc_html( $service['name'] ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( $footer_has_contact_box || $footer_fallback_email ) : ?>
				<div class="footer-col">
					<p class="footer-col__title"><?php esc_html_e( 'Contact', 'appiappi' ); ?></p>
					<ul class="footer-col__list footer-contact">
						<?php if ( $footer_has_contact_box ) : ?>
							<?php if ( $footer_address_value ) : ?>
								<li><?php echo appiappi_icon( 'map-pin' ); ?><span><?php echo esc_html( $footer_address_value ); ?></span></li>
							<?php endif; ?>
							<?php if ( $footer_phone_value ) : ?>
								<li>
									<?php echo appiappi_icon( 'phone' ); ?>
									<?php if ( $footer_phone_href ) : ?>
										<a href="<?php echo esc_url( $footer_phone_href ); ?>"><?php echo esc_html( $footer_phone_value ); ?></a>
									<?php else : ?>
										<span><?php echo esc_html( $footer_phone_value ); ?></span>
									<?php endif; ?>
								</li>
							<?php endif; ?>
							<?php if ( $footer_support_email ) : ?>
								<li><?php echo appiappi_icon( 'mail' ); ?><a href="mailto:<?php echo esc_attr( $footer_support_email ); ?>"><?php echo esc_html( $footer_support_email ); ?></a></li>
							<?php endif; ?>
						<?php else : ?>
							<li><?php echo appiappi_icon( 'mail' ); ?><a href="mailto:<?php echo esc_attr( $footer_fallback_email ); ?>"><?php echo esc_html( $footer_fallback_email ); ?></a></li>
						<?php endif; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>

		<div class="footer-bottom">
			<p>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'All rights reserved.', 'appiappi' ); ?></p>
			<div class="footer-bottom__legal">
				<a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>"><?php esc_html_e( 'Privacy Policy', 'appiappi' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/terms/' ) ); ?>"><?php esc_html_e( 'Terms & Conditions', 'appiappi' ); ?></a>
			</div>
		</div>
	</div>
</footer>
