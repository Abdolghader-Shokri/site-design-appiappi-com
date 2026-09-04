<?php
/**
 * Plugin Name: Appiappi Hero Slider
 * Plugin URI: https://appiappi.com
 * Description: Manage the homepage hero's rotating slides, shown via the [appiappi_hero_slider] shortcode. Built as a companion to the Appiappi theme — see that theme's PROJECT_MASTER.md.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Appiappi
 * Author URI: https://appiappi.com
 * License: Proprietary
 * Text Domain: appiappi-hero-slider
 */

defined( 'ABSPATH' ) || exit;

define( 'APPIAPPI_HERO_VERSION', '0.1.0' );
define( 'APPIAPPI_HERO_DIR', plugin_dir_path( __FILE__ ) );

require APPIAPPI_HERO_DIR . 'includes/cpt.php';
require APPIAPPI_HERO_DIR . 'includes/meta-boxes.php';
require APPIAPPI_HERO_DIR . 'includes/shortcode.php';

function appiappi_hero_admin_notice_no_thumbnails() {
	if ( ! current_theme_supports( 'post-thumbnails' ) && current_user_can( 'activate_plugins' ) ) {
		echo '<div class="notice notice-warning"><p>' .
			esc_html__( 'Appiappi Hero Slider: the active theme does not support featured images, so slide images will not display.', 'appiappi-hero-slider' ) .
			'</p></div>';
	}
}
add_action( 'admin_notices', 'appiappi_hero_admin_notice_no_thumbnails' );
