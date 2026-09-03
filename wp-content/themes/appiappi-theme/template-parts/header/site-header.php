<?php
/**
 * Sticky site header: logo, primary nav, header CTA + client login,
 * mobile nav toggle. The "Client Login" link is a placeholder target
 * for the future customer portal (see PROJECT_MASTER.md, Phase 5) —
 * it is a secondary/ghost action, not the primary CTA.
 */

$cta_text = get_theme_mod( 'appiappi_cta_text', __( 'Get Started', 'appiappi' ) );
$cta_url  = get_theme_mod( 'appiappi_cta_url', '#pricing' );
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
			<a href="<?php echo esc_url( home_url( '/account/' ) ); ?>" class="btn btn-link"><?php esc_html_e( 'Client Login', 'appiappi' ); ?></a>
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
			<a href="<?php echo esc_url( home_url( '/account/' ) ); ?>" class="btn btn-secondary btn-block"><?php esc_html_e( 'Client Login', 'appiappi' ); ?></a>
			<a href="<?php echo esc_url( $cta_url ); ?>" class="btn btn-primary btn-block"><?php echo esc_html( $cta_text ); ?></a>
		</div>
	</div>
</header>
