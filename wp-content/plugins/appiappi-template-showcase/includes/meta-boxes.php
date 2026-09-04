<?php
/**
 * Design Details meta box: short description, price, rating, demo/detail
 * links, and original vendor/source (per the project's rule that
 * third-party designs must never be presented as if Appiappi made them —
 * see MASTER_PROMPT.md § Website Template Library). Native meta box, no
 * ACF dependency.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_showcase_add_meta_box() {
	add_meta_box(
		'appiappi_template_details',
		__( 'Design Details', 'appiappi-template-showcase' ),
		'appiappi_showcase_render_meta_box',
		'appiappi_template',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'appiappi_showcase_add_meta_box' );

function appiappi_showcase_render_meta_box( $post ) {
	wp_nonce_field( 'appiappi_showcase_save', 'appiappi_showcase_nonce' );

	$desc         = get_post_meta( $post->ID, '_appiappi_template_desc', true );
	$price        = get_post_meta( $post->ID, '_appiappi_template_price', true );
	$rating       = get_post_meta( $post->ID, '_appiappi_template_rating', true );
	$rating_count = get_post_meta( $post->ID, '_appiappi_template_rating_count', true );
	$demo_url     = get_post_meta( $post->ID, '_appiappi_template_demo_url', true );
	$details_url  = get_post_meta( $post->ID, '_appiappi_template_details_url', true );
	$vendor       = get_post_meta( $post->ID, '_appiappi_template_vendor', true );
	$source_url   = get_post_meta( $post->ID, '_appiappi_template_source_url', true );
	?>
	<table class="form-table">
		<tr>
			<th><label for="appiappi_template_desc"><?php esc_html_e( 'Short Description', 'appiappi-template-showcase' ); ?></label></th>
			<td><input type="text" id="appiappi_template_desc" name="appiappi_template_desc" value="<?php echo esc_attr( $desc ); ?>" class="large-text"></td>
		</tr>
		<tr>
			<th><label for="appiappi_template_price"><?php esc_html_e( 'Starting Price', 'appiappi-template-showcase' ); ?></label></th>
			<td><input type="text" id="appiappi_template_price" name="appiappi_template_price" value="<?php echo esc_attr( $price ); ?>" placeholder="$59" class="regular-text"></td>
		</tr>
		<tr>
			<th><label for="appiappi_template_rating"><?php esc_html_e( 'Rating (0–5)', 'appiappi-template-showcase' ); ?></label></th>
			<td><input type="text" id="appiappi_template_rating" name="appiappi_template_rating" value="<?php echo esc_attr( $rating ); ?>" placeholder="4.9" class="small-text"></td>
		</tr>
		<tr>
			<th><label for="appiappi_template_rating_count"><?php esc_html_e( 'Rating Count', 'appiappi-template-showcase' ); ?></label></th>
			<td><input type="number" min="0" id="appiappi_template_rating_count" name="appiappi_template_rating_count" value="<?php echo esc_attr( $rating_count ); ?>" class="small-text"></td>
		</tr>
		<tr>
			<th><label for="appiappi_template_demo_url"><?php esc_html_e( 'Live Demo URL', 'appiappi-template-showcase' ); ?></label></th>
			<td><input type="url" id="appiappi_template_demo_url" name="appiappi_template_demo_url" value="<?php echo esc_attr( $demo_url ); ?>" class="regular-text"></td>
		</tr>
		<tr>
			<th><label for="appiappi_template_details_url"><?php esc_html_e( 'Details Page URL', 'appiappi-template-showcase' ); ?></label></th>
			<td>
				<input type="url" id="appiappi_template_details_url" name="appiappi_template_details_url" value="<?php echo esc_attr( $details_url ); ?>" class="regular-text">
				<p class="description"><?php esc_html_e( 'Leave blank until a dedicated design-detail page exists (Phase 3).', 'appiappi-template-showcase' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="appiappi_template_vendor"><?php esc_html_e( 'Original Vendor / Source', 'appiappi-template-showcase' ); ?></label></th>
			<td>
				<input type="text" id="appiappi_template_vendor" name="appiappi_template_vendor" value="<?php echo esc_attr( $vendor ); ?>" placeholder="<?php esc_attr_e( 'e.g. ThemeForest / Envato', 'appiappi-template-showcase' ); ?>" class="regular-text">
				<p class="description"><?php esc_html_e( 'Third-party designs must credit the original vendor, not be presented as Appiappi originals.', 'appiappi-template-showcase' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="appiappi_template_source_url"><?php esc_html_e( 'Original Marketplace URL', 'appiappi-template-showcase' ); ?></label></th>
			<td><input type="url" id="appiappi_template_source_url" name="appiappi_template_source_url" value="<?php echo esc_attr( $source_url ); ?>" class="regular-text"></td>
		</tr>
	</table>
	<p class="description"><?php esc_html_e( 'Set the Featured Image above as the design preview, and use Categories/Styles in the sidebar to classify it.', 'appiappi-template-showcase' ); ?></p>
	<?php
}

function appiappi_showcase_save_meta_box( $post_id ) {
	if ( ! isset( $_POST['appiappi_showcase_nonce'] ) || ! wp_verify_nonce( $_POST['appiappi_showcase_nonce'], 'appiappi_showcase_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'_appiappi_template_desc'        => 'sanitize_text_field',
		'_appiappi_template_price'       => 'sanitize_text_field',
		'_appiappi_template_rating'      => 'sanitize_text_field',
		'_appiappi_template_rating_count'=> 'absint',
		'_appiappi_template_demo_url'    => 'esc_url_raw',
		'_appiappi_template_details_url' => 'esc_url_raw',
		'_appiappi_template_vendor'      => 'sanitize_text_field',
		'_appiappi_template_source_url'  => 'esc_url_raw',
	);
	foreach ( $fields as $meta_key => $sanitizer ) {
		$field_name = ltrim( str_replace( '_appiappi_template_', 'appiappi_template_', $meta_key ), '_' );
		if ( isset( $_POST[ $field_name ] ) ) {
			update_post_meta( $post_id, $meta_key, call_user_func( $sanitizer, wp_unslash( $_POST[ $field_name ] ) ) );
		}
	}
}
add_action( 'save_post_appiappi_template', 'appiappi_showcase_save_meta_box' );
