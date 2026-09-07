<?php
/**
 * Plugin Name: Appiappi Client Login
 * Plugin URI: https://appiappi.com
 * Description: "Sign in with Google" for customer accounts — no separate registration form. First sign-in auto-provisions a WordPress user (a dedicated, low-privilege "Client" role), the header's "Client Login" link becomes the signed-in customer's email + avatar menu (Profile, Dashboard, Log Out), and a minimal Dashboard/Profile page pair is created to link to. Built as a companion to the Appiappi theme — see that theme's PROJECT_MASTER.md.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Appiappi
 * Author URI: https://appiappi.com
 * License: Proprietary
 * Text Domain: appiappi-client-login
 */

defined( 'ABSPATH' ) || exit;

define( 'APPIAPPI_CLIENT_LOGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'APPIAPPI_CLIENT_LOGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'APPIAPPI_CLIENT_LOGIN_VERSION', '0.1.0' );

require APPIAPPI_CLIENT_LOGIN_DIR . 'includes/settings.php';
require APPIAPPI_CLIENT_LOGIN_DIR . 'includes/user.php';
require APPIAPPI_CLIENT_LOGIN_DIR . 'includes/pages.php';
require APPIAPPI_CLIENT_LOGIN_DIR . 'includes/oauth.php';
require APPIAPPI_CLIENT_LOGIN_DIR . 'includes/header-widget.php';

function appiappi_client_login_activate() {
	appiappi_client_login_register_role();
	appiappi_client_login_create_pages();
}
register_activation_hook( __FILE__, 'appiappi_client_login_activate' );

/**
 * Loaded on every front-end page (not gated like the checkout plugin's
 * pricing-page-only assets) since the header widget this replaces
 * appears in the header on every page.
 */
function appiappi_client_login_enqueue_assets() {
	wp_enqueue_style( 'appiappi-client-login', APPIAPPI_CLIENT_LOGIN_URL . 'assets/client-login.css', array(), APPIAPPI_CLIENT_LOGIN_VERSION );
	wp_enqueue_script( 'appiappi-client-login', APPIAPPI_CLIENT_LOGIN_URL . 'assets/client-login.js', array(), APPIAPPI_CLIENT_LOGIN_VERSION, true );
}
add_action( 'wp_enqueue_scripts', 'appiappi_client_login_enqueue_assets' );
