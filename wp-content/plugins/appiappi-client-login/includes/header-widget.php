<?php
/**
 * Replaces the theme's static "Client Login" link — the theme's
 * site-header.php calls this through a function_exists() guard (the
 * same integration pattern appiappi-checkout uses for the pricing
 * cards), so the theme stays decoupled and still has a plain fallback
 * link if this plugin is ever deactivated.
 */

defined( 'ABSPATH' ) || exit;

/**
 * @param string $classes Button classes matching whatever the calling
 *                         slot expects (e.g. 'btn btn-link' in the
 *                         desktop header bar, 'btn btn-secondary
 *                         btn-block' in the mobile nav panel) — only
 *                         used for the guest-state "Sign in with
 *                         Google" button; the logged-in dropdown
 *                         trigger has its own fixed styling either way.
 */
function appiappi_client_login_render_header_widget( $classes = 'btn btn-link' ) {
	if ( ! is_user_logged_in() ) {
		appiappi_client_login_render_signin_button( $classes );
		return;
	}

	$user = wp_get_current_user();
	?>
	<div class="client-login-widget">
		<button type="button" class="client-login-widget__trigger" aria-haspopup="true" aria-expanded="false">
			<?php echo appiappi_icon( 'user', 'client-login-widget__avatar' ); ?>
			<span class="client-login-widget__email"><?php echo esc_html( $user->user_email ); ?></span>
			<?php echo appiappi_icon( 'chevron-down', 'client-login-widget__caret' ); ?>
		</button>
		<div class="client-login-widget__menu" role="menu">
			<a href="<?php echo esc_url( appiappi_client_login_profile_url() ); ?>" role="menuitem">
				<?php echo appiappi_icon( 'user' ); ?> <?php esc_html_e( 'Profile', 'appiappi-client-login' ); ?>
			</a>
			<a href="<?php echo esc_url( appiappi_client_login_dashboard_url() ); ?>" role="menuitem">
				<?php echo appiappi_icon( 'grid' ); ?> <?php esc_html_e( 'Dashboard', 'appiappi-client-login' ); ?>
			</a>
			<a href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>" role="menuitem" class="client-login-widget__logout">
				<?php echo appiappi_icon( 'log-out' ); ?> <?php esc_html_e( 'Log Out', 'appiappi-client-login' ); ?>
			</a>
		</div>
	</div>
	<?php
}
