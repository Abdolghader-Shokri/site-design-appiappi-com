<?php
/**
 * Plan Details meta box: price, period, note, colour, icon, featured
 * flag, badge, CTA, and a one-feature-per-line textarea. Native meta
 * box (no ACF) per the project's "avoid unnecessary plugin dependency"
 * rule — see MASTER_PROMPT.md § Companion Plugin Architecture.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_pricing_color_options() {
	return array(
		'starter'      => __( 'Green (Starter)', 'appiappi-pricing-plans' ),
		'business'     => __( 'Blue (Business)', 'appiappi-pricing-plans' ),
		'professional' => __( 'Purple (Professional)', 'appiappi-pricing-plans' ),
		'growth'       => __( 'Orange (Growth)', 'appiappi-pricing-plans' ),
		'seo-growth'   => __( 'Teal (SEO Growth)', 'appiappi-pricing-plans' ),
	);
}

function appiappi_pricing_icon_options() {
	return array( 'rocket', 'pencil', 'diamond', 'crown', 'shield', 'star', 'trending-up', 'headset' );
}

function appiappi_pricing_group_options() {
	return array(
		'launch' => __( 'Launch Tiers (One-time Setup)', 'appiappi-pricing-plans' ),
		'growth' => __( 'Growth Tiers (Monthly Subscription)', 'appiappi-pricing-plans' ),
	);
}

function appiappi_pricing_add_meta_box() {
	add_meta_box(
		'appiappi_plan_details',
		__( 'Plan Details', 'appiappi-pricing-plans' ),
		'appiappi_pricing_render_meta_box',
		'appiappi_plan',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'appiappi_pricing_add_meta_box' );

function appiappi_pricing_render_meta_box( $post ) {
	wp_nonce_field( 'appiappi_pricing_save', 'appiappi_pricing_nonce' );
	?>
	<p class="description"><?php esc_html_e( 'Write a fuller description of this plan in the main content editor above — it only shows on the full Pricing page (not the homepage preview).', 'appiappi-pricing-plans' ); ?></p>
	<?php

	$price    = get_post_meta( $post->ID, '_appiappi_plan_price', true );
	$period   = get_post_meta( $post->ID, '_appiappi_plan_period', true );
	$note     = get_post_meta( $post->ID, '_appiappi_plan_note', true );
	$tagline  = get_post_meta( $post->ID, '_appiappi_plan_tagline', true );
	$audience = get_post_meta( $post->ID, '_appiappi_plan_audience', true );
	$value_driver = get_post_meta( $post->ID, '_appiappi_plan_value_driver', true );
	$group    = get_post_meta( $post->ID, '_appiappi_plan_group', true ) ?: 'launch';
	$homepage_visible_raw = get_post_meta( $post->ID, '_appiappi_plan_homepage_visible', true );
	$homepage_visible = ( '' === $homepage_visible_raw ) ? true : ( '1' === $homepage_visible_raw );
	$color    = get_post_meta( $post->ID, '_appiappi_plan_color', true ) ?: 'business';
	$icon     = get_post_meta( $post->ID, '_appiappi_plan_icon', true ) ?: 'rocket';
	$featured = (bool) get_post_meta( $post->ID, '_appiappi_plan_featured', true );
	$badge    = get_post_meta( $post->ID, '_appiappi_plan_badge', true );
	$cta_text = get_post_meta( $post->ID, '_appiappi_plan_cta_text', true ) ?: __( 'Choose Plan', 'appiappi-pricing-plans' );
	$cta_url  = get_post_meta( $post->ID, '_appiappi_plan_cta_url', true ) ?: '#contact';
	$features = get_post_meta( $post->ID, '_appiappi_plan_features', true );
	?>
	<table class="form-table">
		<tr>
			<th><label for="appiappi_plan_group"><?php esc_html_e( 'Group', 'appiappi-pricing-plans' ); ?></label></th>
			<td>
				<select id="appiappi_plan_group" name="appiappi_plan_group">
					<?php foreach ( appiappi_pricing_group_options() as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $group, $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Which section of the Pricing page this plan appears under.', 'appiappi-pricing-plans' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="appiappi_plan_tagline"><?php esc_html_e( 'Tagline', 'appiappi-pricing-plans' ); ?></label></th>
			<td><input type="text" id="appiappi_plan_tagline" name="appiappi_plan_tagline" value="<?php echo esc_attr( $tagline ); ?>" placeholder="<?php esc_attr_e( 'e.g. Get Online, Fast.', 'appiappi-pricing-plans' ); ?>" class="large-text"></td>
		</tr>
		<tr>
			<th><label for="appiappi_plan_audience"><?php esc_html_e( 'Target Audience', 'appiappi-pricing-plans' ); ?></label></th>
			<td><input type="text" id="appiappi_plan_audience" name="appiappi_plan_audience" value="<?php echo esc_attr( $audience ); ?>" placeholder="<?php esc_attr_e( 'Perfect for...', 'appiappi-pricing-plans' ); ?>" class="large-text"></td>
		</tr>
		<tr>
			<th><label for="appiappi_plan_value_driver"><?php esc_html_e( 'Value Driver (ROI sentence)', 'appiappi-pricing-plans' ); ?></label></th>
			<td><input type="text" id="appiappi_plan_value_driver" name="appiappi_plan_value_driver" value="<?php echo esc_attr( $value_driver ); ?>" placeholder="<?php esc_attr_e( 'e.g. Get online in days, not weeks.', 'appiappi-pricing-plans' ); ?>" class="large-text"></td>
		</tr>
		<tr>
			<th><label for="appiappi_plan_price"><?php esc_html_e( 'Price (USD)', 'appiappi-pricing-plans' ); ?></label></th>
			<td><input type="text" id="appiappi_plan_price" name="appiappi_plan_price" value="<?php echo esc_attr( $price ); ?>" placeholder="199" class="regular-text"></td>
		</tr>
		<tr>
			<th><label for="appiappi_plan_period"><?php esc_html_e( 'Billing Period', 'appiappi-pricing-plans' ); ?></label></th>
			<td><input type="text" id="appiappi_plan_period" name="appiappi_plan_period" value="<?php echo esc_attr( $period ); ?>" placeholder="<?php esc_attr_e( 'one-time or / month', 'appiappi-pricing-plans' ); ?>" class="regular-text"></td>
		</tr>
		<tr>
			<th><label for="appiappi_plan_note"><?php esc_html_e( 'Short Note', 'appiappi-pricing-plans' ); ?></label></th>
			<td><input type="text" id="appiappi_plan_note" name="appiappi_plan_note" value="<?php echo esc_attr( $note ); ?>" class="large-text"></td>
		</tr>
		<tr>
			<th><label for="appiappi_plan_homepage_visible"><?php esc_html_e( 'Show on Homepage', 'appiappi-pricing-plans' ); ?></label></th>
			<td>
				<label><input type="checkbox" id="appiappi_plan_homepage_visible" name="appiappi_plan_homepage_visible" value="1" <?php checked( $homepage_visible ); ?>> <?php esc_html_e( 'Include this plan in the homepage pricing preview', 'appiappi-pricing-plans' ); ?></label>
				<p class="description"><?php esc_html_e( 'Uncheck to keep a plan available on the full Pricing page only (e.g. a secondary upsell tier not yet part of the main pitch).', 'appiappi-pricing-plans' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="appiappi_plan_color"><?php esc_html_e( 'Card Colour', 'appiappi-pricing-plans' ); ?></label></th>
			<td>
				<select id="appiappi_plan_color" name="appiappi_plan_color">
					<?php foreach ( appiappi_pricing_color_options() as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $color, $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="appiappi_plan_icon"><?php esc_html_e( 'Icon', 'appiappi-pricing-plans' ); ?></label></th>
			<td>
				<select id="appiappi_plan_icon" name="appiappi_plan_icon">
					<?php foreach ( appiappi_pricing_icon_options() as $value ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $icon, $value ); ?>><?php echo esc_html( ucfirst( str_replace( '-', ' ', $value ) ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<th><label for="appiappi_plan_featured"><?php esc_html_e( 'Featured', 'appiappi-pricing-plans' ); ?></label></th>
			<td>
				<label><input type="checkbox" id="appiappi_plan_featured" name="appiappi_plan_featured" value="1" <?php checked( $featured ); ?>> <?php esc_html_e( 'Highlight this plan (e.g. "Most Popular")', 'appiappi-pricing-plans' ); ?></label>
			</td>
		</tr>
		<tr>
			<th><label for="appiappi_plan_badge"><?php esc_html_e( 'Badge Text', 'appiappi-pricing-plans' ); ?></label></th>
			<td><input type="text" id="appiappi_plan_badge" name="appiappi_plan_badge" value="<?php echo esc_attr( $badge ); ?>" placeholder="<?php esc_attr_e( 'Most Popular', 'appiappi-pricing-plans' ); ?>" class="regular-text"></td>
		</tr>
		<tr>
			<th><label for="appiappi_plan_cta_text"><?php esc_html_e( 'Button Text', 'appiappi-pricing-plans' ); ?></label></th>
			<td><input type="text" id="appiappi_plan_cta_text" name="appiappi_plan_cta_text" value="<?php echo esc_attr( $cta_text ); ?>" class="regular-text"></td>
		</tr>
		<tr>
			<th><label for="appiappi_plan_cta_url"><?php esc_html_e( 'Button URL', 'appiappi-pricing-plans' ); ?></label></th>
			<td><input type="url" id="appiappi_plan_cta_url" name="appiappi_plan_cta_url" value="<?php echo esc_attr( $cta_url ); ?>" class="regular-text"></td>
		</tr>
		<tr>
			<th><label for="appiappi_plan_features"><?php esc_html_e( 'Features', 'appiappi-pricing-plans' ); ?></label></th>
			<td>
				<textarea id="appiappi_plan_features" name="appiappi_plan_features" rows="6" class="large-text" placeholder="<?php esc_attr_e( 'One feature per line', 'appiappi-pricing-plans' ); ?>"><?php echo esc_textarea( $features ); ?></textarea>
				<p class="description"><?php esc_html_e( 'One feature per line.', 'appiappi-pricing-plans' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}

function appiappi_pricing_save_meta_box( $post_id ) {
	if ( ! isset( $_POST['appiappi_pricing_nonce'] ) || ! wp_verify_nonce( $_POST['appiappi_pricing_nonce'], 'appiappi_pricing_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$fields = array(
		'_appiappi_plan_price'        => 'sanitize_text_field',
		'_appiappi_plan_period'       => 'sanitize_text_field',
		'_appiappi_plan_note'         => 'sanitize_text_field',
		'_appiappi_plan_badge'        => 'sanitize_text_field',
		'_appiappi_plan_cta_text'     => 'sanitize_text_field',
		'_appiappi_plan_cta_url'      => 'esc_url_raw',
		'_appiappi_plan_tagline'      => 'sanitize_text_field',
		'_appiappi_plan_audience'     => 'sanitize_text_field',
		'_appiappi_plan_value_driver' => 'sanitize_text_field',
	);
	foreach ( $fields as $meta_key => $sanitizer ) {
		$field_name = ltrim( str_replace( '_appiappi_plan_', 'appiappi_plan_', $meta_key ), '_' );
		if ( isset( $_POST[ $field_name ] ) ) {
			update_post_meta( $post_id, $meta_key, call_user_func( $sanitizer, wp_unslash( $_POST[ $field_name ] ) ) );
		}
	}

	if ( isset( $_POST['appiappi_plan_color'] ) && array_key_exists( $_POST['appiappi_plan_color'], appiappi_pricing_color_options() ) ) {
		update_post_meta( $post_id, '_appiappi_plan_color', sanitize_key( $_POST['appiappi_plan_color'] ) );
	}

	if ( isset( $_POST['appiappi_plan_icon'] ) && in_array( $_POST['appiappi_plan_icon'], appiappi_pricing_icon_options(), true ) ) {
		update_post_meta( $post_id, '_appiappi_plan_icon', sanitize_key( $_POST['appiappi_plan_icon'] ) );
	}

	if ( isset( $_POST['appiappi_plan_group'] ) && array_key_exists( $_POST['appiappi_plan_group'], appiappi_pricing_group_options() ) ) {
		update_post_meta( $post_id, '_appiappi_plan_group', sanitize_key( $_POST['appiappi_plan_group'] ) );
	}

	update_post_meta( $post_id, '_appiappi_plan_featured', isset( $_POST['appiappi_plan_featured'] ) ? 1 : 0 );
	update_post_meta( $post_id, '_appiappi_plan_homepage_visible', isset( $_POST['appiappi_plan_homepage_visible'] ) ? '1' : '0' );

	if ( isset( $_POST['appiappi_plan_features'] ) ) {
		update_post_meta( $post_id, '_appiappi_plan_features', sanitize_textarea_field( wp_unslash( $_POST['appiappi_plan_features'] ) ) );
	}
}
add_action( 'save_post_appiappi_plan', 'appiappi_pricing_save_meta_box' );
