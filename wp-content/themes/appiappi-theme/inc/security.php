<?php
/**
 * Baseline security hardening that's safe to apply from theme code.
 * Some standard hardening (DISALLOW_FILE_EDIT, DISALLOW_FILE_MODS) can
 * only be set in wp-config.php, which lives outside version control —
 * see PROJECT_MASTER.md § Security for the recommended snippet to add
 * there per environment.
 */

defined( 'ABSPATH' ) || exit;

// Don't advertise the exact WordPress version to every visitor.
remove_action( 'wp_head', 'wp_generator' );
add_filter( 'the_generator', '__return_empty_string' );

// XML-RPC is a common brute-force/pingback-abuse target and this site
// doesn't use it (no Jetpack, no remote publishing clients).
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * A few standard, low-risk response headers. Real caching/HSTS/CSP
 * policy belongs at the hosting/CDN layer (Phase 4 deployment target),
 * not hard-coded in the theme — these are the safe, generic ones.
 */
function appiappi_security_headers() {
	if ( headers_sent() ) {
		return;
	}
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
}
add_action( 'send_headers', 'appiappi_security_headers' );

/**
 * Login error messages shouldn't confirm whether a username/email
 * exists — helps a little against account enumeration.
 */
add_filter( 'login_errors', function () {
	return __( 'Incorrect username/email or password.', 'appiappi' );
} );
