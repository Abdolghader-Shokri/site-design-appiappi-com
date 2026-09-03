<?php
/**
 * Appiappi theme bootstrap.
 *
 * Each concern lives in its own file under /inc/ — see PROJECT_MASTER.md
 * "File Location Map" for what controls what. Keep this file to
 * requires/includes only; do not add hooks or functions directly here.
 */

defined( 'ABSPATH' ) || exit;

require get_template_directory() . '/inc/setup.php';
require get_template_directory() . '/inc/enqueue.php';
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/template-tags.php';
