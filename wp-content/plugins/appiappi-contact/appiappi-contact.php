<?php
/**
 * Plugin Name: Appiappi Contact
 * Plugin URI: https://appiappi.com
 * Description: Contact form + lead capture, shown via the [appiappi_contact_form] shortcode. Submissions are stored as Leads in wp-admin (not email-only) and also emailed to the site admin. Built as a companion to the Appiappi theme — see that theme's PROJECT_MASTER.md.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Author: Appiappi
 * Author URI: https://appiappi.com
 * License: Proprietary
 * Text Domain: appiappi-contact
 */

defined( 'ABSPATH' ) || exit;

define( 'APPIAPPI_CONTACT_DIR', plugin_dir_path( __FILE__ ) );

require APPIAPPI_CONTACT_DIR . 'includes/cpt.php';
require APPIAPPI_CONTACT_DIR . 'includes/handler.php';
require APPIAPPI_CONTACT_DIR . 'includes/shortcode.php';
