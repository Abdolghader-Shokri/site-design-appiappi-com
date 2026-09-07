<?php
/**
 * Client Dashboard (URL: /account/, page created on plugin activation
 * — see includes/pages.php). Content is intentionally minimal for now:
 * this phase is the sign-in mechanism and header/menu navigation, not
 * the dashboard's real features (project status, invoices, support) —
 * see PROJECT_MASTER.md for what's still pending.
 */

get_header();
?>
<main id="main-content">
	<?php appiappi_breadcrumbs(); ?>
	<?php appiappi_page_header( __( 'Manage your account and keep track of your website with Appiappi.', 'appiappi-client-login' ) ); ?>

	<article class="section">
		<div class="container single-post">
			<?php if ( is_user_logged_in() ) : ?>
				<?php $user = wp_get_current_user(); ?>
				<div class="card client-account-card">
					<p class="client-account-card__welcome">
						<?php
						printf(
							/* translators: %s: display name */
							esc_html__( 'Welcome back, %s.', 'appiappi-client-login' ),
							esc_html( $user->display_name )
						);
						?>
					</p>
					<p class="client-account-card__email"><?php echo esc_html( $user->user_email ); ?></p>
					<p><?php esc_html_e( "Your dashboard is being built out — project status, invoices and support requests will show up here. Check back soon.", 'appiappi-client-login' ); ?></p>
				</div>
			<?php else : ?>
				<div class="card client-account-card client-account-card--signin">
					<p><?php esc_html_e( 'Sign in with your Google account to view your dashboard.', 'appiappi-client-login' ); ?></p>
					<?php appiappi_client_login_render_signin_button( 'btn btn-primary' ); ?>
				</div>
			<?php endif; ?>
		</div>
	</article>
</main>
<?php
get_footer();
