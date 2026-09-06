<?php
/**
 * Plugin Name: Appiappi Checkout
 * Plugin URI: https://appiappi.com
 * Description: Stripe-powered checkout for pricing plans (with an optional Website Design line item) — plan selection, a themed invoice, monthly/annual billing with a configurable annual discount, and Stripe Payment Element for the actual card/wallet details. Orders are stored as Orders in wp-admin. Built as a companion to the Appiappi theme — see that theme's PROJECT_MASTER.md.
 * Version: 0.1.1
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Appiappi
 * Author URI: https://appiappi.com
 * License: Proprietary
 * Text Domain: appiappi-checkout
 */

defined( 'ABSPATH' ) || exit;

define( 'APPIAPPI_CHECKOUT_DIR', plugin_dir_path( __FILE__ ) );
define( 'APPIAPPI_CHECKOUT_URL', plugin_dir_url( __FILE__ ) );
define( 'APPIAPPI_CHECKOUT_VERSION', '0.1.1' );

require APPIAPPI_CHECKOUT_DIR . 'includes/settings.php';
require APPIAPPI_CHECKOUT_DIR . 'includes/cpt.php';
require APPIAPPI_CHECKOUT_DIR . 'includes/hosting-cpt.php';
require APPIAPPI_CHECKOUT_DIR . 'includes/stripe-client.php';
require APPIAPPI_CHECKOUT_DIR . 'includes/pricing.php';
require APPIAPPI_CHECKOUT_DIR . 'includes/ajax.php';
require APPIAPPI_CHECKOUT_DIR . 'includes/webhook.php';
require APPIAPPI_CHECKOUT_DIR . 'includes/checkout-ui.php';
