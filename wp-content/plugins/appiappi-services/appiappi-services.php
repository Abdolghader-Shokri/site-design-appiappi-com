<?php
/**
 * Plugin Name: Appiappi Services
 * Plugin URI: https://appiappi.com
 * Description: Manage the Services page content via the [appiappi_services] shortcode. Built as a companion to the Appiappi theme — see that theme's PROJECT_MASTER.md.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Appiappi
 * Author URI: https://appiappi.com
 * License: Proprietary
 * Text Domain: appiappi-services
 */

defined( 'ABSPATH' ) || exit;

define( 'APPIAPPI_SERVICES_VERSION', '0.1.0' );
define( 'APPIAPPI_SERVICES_DIR', plugin_dir_path( __FILE__ ) );

require APPIAPPI_SERVICES_DIR . 'includes/cpt.php';
require APPIAPPI_SERVICES_DIR . 'includes/meta-boxes.php';
require APPIAPPI_SERVICES_DIR . 'includes/shortcode.php';
