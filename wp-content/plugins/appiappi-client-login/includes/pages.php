<?php
/**
 * Creates the two pages the client portal needs a URL for — Dashboard
 * (slug "account", matching the theme header's existing /account/ link)
 * and Profile (a child of Dashboard, so its URL is /account/profile/) —
 * once, on activation, and serves the plugin's own minimal templates
 * for them via template_include. Both pages' actual content is
 * intentionally minimal for now: this phase is the sign-in mechanism
 * and navigation, not the dashboard/profile features themselves — see
 * PROJECT_MASTER.md for what's still pending.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_client_login_create_pages() {
	if ( ! get_option( 'appiappi_client_login_dashboard_page_id' ) ) {
		$dashboard_id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_title'   => __( 'Dashboard', 'appiappi-client-login' ),
			'post_name'    => 'account',
			'post_status'  => 'publish',
			'post_content' => '',
		) );
		if ( $dashboard_id && ! is_wp_error( $dashboard_id ) ) {
			update_option( 'appiappi_client_login_dashboard_page_id', $dashboard_id );
		}
	}

	if ( ! get_option( 'appiappi_client_login_profile_page_id' ) ) {
		$profile_id = wp_insert_post( array(
			'post_type'    => 'page',
			'post_title'   => __( 'Profile', 'appiappi-client-login' ),
			'post_name'    => 'profile',
			'post_status'  => 'publish',
			'post_parent'  => appiappi_client_login_dashboard_page_id(),
			'post_content' => '',
		) );
		if ( $profile_id && ! is_wp_error( $profile_id ) ) {
			update_option( 'appiappi_client_login_profile_page_id', $profile_id );
		}
	}
}

function appiappi_client_login_dashboard_page_id() {
	return (int) get_option( 'appiappi_client_login_dashboard_page_id' );
}

function appiappi_client_login_profile_page_id() {
	return (int) get_option( 'appiappi_client_login_profile_page_id' );
}

function appiappi_client_login_dashboard_url() {
	$id = appiappi_client_login_dashboard_page_id();
	return $id ? get_permalink( $id ) : home_url( '/account/' );
}

function appiappi_client_login_profile_url() {
	$id = appiappi_client_login_profile_page_id();
	return $id ? get_permalink( $id ) : home_url( '/account/profile/' );
}

/**
 * is_page(0) never matches a real page, so this is safe even before
 * activation has run (option not set yet, ids are 0).
 */
function appiappi_client_login_template_include( $template ) {
	if ( is_page( appiappi_client_login_dashboard_page_id() ) ) {
		$custom = APPIAPPI_CLIENT_LOGIN_DIR . 'templates/page-dashboard.php';
		if ( file_exists( $custom ) ) {
			return $custom;
		}
	}
	if ( is_page( appiappi_client_login_profile_page_id() ) ) {
		$custom = APPIAPPI_CLIENT_LOGIN_DIR . 'templates/page-profile.php';
		if ( file_exists( $custom ) ) {
			return $custom;
		}
	}
	return $template;
}
add_filter( 'template_include', 'appiappi_client_login_template_include' );
