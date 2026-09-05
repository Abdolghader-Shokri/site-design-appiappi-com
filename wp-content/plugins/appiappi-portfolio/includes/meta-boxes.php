<?php
/**
 * Project Details meta box: client name, location, external URL,
 * results summary, services provided, and a "Concept Project" flag —
 * per the project rule to never fabricate results, a project can be
 * explicitly marked as a concept/illustrative example rather than a
 * real client engagement (the theme renders a "Concept" badge when
 * checked). Native meta box, no ACF dependency.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_portfolio_add_meta_box() {
	add_meta_box(
		'appiappi_portfolio_details',
		__( 'Project Details', 'appiappi-portfolio' ),
		'appiappi_portfolio_render_meta_box',
		'appiappi_project',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'appiappi_portfolio_add_meta_box' );

function appiappi_portfolio_render_meta_box( $post ) {
	wp_nonce_field( 'appiappi_portfolio_save', 'appiappi_portfolio_nonce' );

	$client        = get_post_meta( $post->ID, '_appiappi_portfolio_client', true );
	$location      = get_post_meta( $post->ID, '_appiappi_portfolio_location', true );
	$external_url  = get_post_meta( $post->ID, '_appiappi_portfolio_external_url', true );
	$results       = get_post_meta( $post->ID, '_appiappi_portfolio_results', true );
	$services      = get_post_meta( $post->ID, '_appiappi_portfolio_services', true );
	$is_concept    = get_post_meta( $post->ID, '_appiappi_portfolio_is_concept', true );
	?>
	<table class="form-table">
		<tr>
			<th><label for="appiappi_portfolio_client"><?php esc_html_e( 'Client / Business Name', 'appiappi-portfolio' ); ?></label></th>
			<td><input type="text" id="appiappi_portfolio_client" name="appiappi_portfolio_client" value="<?php echo esc_attr( $client ); ?>" class="regular-text"></td>
		</tr>
		<tr>
			<th><label for="appiappi_portfolio_location"><?php esc_html_e( 'Location', 'appiappi-portfolio' ); ?></label></th>
			<td><input type="text" id="appiappi_portfolio_location" name="appiappi_portfolio_location" value="<?php echo esc_attr( $location ); ?>" placeholder="<?php esc_attr_e( 'e.g. Toronto, ON', 'appiappi-portfolio' ); ?>" class="regular-text"></td>
		</tr>
		<tr>
			<th><label for="appiappi_portfolio_external_url"><?php esc_html_e( 'Live Website URL', 'appiappi-portfolio' ); ?></label></th>
			<td><input type="url" id="appiappi_portfolio_external_url" name="appiappi_portfolio_external_url" value="<?php echo esc_attr( $external_url ); ?>" class="regular-text"></td>
		</tr>
		<tr>
			<th><label for="appiappi_portfolio_services"><?php esc_html_e( 'Services Provided', 'appiappi-portfolio' ); ?></label></th>
			<td><input type="text" id="appiappi_portfolio_services" name="appiappi_portfolio_services" value="<?php echo esc_attr( $services ); ?>" placeholder="<?php esc_attr_e( 'Website Design, SEO, Hosting', 'appiappi-portfolio' ); ?>" class="large-text"></td>
		</tr>
		<tr>
			<th><label for="appiappi_portfolio_results"><?php esc_html_e( 'Results Summary', 'appiappi-portfolio' ); ?></label></th>
			<td>
				<input type="text" id="appiappi_portfolio_results" name="appiappi_portfolio_results" value="<?php echo esc_attr( $results ); ?>" class="large-text">
				<p class="description"><?php esc_html_e( 'Only enter real, verifiable results here. Leave blank rather than estimate.', 'appiappi-portfolio' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="appiappi_portfolio_is_concept"><?php esc_html_e( 'Concept Project', 'appiappi-portfolio' ); ?></label></th>
			<td>
				<label><input type="checkbox" id="appiappi_portfolio_is_concept" name="appiappi_portfolio_is_concept" value="1" <?php checked( $is_concept, '1' ); ?>> <?php esc_html_e( 'This is an illustrative concept, not a real client engagement', 'appiappi-portfolio' ); ?></label>
				<p class="description"><?php esc_html_e( 'Check this until you have real completed client projects to feature — it shows a "Concept" badge instead of implying a real result.', 'appiappi-portfolio' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}

function appiappi_portfolio_save_meta_box( $post_id ) {
	if ( ! isset( $_POST['appiappi_portfolio_nonce'] ) || ! wp_verify_nonce( $_POST['appiappi_portfolio_nonce'], 'appiappi_portfolio_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'_appiappi_portfolio_client'       => 'sanitize_text_field',
		'_appiappi_portfolio_location'     => 'sanitize_text_field',
		'_appiappi_portfolio_external_url' => 'esc_url_raw',
		'_appiappi_portfolio_services'     => 'sanitize_text_field',
		'_appiappi_portfolio_results'      => 'sanitize_text_field',
	);
	foreach ( $fields as $meta_key => $sanitizer ) {
		$field_name = ltrim( str_replace( '_appiappi_portfolio_', 'appiappi_portfolio_', $meta_key ), '_' );
		if ( isset( $_POST[ $field_name ] ) ) {
			update_post_meta( $post_id, $meta_key, call_user_func( $sanitizer, wp_unslash( $_POST[ $field_name ] ) ) );
		}
	}

	update_post_meta( $post_id, '_appiappi_portfolio_is_concept', isset( $_POST['appiappi_portfolio_is_concept'] ) ? '1' : '0' );
}
add_action( 'save_post_appiappi_project', 'appiappi_portfolio_save_meta_box' );
