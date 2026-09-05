<?php
/**
 * Plugin Name: Appiappi Portfolio
 * Plugin URI: https://appiappi.com
 * Description: Manage portfolio projects, shown via the [appiappi_portfolio] shortcode. Built as a companion to the Appiappi theme — see that theme's PROJECT_MASTER.md.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Appiappi
 * Author URI: https://appiappi.com
 * License: Proprietary
 * Text Domain: appiappi-portfolio
 */

defined( 'ABSPATH' ) || exit;

define( 'APPIAPPI_PORTFOLIO_DIR', plugin_dir_path( __FILE__ ) );

require APPIAPPI_PORTFOLIO_DIR . 'includes/cpt.php';
require APPIAPPI_PORTFOLIO_DIR . 'includes/meta-boxes.php';
require APPIAPPI_PORTFOLIO_DIR . 'includes/shortcode.php';
