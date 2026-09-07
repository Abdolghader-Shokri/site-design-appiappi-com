<?php
/**
 * Google OAuth 2.0 — authorization-code flow, plain REST calls (no SDK,
 * no Composer), matching this project's Stripe integration style
 * exactly. The access token is only ever used server-side, once, to
 * call Google's own userinfo endpoint and get an authoritative email —
 * nothing about the signed-in identity is ever trusted from the browser.
 *
 * Flow: appiappi_client_login_render_signin_button() links to Google's
 * consent screen → Google redirects back to
 * appiappi_client_login_redirect_uri() (a REST route) with a one-time
 * code → appiappi_client_login_handle_callback() exchanges it for an
 * access token, fetches the profile, finds/creates the WP user, logs
 * them in, and redirects back to wherever sign-in was started from.
 */

defined( 'ABSPATH' ) || exit;

define( 'APPIAPPI_CLIENT_LOGIN_STATE_TTL', 10 * MINUTE_IN_SECONDS );

function appiappi_client_login_register_route() {
	register_rest_route( 'appiappi/v1', '/google-callback', array(
		'methods'             => 'GET',
		'callback'            => 'appiappi_client_login_handle_callback',
		'permission_callback' => '__return_true', // Google isn't a logged-in WP user; the one-time state value is the actual protection.
	) );
}
add_action( 'rest_api_init', 'appiappi_client_login_register_route' );

/**
 * Builds the Google consent-screen URL for one sign-in attempt. Stores
 * a random, single-use state value in a short-lived transient (rather
 * than trusting anything round-tripped through the browser) alongside
 * the page to return to, so the callback can both verify the request
 * genuinely started here and know where to send the visitor back.
 */
function appiappi_client_login_authorize_url( $redirect_to = '' ) {
	if ( ! appiappi_client_login_is_configured() ) {
		return '';
	}

	$state = wp_generate_password( 32, false );
	set_transient( 'appiappi_client_login_state_' . $state, array(
		'redirect_to' => $redirect_to ? esc_url_raw( $redirect_to ) : home_url( '/' ),
	), APPIAPPI_CLIENT_LOGIN_STATE_TTL );

	$params = array(
		'client_id'     => appiappi_client_login_get_setting( 'google_client_id' ),
		'redirect_uri'  => appiappi_client_login_redirect_uri(),
		'response_type' => 'code',
		'scope'         => 'openid email profile',
		'state'         => $state,
		'prompt'        => 'select_account',
	);

	return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query( $params );
}

/**
 * Renders the "Sign in with Google" link — used both by the header
 * widget (guest state) and the gated Dashboard/Profile page templates.
 * $classes should match whatever button classes the calling context
 * expects (e.g. 'btn btn-link' in the desktop header, 'btn btn-primary'
 * on a gated page) so it fits in visually without extra markup.
 */
