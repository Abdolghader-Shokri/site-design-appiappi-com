<?php
/**
 * Settings → Client Login: the only things that should ever need
 * touching after moving this site to real hosting, same philosophy as
 * appiappi-checkout's settings page. The redirect URI is a REST route
 * built from rest_url(), so it's correct on any domain automatically;
 * the one unavoidable manual step is pasting that URL into Google Cloud
 * Console's OAuth Client once (Google has no way to discover it on its
 * own), same as Stripe's webhook registration step.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_client_login_get_setting( $key, $default = '' ) {
	$settings = get_option( 'appiappi_client_login_settings', array() );
	return isset( $settings[ $key ] ) && '' !== $settings[ $key ] ? $settings[ $key ] : $default;
}

function appiappi_client_login_is_configured() {
	return (bool) appiappi_client_login_get_setting( 'google_client_id' ) && (bool) appiappi_client_login_get_setting( 'google_client_secret' );
}

function appiappi_client_login_redirect_uri() {
	return rest_url( 'appiappi/v1/google-callback' );
}

function appiappi_client_login_settings_fields() {
	return array(
		'google_client_id'     => array(
			'label'       => __( 'Google Client ID', 'appiappi-client-login' ),
			'type'        => 'text',
			'placeholder' => 'xxxxxxxxxxxx.apps.googleusercontent.com',
		),
		'google_client_secret' => array(
			'label'       => __( 'Google Client Secret', 'appiappi-client-login' ),
			'type'        => 'password',
			'placeholder' => 'GOCSPX-…',
		),
	);
}

function appiappi_client_login_settings_sanitize( $input ) {
	$fields = appiappi_client_login_settings_fields();
	$output = array();
	foreach ( $fields as $key => $field ) {
		if ( isset( $input[ $key ] ) ) {
			$output[ $key ] = sanitize_text_field( wp_unslash( $input[ $key ] ) );
		}
	}
	return $output;
}

function appiappi_client_login_settings_register() {
	register_setting( 'appiappi_client_login_settings_group', 'appiappi_client_login_settings', array(
		'sanitize_callback' => 'appiappi_client_login_settings_sanitize',
		'default'           => array(),
	) );
}
add_action( 'admin_init', 'appiappi_client_login_settings_register' );

function appiappi_client_login_settings_menu() {
	add_options_page(
		__( 'Client Login Settings', 'appiappi-client-login' ),
		__( 'Client Login', 'appiappi-client-login' ),
		'manage_options',
		'appiappi-client-login-settings',
		'appiappi_client_login_render_settings_page'
	);
}
add_action( 'admin_menu', 'appiappi_client_login_settings_menu' );

function appiappi_client_login_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Appiappi Client Login — Settings', 'appiappi-client-login' ); ?></h1>
		<p><?php esc_html_e( 'Lets customers sign in with their Google account — no separate registration or password to manage. Moving the site to real hosting later should only ever require updating the two fields below and the redirect URI in Google Cloud Console.', 'appiappi-client-login' ); ?></p>

		<?php if ( ! appiappi_client_login_is_configured() ) : ?>
			<div class="notice notice-warning"><p><?php esc_html_e( 'No Google credentials set yet — the "Sign in with Google" button will show a friendly "not set up yet" message until both fields below are filled in.', 'appiappi-client-login' ); ?></p></div>
		<?php else : ?>
			<div class="notice notice-success"><p><?php esc_html_e( 'Google sign-in is configured and active.', 'appiappi-client-login' ); ?></p></div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'appiappi_client_login_settings_group' ); ?>
			<table class="form-table">
				<?php foreach ( appiappi_client_login_settings_fields() as $key => $field ) : ?>
					<tr>
						<th><label for="appiappi_client_login_settings_<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field['label'] ); ?></label></th>
						<td>
							<input type="<?php echo esc_attr( $field['type'] ); ?>" id="appiappi_client_login_settings_<?php echo esc_attr( $key ); ?>" name="appiappi_client_login_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( appiappi_client_login_get_setting( $key ) ); ?>" class="regular-text" placeholder="<?php echo esc_attr( $field['placeholder'] ?? '' ); ?>" autocomplete="off">
						</td>
					</tr>
				<?php endforeach; ?>
				<tr>
					<th><?php esc_html_e( 'Authorized Redirect URI', 'appiappi-client-login' ); ?></th>
					<td>
						<input type="text" readonly value="<?php echo esc_attr( appiappi_client_login_redirect_uri() ); ?>" class="regular-text code" onclick="this.select()">
						<p class="description"><?php esc_html_e( 'In Google Cloud Console → APIs & Services → Credentials → your OAuth 2.0 Client ID, add this exact URL under "Authorized redirect URIs". That\'s the only manual step — once it\'s added there and the keys above are saved here, sign-in works automatically on whatever domain the site is on.', 'appiappi-client-login' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
