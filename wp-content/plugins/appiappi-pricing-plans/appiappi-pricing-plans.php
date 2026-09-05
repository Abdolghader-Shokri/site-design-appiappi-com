<?php
/**
 * Plugin Name: Appiappi Pricing Plans
 * Plugin URI: https://appiappi.com
 * Description: Manage the website design & support pricing plans shown via the [appiappi_pricing] shortcode. Built as a companion to the Appiappi theme — see that theme's PROJECT_MASTER.md.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Appiappi
 * Author URI: https://appiappi.com
 * License: Proprietary
 * Text Domain: appiappi-pricing-plans
 */

defined( 'ABSPATH' ) || exit;

define( 'APPIAPPI_PRICING_PLANS_VERSION', '0.1.0' );
define( 'APPIAPPI_PRICING_PLANS_DIR', plugin_dir_path( __FILE__ ) );

require APPIAPPI_PRICING_PLANS_DIR . 'includes/cpt.php';
require APPIAPPI_PRICING_PLANS_DIR . 'includes/meta-boxes.php';
require APPIAPPI_PRICING_PLANS_DIR . 'includes/shortcode.php';
require APPIAPPI_PRICING_PLANS_DIR . 'includes/settings.php';
