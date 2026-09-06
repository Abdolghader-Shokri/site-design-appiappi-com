<?php
/**
 * Registers `appiappi_hosting` — admin-managed hosting packages (create
 * as many as needed, edit or delete any time) that a customer picks from
 * at checkout when the plan they chose doesn't include free hosting, or
 * when they're deferring the plan fee until work is done (see
 * includes/pricing.php for exactly when hosting becomes required). Each
 * package is one location + storage + traffic + annual price
 * combination — the checkout modal's cascading Location → Storage →
 * Traffic selectors (checkout.js) narrow down to one matching package.
 *
 * Uses core's native post-list screen for create/edit/delete — no
 * custom admin UI needed beyond the meta box, same as every other CPT
 * in this project.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_checkout_register_hosting_cpt() {
	register_post_type( 'appiappi_hosting', array(
		'labels' => array(
			'name'          => __( 'Hosting Packages', 'appiappi-checkout' ),
			'singular_name' => __( 'Hosting Package', 'appiappi-checkout' ),
			'add_new_item'  => __( 'Add Hosting Package', 'appiappi-checkout' ),
			'edit_item'     => __( 'Edit Hosting Package', 'appiappi-checkout' ),
			'all_items'     => __( 'Hosting Packages', 'appiappi-checkout' ),
			'menu_name'     => __( 'Hosting Packages', 'appiappi-checkout' ),
			'not_found'     => __( 'No hosting packages yet.', 'appiappi-checkout' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => 'edit.php?post_type=appiappi_order',
		'show_in_rest'    => false,
		'supports'        => array( 'title' ),
		'capability_type' => 'post',
		'map_meta_cap'    => true,
	) );
}
add_action( 'init', 'appiappi_checkout_register_hosting_cpt' );

function appiappi_checkout_hosting_admin_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['appiappi_hosting_location'] = __( 'Location', 'appiappi-checkout' );
			$new['appiappi_hosting_storage']  = __( 'Storage', 'appiappi-checkout' );
			$new['appiappi_hosting_traffic']  = __( 'Traffic', 'appiappi-checkout' );
			$new['appiappi_hosting_price']    = __( 'Annual Price', 'appiappi-checkout' );
		}
	}
	return $new;
}
add_filter( 'manage_appiappi_hosting_posts_columns', 'appiappi_checkout_hosting_admin_columns' );

function appiappi_checkout_hosting_admin_column_content( $column, $post_id ) {
	if ( 'appiappi_hosting_location' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_appiappi_hosting_location', true ) ?: '—' );
	}
	if ( 'appiappi_hosting_storage' === $column ) {
		$unlimited = get_post_meta( $post_id, '_appiappi_hosting_storage_unlimited', true );
		echo $unlimited ? esc_html__( 'Unlimited', 'appiappi-checkout' ) : esc_html( get_post_meta( $post_id, '_appiappi_hosting_storage_amount', true ) ?: '—' );
	}
	if ( 'appiappi_hosting_traffic' === $column ) {
		$unlimited = get_post_meta( $post_id, '_appiappi_hosting_traffic_unlimited', true );
		echo $unlimited ? esc_html__( 'Unlimited', 'appiappi-checkout' ) : esc_html( get_post_meta( $post_id, '_appiappi_hosting_traffic_amount', true ) ?: '—' );
	}
	if ( 'appiappi_hosting_price' === $column ) {
		$price = get_post_meta( $post_id, '_appiappi_hosting_annual_price', true );
		echo $price ? '$' . esc_html( number_format( (float) $price, 2 ) ) . ' / ' . esc_html__( 'yr', 'appiappi-checkout' ) : '—';
	}
}
add_action( 'manage_appiappi_hosting_posts_custom_column', 'appiappi_checkout_hosting_admin_column_content', 10, 2 );

function appiappi_checkout_hosting_add_meta_box() {
	add_meta_box( 'appiappi_hosting_details', __( 'Hosting Package Details', 'appiappi-checkout' ), 'appiappi_checkout_hosting_render_meta_box', 'appiappi_hosting', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'appiappi_checkout_hosting_add_meta_box' );

function appiappi_checkout_hosting_render_meta_box( $post ) {
	wp_nonce_field( 'appiappi_hosting_save', 'appiappi_hosting_nonce' );

	$location           = get_post_meta( $post->ID, '_appiappi_hosting_location', true );
	$storage_unlimited  = (bool) get_post_meta( $post->ID, '_appiappi_hosting_storage_unlimited', true );
	$storage_amount     = get_post_meta( $post->ID, '_appiappi_hosting_storage_amount', true );
	$traffic_unlimited  = (bool) get_post_meta( $post->ID, '_appiappi_hosting_traffic_unlimited', true );
	$traffic_amount     = get_post_meta( $post->ID, '_appiappi_hosting_traffic_amount', true );
	$annual_price       = get_post_meta( $post->ID, '_appiappi_hosting_annual_price', true );
	?>
	<p class="description"><?php esc_html_e( 'The post title is just an internal label for you (e.g. "Canada — 10GB" or "Europe — Unlimited"); customers see the Location/Storage/Traffic values below instead.', 'appiappi-checkout' ); ?></p>
	<table class="form-table">
		<tr>
			<th><label for="appiappi_hosting_location"><?php esc_html_e( 'Location', 'appiappi-checkout' ); ?></label></th>
			<td>
				<input type="text" id="appiappi_hosting_location" name="appiappi_hosting_location" value="<?php echo esc_attr( $location ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. Canada, United States, Europe', 'appiappi-checkout' ); ?>" required>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Storage', 'appiappi-checkout' ); ?></th>
			<td>
				<label><input type="checkbox" id="appiappi_hosting_storage_unlimited" name="appiappi_hosting_storage_unlimited" value="1" <?php checked( $storage_unlimited ); ?>> <?php esc_html_e( 'Unlimited', 'appiappi-checkout' ); ?></label>
				<br><br>
				<input type="text" id="appiappi_hosting_storage_amount" name="appiappi_hosting_storage_amount" value="<?php echo esc_attr( $storage_amount ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. 10 GB', 'appiappi-checkout' ); ?>" <?php disabled( $storage_unlimited ); ?>>
				<p class="description"><?php esc_html_e( 'Ignored if Unlimited is checked.', 'appiappi-checkout' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><?php esc_html_e( 'Traffic / Bandwidth', 'appiappi-checkout' ); ?></th>
			<td>
				<label><input type="checkbox" id="appiappi_hosting_traffic_unlimited" name="appiappi_hosting_traffic_unlimited" value="1" <?php checked( $traffic_unlimited ); ?>> <?php esc_html_e( 'Unlimited', 'appiappi-checkout' ); ?></label>
				<br><br>
				<input type="text" id="appiappi_hosting_traffic_amount" name="appiappi_hosting_traffic_amount" value="<?php echo esc_attr( $traffic_amount ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'e.g. 100 GB/month', 'appiappi-checkout' ); ?>" <?php disabled( $traffic_unlimited ); ?>>
				<p class="description"><?php esc_html_e( 'Ignored if Unlimited is checked. Left as Unlimited, customers never see a traffic choice for this package.', 'appiappi-checkout' ); ?></p>
			</td>
		</tr>
		<tr>
			<th><label for="appiappi_hosting_annual_price"><?php esc_html_e( 'Annual Subscription Price ($)', 'appiappi-checkout' ); ?></label></th>
			<td><input type="number" step="0.01" min="0" id="appiappi_hosting_annual_price" name="appiappi_hosting_annual_price" value="<?php echo esc_attr( $annual_price ); ?>" class="regular-text" required></td>
		</tr>
	</table>
	<script>
	( function () {
		document.getElementById( 'appiappi_hosting_storage_unlimited' ).addEventListener( 'change', function () {
			document.getElementById( 'appiappi_hosting_storage_amount' ).disabled = this.checked;
		} );
		document.getElementById( 'appiappi_hosting_traffic_unlimited' ).addEventListener( 'change', function () {
			document.getElementById( 'appiappi_hosting_traffic_amount' ).disabled = this.checked;
		} );
	} )();
	</script>
	<?php
}

function appiappi_checkout_hosting_save_meta_box( $post_id ) {
	if ( ! isset( $_POST['appiappi_hosting_nonce'] ) || ! wp_verify_nonce( $_POST['appiappi_hosting_nonce'], 'appiappi_hosting_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	update_post_meta( $post_id, '_appiappi_hosting_location', isset( $_POST['appiappi_hosting_location'] ) ? sanitize_text_field( wp_unslash( $_POST['appiappi_hosting_location'] ) ) : '' );
	update_post_meta( $post_id, '_appiappi_hosting_storage_unlimited', isset( $_POST['appiappi_hosting_storage_unlimited'] ) ? 1 : 0 );
	update_post_meta( $post_id, '_appiappi_hosting_storage_amount', isset( $_POST['appiappi_hosting_storage_amount'] ) ? sanitize_text_field( wp_unslash( $_POST['appiappi_hosting_storage_amount'] ) ) : '' );
	update_post_meta( $post_id, '_appiappi_hosting_traffic_unlimited', isset( $_POST['appiappi_hosting_traffic_unlimited'] ) ? 1 : 0 );
	update_post_meta( $post_id, '_appiappi_hosting_traffic_amount', isset( $_POST['appiappi_hosting_traffic_amount'] ) ? sanitize_text_field( wp_unslash( $_POST['appiappi_hosting_traffic_amount'] ) ) : '' );
	if ( isset( $_POST['appiappi_hosting_annual_price'] ) ) {
		update_post_meta( $post_id, '_appiappi_hosting_annual_price', max( 0, (float) $_POST['appiappi_hosting_annual_price'] ) );
	}
}
add_action( 'save_post_appiappi_hosting', 'appiappi_checkout_hosting_save_meta_box' );

/**
 * Every published hosting package, shaped for both PHP (pricing.php's
 * validation) and the frontend (localized as JSON for the cascading
 * Location → Storage → Traffic selectors in checkout.js).
 */
