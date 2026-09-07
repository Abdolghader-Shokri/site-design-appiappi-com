<?php
/**
 * Sticky site header: logo, primary nav, header CTA + client login,
 * mobile nav toggle. "Client Login" itself is rendered by the
 * appiappi-client-login plugin (function_exists() guard below, same
 * pattern as the checkout plugin's pricing-card integration) — a
 * "Sign in with Google" link when logged out, or an email + avatar
 * dropdown when logged in; the plain link here is only a fallback for
 * when that plugin is inactive.
 */

$cta_text = get_theme_mod( 'appiappi_cta_text', __( 'Get Started', 'appiappi' ) );
$cta_url  = appiappi_header_cta_url();
?>
<header class="site-header">
	<div class="container site-header__inner">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<span aria-hidden="true" style="color:var(--color-maple)"><?php echo appiappi_icon( 'leaf' ); ?></span>
				<span>
					<span class="site-branding__name"><?php bloginfo( 'name' ); ?></span>
					<span class="site-branding__tagline"><?php esc_html_e( 'Web Design & SEO', 'appiappi' ); ?></span>
				</span>
			<?php endif; ?>
		</a>

		<nav class="primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'appiappi' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'items_wrap'     => '<ul>%3$s</ul>',
				'fallback_cb'    => 'appiappi_nav_fallback',
			) );
			?>
		</nav>

		<div class="header-actions">
			<?php if ( function_exists( 'appiappi_client_login_render_header_widget' ) ) : ?>
				<?php appiappi_client_login_render_header_widget( 'btn btn-link' ); ?>
			<?php else : ?>
				<a href="<?php echo esc_url( home_url( '/account/' ) ); ?>" class="btn btn-link"><?php esc_html_e( 'Client Login', 'appiappi' ); ?></a>
			<?php endif; ?>
			<a href="<?php echo esc_url( $cta_url ); ?>" class="btn btn-primary"><?php echo esc_html( $cta_text ); ?></a>
			<button type="button" class="mobile-nav-toggle" aria-expanded="false" aria-controls="mobile-nav" aria-label="<?php esc_attr_e( 'Open menu', 'appiappi' ); ?>">
				<?php echo appiappi_icon( 'menu' ); ?>
			</button>
		</div>
	</div>

	<div class="mobile-nav" id="mobile-nav">
		<nav class="mobile-nav__list" aria-label="<?php esc_attr_e( 'Mobile', 'appiappi' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'items_wrap'     => '<ul>%3$s</ul>',
				'fallback_cb'    => 'appiappi_nav_fallback',
			) );
			?>
		</nav>
		<div class="mobile-nav__actions">
			<?php if ( function_exists( 'appiappi_client_login_render_header_widget' ) ) : ?>
				<?php appiappi_client_login_render_header_widget( 'btn btn-secondary btn-block' ); ?>
			<?php else : ?>
				<a href="<?php echo esc_url( home_url( '/account/' ) ); ?>" class="btn btn-secondary btn-block"><?php esc_html_e( 'Client Login', 'appiappi' ); ?></a>
			<?php endif; ?>
			<a href="<?php echo esc_url( $cta_url ); ?>" class="btn btn-primary btn-block"><?php echo esc_html( $cta_text ); ?></a>
		</div>
	</div>
</header>
