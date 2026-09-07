<?php
/**
 * Client Profile (URL: /account/profile/, page created on plugin
 * activation — see includes/pages.php). Content is intentionally
 * minimal for now, same reasoning as page-dashboard.php.
 */

get_header();
?>
<main id="main-content">
	<?php appiappi_breadcrumbs(); ?>
	<?php appiappi_page_header( __( 'Your account details, from your Google sign-in.', 'appiappi-client-login' ) ); ?>

	<article class="section">
		<div class="container single-post">
			<?php if ( is_user_logged_in() ) : ?>
				<?php $user = wp_get_current_user(); ?>
				<div class="card client-account-card">
					<p class="client-account-card__welcome"><?php echo esc_html( $user->display_name ); ?></p>
					<p class="client-account-card__email"><?php echo esc_html( $user->user_email ); ?></p>
					<p><?php esc_html_e( 'Profile settings will be available here soon.', 'appiappi-client-login' ); ?></p>
				</div>
			<?php else : ?>
				<div class="card client-account-card client-account-card--signin">
					<p><?php esc_html_e( 'Sign in with your Google account to view your profile.', 'appiappi-client-login' ); ?></p>
					<?php appiappi_client_login_render_signin_button( 'btn btn-primary' ); ?>
				</div>
			<?php endif; ?>
		</div>
	</article>
</main>
<?php
get_footer();