function appiappi_checkout_get_hosting_packages() {
	$posts = get_posts( array(
		'post_type'      => 'appiappi_hosting',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
	) );

	$packages = array();
	foreach ( $posts as $post ) {
		$packages[] = array(
			'id'                => $post->ID,
			'location'          => get_post_meta( $post->ID, '_appiappi_hosting_location', true ),
			'storageUnlimited'  => (bool) get_post_meta( $post->ID, '_appiappi_hosting_storage_unlimited', true ),
			'storageAmount'     => get_post_meta( $post->ID, '_appiappi_hosting_storage_amount', true ),
			'trafficUnlimited'  => (bool) get_post_meta( $post->ID, '_appiappi_hosting_traffic_unlimited', true ),
			'trafficAmount'     => get_post_meta( $post->ID, '_appiappi_hosting_traffic_amount', true ),
			'annualPrice'       => (float) get_post_meta( $post->ID, '_appiappi_hosting_annual_price', true ),
		);
	}
	return $packages;
}

function appiappi_checkout_get_hosting_package( $hosting_id ) {
	$post = get_post( (int) $hosting_id );
	if ( ! $post || 'appiappi_hosting' !== $post->post_type || 'publish' !== $post->post_status ) {
		return null;
	}
	foreach ( appiappi_checkout_get_hosting_packages() as $package ) {
		if ( $package['id'] === $post->ID ) {
			return $package;
		}
	}
	return null;
}
