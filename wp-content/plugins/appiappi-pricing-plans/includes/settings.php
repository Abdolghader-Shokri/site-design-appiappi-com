<?php
/**
 * Plugin-wide display settings — currently just "how many plan cards
 * sit side by side on desktop before wrapping." Read by the theme's
 * appiappi_render_pricing_cards() via get_option() (loose coupling —
 * a plain stored number, no function dependency in either direction).
 * A separate settings screen from the per-plan meta box since this
 * applies to every pricing grid on the site, not one plan.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_pricing_settings_menu() {
	add_submenu_page(
		'edit.php?post_type=appiappi_plan',
		__( 'Pricing Display Settings', 'appiappi-pricing-plans' ),
		__( 'Display Settings', 'appiappi-pricing-plans' ),
		'manage_options',
		'appiappi-pricing-settings',
		'appiappi_pricing_render_settings_page'
	);
}
add_action( 'admin_menu', 'appiappi_pricing_settings_menu' );

function appiappi_pricing_settings_sanitize_columns( $value ) {
	$value = (int) $value;
	return max( 1, min( 6, $value ) );
}

function appiappi_pricing_settings_register() {
	register_setting( 'appiappi_pricing_settings_group', 'appiappi_pricing_columns', array(
		'type'              => 'integer',
		'sanitize_callback' => 'appiappi_pricing_settings_sanitize_columns',
		'default'           => 4,
	) );
}
add_action( 'admin_init', 'appiappi_pricing_settings_register' );

function appiappi_pricing_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$columns = (int) get_option( 'appiappi_pricing_columns', 4 );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Pricing Display Settings', 'appiappi-pricing-plans' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'appiappi_pricing_settings_group' ); ?>
			<table class="form-table">
				<tr>
					<th><label for="appiappi_pricing_columns"><?php esc_html_e( 'Plans Per Row (Desktop)', 'appiappi-pricing-plans' ); ?></label></th>
					<td>
						<select id="appiappi_pricing_columns" name="appiappi_pricing_columns">
							<?php for ( $i = 1; $i <= 6; $i++ ) : ?>
								<option value="<?php echo esc_attr( $i ); ?>" <?php selected( $columns, $i ); ?>><?php echo esc_html( $i ); ?></option>
							<?php endfor; ?>
						</select>
						<p class="description"><?php esc_html_e( 'How many plan cards sit side by side on desktop before wrapping to a new row. Applies everywhere pricing cards are shown (homepage and the Pricing page). Tablet is automatically capped at 2 regardless of this setting; mobile always shows one plan per row. If a row has fewer plans than this number, that row centres on the page instead of stretching edge to edge.', 'appiappi-pricing-plans' ); ?></p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
