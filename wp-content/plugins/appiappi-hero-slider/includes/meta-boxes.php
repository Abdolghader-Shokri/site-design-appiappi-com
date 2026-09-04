<?php
/**
 * Slide Details meta box: subheadline, CTA button text/URL, and an
 * optional image alt text. Native meta box, no ACF dependency — same
 * convention as the other two companion plugins.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_hero_add_meta_box() {
	add_meta_box(
		'appiappi_slide_details',
		__( 'Slide Details', 'appiappi-hero-slider' ),
		'appiappi_hero_render_meta_box',
		'appiappi_slide',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'appiappi_hero_add_meta_box' );

function appiappi_hero_render_meta_box( $post ) {
	wp_nonce_field( 'appiappi_hero_save', 'appiappi_hero_nonce' );

	$subheadline = get_post_meta( $post->ID, '_appiappi_slide_subheadline', true );
	$cta_text    = get_post_meta( $post->ID, '_appiappi_slide_cta_text', true );
	$cta_url     = get_post_meta( $post->ID, '_appiappi_slide_cta_url', true );
	$image_alt   = get_post_meta( $post->ID, '_appiappi_slide_image_alt', true );
	?>
	<p class="description"><?php esc_html_e( 'The Title above is this slide\'s headline (the big H1 text).', 'appiappi-hero-slider' ); ?></p>
	<table class="form-table">
		<tr>
			<th><label for="appiappi_slide_subheadline"><?php esc_html_e( 'Subheadline', 'appiappi-hero-slider' ); ?></label></th>
			<td><textarea id="appiappi_slide_subheadline" name="appiappi_slide_subheadline" rows="3" class="large-text"><?php echo esc_textarea( $subheadline ); ?></textarea></td>
		</tr>
		<tr>
			<th><label for="appiappi_slide_cta_text"><?php esc_html_e( 'Button Text', 'appiappi-hero-slider' ); ?></label></th>
			<td><input type="text" id="appiappi_slide_cta_text" name="appiappi_slide_cta_text" value="<?php echo esc_attr( $cta_text ); ?>" placeholder="<?php esc_attr_e( 'Explore Website Designs', 'appiappi-hero-slider' ); ?>" class="regular-text"></td>
		</tr>
		<tr>
			<th><label for="appiappi_slide_cta_url"><?php esc_html_e( 'Button URL', 'appiappi-hero-slider' ); ?></label></th>
			<td><input type="url" id="appiappi_slide_cta_url" name="appiappi_slide_cta_url" value="<?php echo esc_attr( $cta_url ); ?>" class="regular-text"></td>
		</tr>
		<tr>
			<th><label for="appiappi_slide_image_alt"><?php esc_html_e( 'Image Alt Text', 'appiappi-hero-slider' ); ?></label></th>
			<td>
				<input type="text" id="appiappi_slide_image_alt" name="appiappi_slide_image_alt" value="<?php echo esc_attr( $image_alt ); ?>" class="regular-text">
				<p class="description"><?php esc_html_e( 'Leave blank if the featured image is purely decorative (the headline already conveys the meaning).', 'appiappi-hero-slider' ); ?></p>
			</td>
		</tr>
	</table>
	<p class="description"><?php esc_html_e( 'Set the Featured Image above as this slide\'s visual, and use Page Attributes → Order to control the slide sequence.', 'appiappi-hero-slider' ); ?></p>
	<?php
}

function appiappi_hero_save_meta_box( $post_id ) {
	if ( ! isset( $_POST['appiappi_hero_nonce'] ) || ! wp_verify_nonce( $_POST['appiappi_hero_nonce'], 'appiappi_hero_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'_appiappi_slide_subheadline' => 'sanitize_textarea_field',
		'_appiappi_slide_cta_text'    => 'sanitize_text_field',
		'_appiappi_slide_cta_url'     => 'esc_url_raw',
		'_appiappi_slide_image_alt'   => 'sanitize_text_field',
	);
	foreach ( $fields as $meta_key => $sanitizer ) {
		$field_name = ltrim( str_replace( '_appiappi_slide_', 'appiappi_slide_', $meta_key ), '_' );
		if ( isset( $_POST[ $field_name ] ) ) {
			update_post_meta( $post_id, $meta_key, call_user_func( $sanitizer, wp_unslash( $_POST[ $field_name ] ) ) );
		}
	}
}
add_action( 'save_post_appiappi_slide', 'appiappi_hero_save_meta_box' );
