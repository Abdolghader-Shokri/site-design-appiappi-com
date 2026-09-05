<?php
/**
 * Plugin Name: Appiappi Template Showcase
 * Plugin URI: https://appiappi.com
 * Description: Manage the curated website-design library shown via the [appiappi_templates] shortcode. Built as a companion to the Appiappi theme — see that theme's PROJECT_MASTER.md.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Appiappi
 * Author URI: https://appiappi.com
 * License: Proprietary
 * Text Domain: appiappi-template-showcase
 */

defined( 'ABSPATH' ) || exit;

define( 'APPIAPPI_SHOWCASE_VERSION', '0.1.0' );
define( 'APPIAPPI_SHOWCASE_DIR', plugin_dir_path( __FILE__ ) );

require APPIAPPI_SHOWCASE_DIR . 'includes/cpt.php';
require APPIAPPI_SHOWCASE_DIR . 'includes/taxonomy-meta.php';
require APPIAPPI_SHOWCASE_DIR . 'includes/meta-boxes.php';
require APPIAPPI_SHOWCASE_DIR . 'includes/shortcode.php';
require APPIAPPI_SHOWCASE_DIR . 'includes/settings.php';

/**
 * Featured images require post-thumbnails theme support, which the
 * Appiappi theme already declares globally — but flag it if this plugin
 * is ever used with a different theme.
 */
function appiappi_showcase_admin_notice_no_thumbnails() {
	if ( ! current_theme_supports( 'post-thumbnails' ) && current_user_can( 'activate_plugins' ) ) {
		echo '<div class="notice notice-warning"><p>' .
			esc_html__( 'Appiappi Template Showcase: the active theme does not support featured images, so template preview images will not display.', 'appiappi-template-showcase' ) .
			'</p></div>';
	}
}
add_action( 'admin_notices', 'appiappi_showcase_admin_notice_no_thumbnails' );