function appiappi_client_login_render_signin_button( $classes = 'btn btn-primary' ) {
	$current_url = ! empty( $_SERVER['REQUEST_URI'] ) ? home_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : home_url( '/' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- used only as an internal redirect target, never echoed raw or trusted for auth.

	$url = appiappi_client_login_authorize_url( $current_url );

	if ( ! $url ) {
		?>
		<p class="client-login-not-configured"><?php esc_html_e( 'Sign-in isn\'t set up yet — please check back soon.', 'appiappi-client-login' ); ?></p>
		<?php
		return;
	}
	?>
	<a href="<?php echo esc_url( $url ); ?>" class="client-login-google-btn <?php echo esc_attr( $classes ); ?>">
		<?php echo appiappi_client_login_google_logo_svg(); ?>
		<span><?php esc_html_e( 'Sign in with Google', 'appiappi-client-login' ); ?></span>
	</a>
	<?php
}

/**
 * Google's official multi-colour "G" mark, inline so the button needs
 * no image request — standard practice for a recognisable, trustworthy
 * "Sign in with Google" control rather than an unbranded generic button.
 */
function appiappi_client_login_google_logo_svg() {
	return '<svg class="client-login-google-btn__logo" width="18" height="18" viewBox="0 0 18 18" aria-hidden="true" focusable="false"><path fill="#4285F4" d="M17.64 9.2c0-.64-.06-1.25-.16-1.84H9v3.48h4.84a4.14 4.14 0 0 1-1.8 2.72v2.26h2.9c1.7-1.57 2.7-3.88 2.7-6.62z"/><path fill="#34A853" d="M9 18c2.43 0 4.47-.8 5.96-2.18l-2.9-2.26c-.8.54-1.83.86-3.06.86-2.35 0-4.34-1.59-5.05-3.72H.96v2.33A9 9 0 0 0 9 18z"/><path fill="#FBBC05" d="M3.95 10.7A5.4 5.4 0 0 1 3.67 9c0-.59.1-1.17.28-1.7V4.97H.96A9 9 0 0 0 0 9c0 1.45.35 2.83.96 4.03l2.99-2.33z"/><path fill="#EA4335" d="M9 3.58c1.32 0 2.51.46 3.44 1.35l2.58-2.58C13.46.89 11.43 0 9 0A9 9 0 0 0 .96 4.97l2.99 2.33C4.66 5.17 6.65 3.58 9 3.58z"/></svg>';
}

/**
 * Google's redirect back to us. Deliberately redirects the browser
 * directly (rather than returning a REST JSON body) since this route
 * exists purely as a landing point in a full-page browser flow, not an
 * API a script calls.
 */
function appiappi_client_login_handle_callback( WP_REST_Request $request ) {
	$code  = $request->get_param( 'code' );
	$state = $request->get_param( 'state' );
	$error = $request->get_param( 'error' );

	$state_key  = $state ? 'appiappi_client_login_state_' . $state : '';
	$state_data = $state_key ? get_transient( $state_key ) : false;
	if ( $state_key ) {
		delete_transient( $state_key ); // Single-use, regardless of outcome.
	}

	$redirect_to = ( is_array( $state_data ) && ! empty( $state_data['redirect_to'] ) ) ? $state_data['redirect_to'] : home_url( '/' );

	if ( $error || ! $code || ! $state_data ) {
		wp_safe_redirect( add_query_arg( 'client_login_error', '1', $redirect_to ) );
		exit;
	}

	$token_response = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
		'timeout' => 20,
		'body'    => array(
			'code'          => $code,
			'client_id'     => appiappi_client_login_get_setting( 'google_client_id' ),
			'client_secret' => appiappi_client_login_get_setting( 'google_client_secret' ),
			'redirect_uri'  => appiappi_client_login_redirect_uri(),
			'grant_type'    => 'authorization_code',
		),
	) );

	$access_token = '';
	if ( ! is_wp_error( $token_response ) ) {
		$token_body   = json_decode( wp_remote_retrieve_body( $token_response ), true );
		$access_token = is_array( $token_body ) ? ( $token_body['access_token'] ?? '' ) : '';
	}

	if ( ! $access_token ) {
		wp_safe_redirect( add_query_arg( 'client_login_error', '1', $redirect_to ) );
		exit;
	}

	$profile_response = wp_remote_get( 'https://www.googleapis.com/oauth2/v3/userinfo', array(
		'timeout' => 20,
		'headers' => array( 'Authorization' => 'Bearer ' . $access_token ),
	) );

	$profile = is_wp_error( $profile_response ) ? null : json_decode( wp_remote_retrieve_body( $profile_response ), true );
	if ( ! is_array( $profile ) || empty( $profile['email'] ) ) {
		wp_safe_redirect( add_query_arg( 'client_login_error', '1', $redirect_to ) );
		exit;
	}

	$user = appiappi_client_login_find_or_create_user( $profile );
	if ( is_wp_error( $user ) ) {
		wp_safe_redirect( add_query_arg( 'client_login_error', '1', $redirect_to ) );
		exit;
	}

	wp_clear_auth_cookie();
	wp_set_current_user( $user->ID );
	wp_set_auth_cookie( $user->ID, true );

	wp_safe_redirect( $redirect_to );
	exit;
}

/**
 * Small dismissible banner shown when a sign-in attempt failed — reads
 * the ?client_login_error=1 the callback above adds. client-login.js
 * strips the query arg from the address bar once it's dismissed/shown.
 */
function appiappi_client_login_render_error_notice() {
	if ( empty( $_GET['client_login_error'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- purely a display flag, not an action.
		return;
	}
	?>
	<div class="client-login-error-banner" role="alert">
		<span><?php esc_html_e( 'Sign-in with Google didn\'t go through. Please try again.', 'appiappi-client-login' ); ?></span>
		<button type="button" class="client-login-error-banner__close" aria-label="<?php esc_attr_e( 'Dismiss', 'appiappi-client-login' ); ?>">&times;</button>
	</div>
	<?php
}
add_action( 'wp_body_open', 'appiappi_client_login_render_error_notice' );
