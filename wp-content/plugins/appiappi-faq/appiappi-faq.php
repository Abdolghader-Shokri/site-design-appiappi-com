<?php
/**
 * Plugin Name: Appiappi FAQ
 * Plugin URI: https://appiappi.com
 * Description: Manage frequently asked questions, shown via the [appiappi_faq] shortcode. Built as a companion to the Appiappi theme — see that theme's PROJECT_MASTER.md.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Appiappi
 * Author URI: https://appiappi.com
 * License: Proprietary
 * Text Domain: appiappi-faq
 */

defined( 'ABSPATH' ) || exit;

define( 'APPIAPPI_FAQ_DIR', plugin_dir_path( __FILE__ ) );

require APPIAPPI_FAQ_DIR . 'includes/cpt.php';
require APPIAPPI_FAQ_DIR . 'includes/shortcode.php';
