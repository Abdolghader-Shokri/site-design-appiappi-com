<?php
/**
 * Adds an "Icon" field to appiappi_template_category terms, so the
 * homepage sidebar (built by the theme's appiappi_render_template_showcase())
 * can show a matching icon per category without any code changes when
 * the admin adds a new category.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_showcase_category_icon_options() {
	return array( 'grid', 'hammer', 'scale', 'heart', 'home', 'shopping-bag', 'briefcase', 'monitor' );
}

function appiappi_showcase_category_add_fields() {
	?>
	<div class="form-field">
		<label for="appiappi_category_icon"><?php esc_html_e( 'Icon', 'appiappi-template-showcase' ); ?></label>
		<select name="appiappi_category_icon" id="appiappi_category_icon">
			<?php foreach ( appiappi_showcase_category_icon_options() as $icon ) : ?>
				<option value="<?php echo esc_attr( $icon ); ?>"><?php echo esc_html( ucfirst( str_replace( '-', ' ', $icon ) ) ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>
	<?php
}
add_action( 'appiappi_template_category_add_form_fields', 'appiappi_showcase_category_add_fields' );

function appiappi_showcase_category_edit_fields( $term ) {
	$icon = get_term_meta( $term->term_id, 'icon', true ) ?: 'grid';
	?>
	<tr class="form-field">
		<th scope="row"><label for="appiappi_category_icon"><?php esc_html_e( 'Icon', 'appiappi-template-showcase' ); ?></label></th>
		<td>
			<select name="appiappi_category_icon" id="appiappi_category_icon">
				<?php foreach ( appiappi_showcase_category_icon_options() as $option ) : ?>
					<option value="<?php echo esc_attr( $option ); ?>" <?php selected( $icon, $option ); ?>><?php echo esc_html( ucfirst( str_replace( '-', ' ', $option ) ) ); ?></option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<?php
}
add_action( 'appiappi_template_category_edit_form_fields', 'appiappi_showcase_category_edit_fields' );

function appiappi_showcase_category_save( $term_id ) {
	if ( isset( $_POST['appiappi_category_icon'] ) && in_array( $_POST['appiappi_category_icon'], appiappi_showcase_category_icon_options(), true ) ) {
		update_term_meta( $term_id, 'icon', sanitize_key( $_POST['appiappi_category_icon'] ) );
	}
}
add_action( 'created_appiappi_template_category', 'appiappi_showcase_category_save' );
add_action( 'edited_appiappi_template_category', 'appiappi_showcase_category_save' );
