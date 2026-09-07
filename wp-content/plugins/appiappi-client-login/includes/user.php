<?php
/**
 * A dedicated, low-privilege "Client" role (just 'read', nothing else)
 * plus the find-or-create logic that turns a verified Google profile
 * into a WordPress user. There is deliberately no separate registration
 * form anywhere in this plugin — successfully signing in with Google
 * for the first time *is* how an account gets created.
 */

defined( 'ABSPATH' ) || exit;

define( 'APPIAPPI_CLIENT_LOGIN_ROLE', 'appiappi_client' );

function appiappi_client_login_register_role() {
	if ( ! get_role( APPIAPPI_CLIENT_LOGIN_ROLE ) ) {
		add_role( APPIAPPI_CLIENT_LOGIN_ROLE, __( 'Client', 'appiappi-client-login' ), array( 'read' => true ) );
	}
}

/**
 * Looks up the WP user matching a verified Google profile's email, or
 * creates one. $google_profile is whatever Google's userinfo endpoint
 * returned for the access token we obtained server-side during the
 * OAuth exchange — never anything the browser could have tampered with.
 *
 * @return WP_User|WP_Error
 */
function appiappi_client_login_find_or_create_user( $google_profile ) {
	$email = isset( $google_profile['email'] ) ? sanitize_email( $google_profile['email'] ) : '';
	if ( ! $email || ! is_email( $email ) ) {
		return new WP_Error( 'appiappi_client_login_bad_email', __( 'Google did not return a usable email address.', 'appiappi-client-login' ) );
	}

	$existing = get_user_by( 'email', $email );
	if ( $existing ) {
		return $existing;
	}

	$name     = isset( $google_profile['name'] ) ? sanitize_text_field( $google_profile['name'] ) : $email;
	$username = appiappi_client_login_unique_username( $email );

	$user_id = wp_insert_user( array(
		'user_login'   => $username,
		'user_email'   => $email,
		'user_pass'    => wp_generate_password( 32 ), // Never used — sign-in is Google-only, there is no password login path for this role.
		'display_name' => $name,
		'first_name'   => $name,
		'role'         => APPIAPPI_CLIENT_LOGIN_ROLE,
	) );

	if ( is_wp_error( $user_id ) ) {
		return $user_id;
	}

	if ( ! empty( $google_profile['sub'] ) ) {
		update_user_meta( $user_id, 'appiappi_client_login_google_id', sanitize_text_field( $google_profile['sub'] ) );
	}

	return get_user_by( 'id', $user_id );
}

/**
 * The part before @ in the email, de-duplicated with a numeric suffix
 * if it's already taken — user_login is otherwise invisible to the
 * customer (they only ever see their email, never a "username").
 */
function appiappi_client_login_unique_username( $email ) {
	$base = sanitize_user( current( explode( '@', $email ) ), true );
	if ( ! $base ) {
		$base = 'client';
	}
	$username = $base;
	$i = 1;
	while ( username_exists( $username ) ) {
		$username = $base . $i;
		$i++;
	}
	return $username;
}

/**
 * Client accounts only ever have the 'read' capability — wp-admin would
 * just be a confusing, irrelevant screen for them. Send them to their
 * Dashboard page instead of letting them land in wp-admin at all.
 */
function appiappi_client_login_redirect_from_admin() {
	if ( wp_doing_ajax() || ! is_user_logged_in() ) {
		return;
	}
	$user = wp_get_current_user();
	if ( in_array( APPIAPPI_CLIENT_LOGIN_ROLE, (array) $user->roles, true ) ) {
		wp_safe_redirect( appiappi_client_login_dashboard_url() );
		exit;
	}
}
add_action( 'admin_init', 'appiappi_client_login_redirect_from_admin' );
