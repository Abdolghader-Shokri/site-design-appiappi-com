<?php
/**
 * Four-column footer: brand, quick links, services, contact + social.
 * Contact/social values come from the Customizer (inc/customizer.php)
 * so they're editable without touching code.
 */

$phone   = get_theme_mod( 'appiappi_phone' );
$email   = get_theme_mod( 'appiappi_email' );
$address = get_theme_mod( 'appiappi_address' );

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

			<div class="footer-col">
				<p class="footer-col__title"><?php esc_html_e( 'Services', 'appiappi' ); ?></p>
				<ul class="footer-col__list">
					<li><a href="<?php echo esc_url( home_url( '/services/#design' ) ); ?>"><?php esc_html_e( 'Website Design', 'appiappi' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/services/#management' ) ); ?>"><?php esc_html_e( 'Website Management', 'appiappi' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/services/#seo' ) ); ?>"><?php esc_html_e( 'SEO', 'appiappi' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/services/#hosting' ) ); ?>"><?php esc_html_e( 'Managed Hosting', 'appiappi' ); ?></a></li>
				</ul>
			</div>

			<div class="footer-col">
				<p class="footer-col__title"><?php esc_html_e( 'Contact', 'appiappi' ); ?></p>
				<ul class="footer-col__list footer-contact">
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
