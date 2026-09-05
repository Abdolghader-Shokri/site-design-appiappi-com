<?php
/**
 * Service Details meta box: icon, breakdown items (one per line, any
 * count) and a closing line. The "Hook" (short benefit statement) is
 * the native content editor above this box — see the meta box's own
 * description. Native meta box (no ACF) per the project's "avoid
 * unnecessary plugin dependency" rule.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_services_icon_options() {
	return array( 'monitor', 'refresh', 'trending-up', 'pencil', 'shield', 'headset', 'rocket', 'briefcase', 'hammer', 'leaf', 'scale', 'heart', 'home', 'star', 'diamond' );
}

function appiappi_services_add_meta_box() {
	add_meta_box(
		'appiappi_service_details',
		__( 'Service Details', 'appiappi-services' ),
		'appiappi_services_render_meta_box',
		'appiappi_service',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'appiappi_services_add_meta_box' );

function appiappi_services_render_meta_box( $post ) {
	wp_nonce_field( 'appiappi_services_save', 'appiappi_services_nonce' );

	$icon      = get_post_meta( $post->ID, '_appiappi_service_icon', true ) ?: 'monitor';
	$hook      = get_post_meta( $post->ID, '_appiappi_service_hook', true );
	$breakdown = get_post_meta( $post->ID, '_appiappi_service_breakdown', true );
	$closing   = get_post_meta( $post->ID, '_appiappi_service_closing', true );
	?>
	<table class="form-table">
		<tr>
			<th><label for="appiappi_service_icon"><?php esc_html_e( 'Icon', 'appiappi-services' ); ?></label></th>
			<td>
				<select id="appiappi_service_icon" name="appiappi_service_icon">
					<?php foreach ( appiappi_services_icon_options() as $value ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $icon, $value ); ?>><?php echo esc_html( ucfirst( str_replace( '-', ' ', $value ) ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="appiappi_service_hook"><?php esc_html_e( 'Hook', 'appiappi-services' ); ?></label></th>
			<td>
				<textarea id="appiappi_service_hook" name="appiappi_service_hook" rows="3" class="large-text"><?php echo esc_textarea( $hook ); ?></textarea>
				<p class="description"><?php esc_html_e( 'A short, punchy benefit statement — shows directly under the service name.', 'appiappi-services' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="appiappi_service_breakdown"><?php esc_html_e( 'Breakdown Items', 'appiappi-services' ); ?></label></th>
			<td>
				<textarea id="appiappi_service_breakdown" name="appiappi_service_breakdown" rows="8" class="large-text" placeholder="<?php esc_attr_e( 'One item per line', 'appiappi-services' ); ?>"><?php echo esc_textarea( $breakdown ); ?></textarea>
				<p class="description"><?php esc_html_e( 'One concrete sub-task per line — as many or as few as this service needs. Each renders with a checkmark under the Hook.', 'appiappi-services' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="appiappi_service_closing"><?php esc_html_e( 'Closing Line', 'appiappi-services' ); ?></label></th>
			<td>
				<textarea id="appiappi_service_closing" name="appiappi_service_closing" rows="3" class="large-text"><?php echo esc_textarea( $closing ); ?></textarea>
				<p class="description"><?php esc_html_e( 'A short line bridging this service to the ongoing partnership — shown as a highlighted callout at the bottom of the block.', 'appiappi-services' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}

function appiappi_services_save_meta_box( $post_id ) {
	if ( ! isset( $_POST['appiappi_services_nonce'] ) || ! wp_verify_nonce( $_POST['appiappi_services_nonce'], 'appiappi_services_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['appiappi_service_icon'] ) && in_array( $_POST['appiappi_service_icon'], appiappi_services_icon_options(), true ) ) {
		update_post_meta( $post_id, '_appiappi_service_icon', sanitize_key( $_POST['appiappi_service_icon'] ) );
	}

	if ( isset( $_POST['appiappi_service_hook'] ) ) {
		update_post_meta( $post_id, '_appiappi_service_hook', sanitize_textarea_field( wp_unslash( $_POST['appiappi_service_hook'] ) ) );
	}

	if ( isset( $_POST['appiappi_service_breakdown'] ) ) {
		update_post_meta( $post_id, '_appiappi_service_breakdown', sanitize_textarea_field( wp_unslash( $_POST['appiappi_service_breakdown'] ) ) );
	}

	if ( isset( $_POST['appiappi_service_closing'] ) ) {
		update_post_meta( $post_id, '_appiappi_service_closing', sanitize_textarea_field( wp_unslash( $_POST['appiappi_service_closing'] ) ) );
	}
}
add_action( 'save_post_appiappi_service', 'appiappi_services_save_meta_box' );
