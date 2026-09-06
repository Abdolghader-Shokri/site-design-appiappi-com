<?php
/**
 * Plugin-wide display settings for the /templates/ "Website Designs"
 * archive page — how many designs sit side by side, and how many rows
 * before paginating to a new page. The homepage teaser and any manual
 * [appiappi_templates] shortcode instance are unaffected (they pass
 * their own explicit count/columns). Read by the theme's
 * appiappi_render_template_showcase() and this plugin's
 * appiappi_showcase_archive_query() via get_option() — loose coupling,
 * a plain stored number, no function dependency in either direction.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_showcase_settings_menu() {
	add_submenu_page(
		'edit.php?post_type=appiappi_template',
		__( 'Website Designs Display Settings', 'appiappi-template-showcase' ),
		__( 'Display Settings', 'appiappi-template-showcase' ),
		'manage_options',
		'appiappi-showcase-settings',
		'appiappi_showcase_render_settings_page'
	);
}
add_action( 'admin_menu', 'appiappi_showcase_settings_menu' );

function appiappi_showcase_settings_sanitize_columns( $value ) {
	return max( 1, min( 4, (int) $value ) );
}

function appiappi_showcase_settings_sanitize_rows( $value ) {
	return max( 1, min( 10, (int) $value ) );
}

function appiappi_showcase_settings_sanitize_carousel_interval( $value ) {
	return max( 1000, min( 15000, (int) $value ) );
}

function appiappi_showcase_settings_register() {
	register_setting( 'appiappi_showcase_settings_group', 'appiappi_templates_columns', array(
		'type'              => 'integer',
		'sanitize_callback' => 'appiappi_showcase_settings_sanitize_columns',
		'default'           => 3,
	) );
	register_setting( 'appiappi_showcase_settings_group', 'appiappi_templates_rows_per_page', array(
		'type'              => 'integer',
		'sanitize_callback' => 'appiappi_showcase_settings_sanitize_rows',
		'default'           => 4,
	) );
	register_setting( 'appiappi_showcase_settings_group', 'appiappi_templates_carousel_interval', array(
		'type'              => 'integer',
		'sanitize_callback' => 'appiappi_showcase_settings_sanitize_carousel_interval',
		'default'           => 3000,
	) );
}
add_action( 'admin_init', 'appiappi_showcase_settings_register' );

function appiappi_showcase_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$columns = (int) get_option( 'appiappi_templates_columns', 3 );
	$rows    = (int) get_option( 'appiappi_templates_rows_per_page', 4 );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Website Designs Display Settings', 'appiappi-template-showcase' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'appiappi_showcase_settings_group' ); ?>
			<table class="form-table">
				<tr>
					<th><label for="appiappi_templates_columns"><?php esc_html_e( 'Designs Per Row (Desktop)', 'appiappi-template-showcase' ); ?></label></th>
					<td>
						<select id="appiappi_templates_columns" name="appiappi_templates_columns">
							<?php for ( $i = 1; $i <= 4; $i++ ) : ?>
								<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $columns, $i ); ?>><?php echo esc_html( $i ); ?></option>
							<?php endfor; ?>
						</select>
						<p class="description"><?php esc_html_e( 'How many design cards sit side by side on desktop on the Website Designs page. Tablet is automatically capped at 2 regardless of this setting; mobile always shows one design per row. If the last row has fewer designs than this number, that row centres on the page instead of stretching edge to edge.', 'appiappi-template-showcase' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><label for="appiappi_templates_rows_per_page"><?php esc_html_e( 'Rows Per Page', 'appiappi-template-showcase' ); ?></label></th>
					<td>
						<input type="number" id="appiappi_templates_rows_per_page" name="appiappi_templates_rows_per_page" min="1" max="10" value="<?php echo esc_attr( $rows ); ?>">
						<p class="description">
							<?php
							printf(
								/* translators: 1: designs per row, 2: rows per page, 3: resulting total per page */
								esc_html__( 'How many rows show before the Website Designs page paginates to a new page. With %1$d per row and %2$d rows, that is %3$d designs per page.', 'appiappi-template-showcase' ),
								(int) $columns,
								(int) $rows,
								(int) $columns * (int) $rows
							);
							?>
						</p>
					</td>
				</tr>
				<tr>
					<th><label for="appiappi_templates_carousel_interval"><?php esc_html_e( 'Image Carousel Auto-Advance (ms)', 'appiappi-template-showcase' ); ?></label></th>
					<td>
						<input type="number" id="appiappi_templates_carousel_interval" name="appiappi_templates_carousel_interval" min="1000" max="15000" step="500" value="<?php echo esc_attr( (int) get_option( 'appiappi_templates_carousel_interval', 3000 ) ); ?>">
						<p class="description"><?php esc_html_e( 'For designs with more than one gallery image, how often (in milliseconds) the card automatically advances to the next image. Default 3000 (3 seconds). Visitors can also click the arrows to move manually.', 'appiappi-template-showcase' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
