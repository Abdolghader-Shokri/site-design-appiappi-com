<?php
/**
 * Registers the `appiappi_lead` post type — every contact-form
 * submission becomes one Lead, visible in wp-admin (per the project
 * rule "don't rely solely on email delivery"). Not public, no
 * front-end template.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_contact_register_cpt() {
	register_post_type( 'appiappi_lead', array(
		'labels' => array(
			'name'          => __( 'Leads', 'appiappi-contact' ),
			'singular_name' => __( 'Lead', 'appiappi-contact' ),
			'all_items'     => __( 'Leads', 'appiappi-contact' ),
			'menu_name'     => __( 'Leads', 'appiappi-contact' ),
			'not_found'     => __( 'No leads yet.', 'appiappi-contact' ),
			'edit_item'     => __( 'View Lead', 'appiappi-contact' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'show_in_rest'    => false,
		'menu_icon'       => 'dashicons-email-alt',
		'supports'        => array( 'title' ),
		'capability_type' => 'post',
		'map_meta_cap'    => true,
	) );
}
add_action( 'init', 'appiappi_contact_register_cpt' );

function appiappi_contact_lead_statuses() {
	return array(
		'new'       => __( 'New', 'appiappi-contact' ),
		'contacted' => __( 'Contacted', 'appiappi-contact' ),
		'qualified' => __( 'Qualified', 'appiappi-contact' ),
		'proposal'  => __( 'Proposal', 'appiappi-contact' ),
		'won'       => __( 'Won', 'appiappi-contact' ),
		'lost'      => __( 'Lost', 'appiappi-contact' ),
	);
}

function appiappi_contact_admin_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['appiappi_lead_email']   = __( 'Email', 'appiappi-contact' );
			$new['appiappi_lead_service'] = __( 'Interested Service', 'appiappi-contact' );
			$new['appiappi_lead_status']  = __( 'Status', 'appiappi-contact' );
		}
	}
	return $new;
}
add_filter( 'manage_appiappi_lead_posts_columns', 'appiappi_contact_admin_columns' );

function appiappi_contact_admin_column_content( $column, $post_id ) {
	if ( 'appiappi_lead_email' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_appiappi_lead_email', true ) );
	}
	if ( 'appiappi_lead_service' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_appiappi_lead_interested_service', true ) ?: '—' );
	}
	if ( 'appiappi_lead_status' === $column ) {
		$statuses = appiappi_contact_lead_statuses();
		$status   = get_post_meta( $post_id, '_appiappi_lead_status', true ) ?: 'new';
		echo esc_html( $statuses[ $status ] ?? $status );
	}
}
add_action( 'manage_appiappi_lead_posts_custom_column', 'appiappi_contact_admin_column_content', 10, 2 );

function appiappi_contact_add_meta_box() {
	add_meta_box( 'appiappi_lead_details', __( 'Lead Details', 'appiappi-contact' ), 'appiappi_contact_render_meta_box', 'appiappi_lead', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'appiappi_contact_add_meta_box' );

function appiappi_contact_render_meta_box( $post ) {
	wp_nonce_field( 'appiappi_lead_save', 'appiappi_lead_nonce' );

	$fields = array(
		'business'             => __( 'Business Name', 'appiappi-contact' ),
		'email'                => __( 'Email', 'appiappi-contact' ),
		'phone'                => __( 'Phone', 'appiappi-contact' ),
		'province'             => __( 'Province', 'appiappi-contact' ),
		'website'              => __( 'Current Website', 'appiappi-contact' ),
		'business_type'        => __( 'Business Type', 'appiappi-contact' ),
		'interested_service'   => __( 'Interested Service', 'appiappi-contact' ),
		'budget_range'         => __( 'Budget Range', 'appiappi-contact' ),
		'launch_date'          => __( 'Preferred Launch Date', 'appiappi-contact' ),
		'selected_design'      => __( 'Selected Design', 'appiappi-contact' ),
		'selected_plan'        => __( 'Recommended Plan', 'appiappi-contact' ),
		'source'               => __( 'Source', 'appiappi-contact' ),
	);
	?>
	<table class="form-table">
		<?php foreach ( $fields as $key => $label ) : ?>
			<tr>
				<th><?php echo esc_html( $label ); ?></th>
				<td><?php echo esc_html( get_post_meta( $post->ID, '_appiappi_lead_' . $key, true ) ?: '—' ); ?></td>
			</tr>
		<?php endforeach; ?>
		<tr>
			<th><?php esc_html_e( 'Message', 'appiappi-contact' ); ?></th>
			<td><?php echo nl2br( esc_html( get_post_meta( $post->ID, '_appiappi_lead_message', true ) ) ); ?></td>
		</tr>
		<tr>
			<th><label for="appiappi_lead_status"><?php esc_html_e( 'Status', 'appiappi-contact' ); ?></label></th>
			<td>
				<select id="appiappi_lead_status" name="appiappi_lead_status">
					<?php $current = get_post_meta( $post->ID, '_appiappi_lead_status', true ) ?: 'new'; ?>
					<?php foreach ( appiappi_contact_lead_statuses() as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
	</table>
	<?php
}

function appiappi_contact_save_meta_box( $post_id ) {
	if ( ! isset( $_POST['appiappi_lead_nonce'] ) || ! wp_verify_nonce( $_POST['appiappi_lead_nonce'], 'appiappi_lead_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['appiappi_lead_status'] ) && array_key_exists( $_POST['appiappi_lead_status'], appiappi_contact_lead_statuses() ) ) {
		update_post_meta( $post_id, '_appiappi_lead_status', sanitize_key( $_POST['appiappi_lead_status'] ) );
	}
}
add_action( 'save_post_appiappi_lead', 'appiappi_contact_save_meta_box' );
