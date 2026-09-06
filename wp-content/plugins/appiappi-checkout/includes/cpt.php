<?php
/**
 * Registers the `appiappi_order` post type — every checkout attempt
 * becomes one Order (created as 'pending' the moment a PaymentIntent or
 * Subscription is created, then flipped to 'paid'/'failed' by the Stripe
 * webhook — see includes/webhook.php). Not public, no front-end template,
 * same pattern as appiappi-contact's Leads.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_checkout_register_cpt() {
	register_post_type( 'appiappi_order', array(
		'labels' => array(
			'name'          => __( 'Orders', 'appiappi-checkout' ),
			'singular_name' => __( 'Order', 'appiappi-checkout' ),
			'all_items'     => __( 'Orders', 'appiappi-checkout' ),
			'menu_name'     => __( 'Orders', 'appiappi-checkout' ),
			'not_found'     => __( 'No orders yet.', 'appiappi-checkout' ),
			'edit_item'     => __( 'View Order', 'appiappi-checkout' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'show_in_menu'    => true,
		'show_in_rest'    => false,
		'menu_icon'       => 'dashicons-cart',
		'supports'        => array( 'title' ),
		'capability_type' => 'post',
		'map_meta_cap'    => true,
	) );
}
add_action( 'init', 'appiappi_checkout_register_cpt' );

function appiappi_checkout_order_statuses() {
	return array(
		'pending' => __( 'Pending', 'appiappi-checkout' ),
		'paid'    => __( 'Paid', 'appiappi-checkout' ),
		'failed'  => __( 'Failed', 'appiappi-checkout' ),
		'refunded'=> __( 'Refunded', 'appiappi-checkout' ),
	);
}

function appiappi_checkout_admin_columns( $columns ) {
	$new = array();
	foreach ( $columns as $key => $label ) {
		$new[ $key ] = $label;
		if ( 'title' === $key ) {
			$new['appiappi_order_plan']   = __( 'Plan', 'appiappi-checkout' );
			$new['appiappi_order_amount'] = __( 'Amount', 'appiappi-checkout' );
			$new['appiappi_order_status'] = __( 'Status', 'appiappi-checkout' );
			$new['appiappi_order_mode']   = __( 'Mode', 'appiappi-checkout' );
		}
	}
	return $new;
}
add_filter( 'manage_appiappi_order_posts_columns', 'appiappi_checkout_admin_columns' );

function appiappi_checkout_admin_column_content( $column, $post_id ) {
	if ( 'appiappi_order_plan' === $column ) {
		$plan   = get_post_meta( $post_id, '_appiappi_order_plan_name', true );
		$freq   = get_post_meta( $post_id, '_appiappi_order_billing_frequency', true );
		echo esc_html( $plan ) . ( $freq ? ' <span style="color:#888">(' . esc_html( $freq ) . ')</span>' : '' );
	}
	if ( 'appiappi_order_amount' === $column ) {
		$amount   = get_post_meta( $post_id, '_appiappi_order_total_amount', true );
		$currency = strtoupper( get_post_meta( $post_id, '_appiappi_order_currency', true ) ?: 'cad' );
		echo esc_html( number_format( (float) $amount, 2 ) . ' ' . $currency );
	}
	if ( 'appiappi_order_status' === $column ) {
		$statuses = appiappi_checkout_order_statuses();
		$status   = get_post_meta( $post_id, '_appiappi_order_status', true ) ?: 'pending';
		$color    = array( 'paid' => '#1a7f37', 'failed' => '#c9252d', 'refunded' => '#9a6700', 'pending' => '#787c82' );
		printf( '<strong style="color:%s">%s</strong>', esc_attr( $color[ $status ] ?? '#333' ), esc_html( $statuses[ $status ] ?? $status ) );
	}
	if ( 'appiappi_order_mode' === $column ) {
		$mode = get_post_meta( $post_id, '_appiappi_order_stripe_mode', true );
		echo esc_html( $mode ? ucfirst( $mode ) : '—' );
	}
}
add_action( 'manage_appiappi_order_posts_custom_column', 'appiappi_checkout_admin_column_content', 10, 2 );

function appiappi_checkout_add_meta_box() {
	add_meta_box( 'appiappi_order_details', __( 'Order Details', 'appiappi-checkout' ), 'appiappi_checkout_render_meta_box', 'appiappi_order', 'normal', 'high' );
}
add_action( 'add_meta_boxes', 'appiappi_checkout_add_meta_box' );

function appiappi_checkout_render_meta_box( $post ) {
	wp_nonce_field( 'appiappi_order_save', 'appiappi_order_nonce' );

	$fields = array(
		'design_name'         => __( 'Website Design', 'appiappi-checkout' ),
		'design_price'        => __( 'Design Price', 'appiappi-checkout' ),
		'plan_name'           => __( 'Plan', 'appiappi-checkout' ),
		'billing_frequency'   => __( 'Billing Frequency', 'appiappi-checkout' ),
		'discount_percent'    => __( 'Discount Applied (%)', 'appiappi-checkout' ),
		'total_amount'        => __( 'Total Amount', 'appiappi-checkout' ),
		'currency'            => __( 'Currency', 'appiappi-checkout' ),
		'customer_name'       => __( 'Customer Name', 'appiappi-checkout' ),
		'customer_email'      => __( 'Customer Email', 'appiappi-checkout' ),
		'customer_phone'      => __( 'Customer Phone', 'appiappi-checkout' ),
		'stripe_mode'         => __( 'Stripe Mode', 'appiappi-checkout' ),
		'stripe_payment_intent_id' => __( 'Stripe Payment Intent ID', 'appiappi-checkout' ),
		'stripe_subscription_id'   => __( 'Stripe Subscription ID', 'appiappi-checkout' ),
		'stripe_customer_id'  => __( 'Stripe Customer ID', 'appiappi-checkout' ),
	);
	?>
	<table class="form-table">
		<?php foreach ( $fields as $key => $label ) : ?>
			<tr>
				<th><?php echo esc_html( $label ); ?></th>
				<td><?php echo esc_html( get_post_meta( $post->ID, '_appiappi_order_' . $key, true ) ?: '—' ); ?></td>
			</tr>
		<?php endforeach; ?>
		<tr>
			<th><label for="appiappi_order_status"><?php esc_html_e( 'Status', 'appiappi-checkout' ); ?></label></th>
			<td>
				<select id="appiappi_order_status" name="appiappi_order_status">
					<?php $current = get_post_meta( $post->ID, '_appiappi_order_status', true ) ?: 'pending'; ?>
					<?php foreach ( appiappi_checkout_order_statuses() as $value => $label ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $current, $value ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description"><?php esc_html_e( 'Normally set automatically by the Stripe webhook. Change manually only to correct a record (e.g. after a manual refund in the Stripe Dashboard).', 'appiappi-checkout' ); ?></p>
			</td>
		</tr>
	</table>
	<?php
}

function appiappi_checkout_save_meta_box( $post_id ) {
	if ( ! isset( $_POST['appiappi_order_nonce'] ) || ! wp_verify_nonce( $_POST['appiappi_order_nonce'], 'appiappi_order_save' ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['appiappi_order_status'] ) && array_key_exists( $_POST['appiappi_order_status'], appiappi_checkout_order_statuses() ) ) {
		update_post_meta( $post_id, '_appiappi_order_status', sanitize_key( $_POST['appiappi_order_status'] ) );
	}
}
add_action( 'save_post_appiappi_order', 'appiappi_checkout_save_meta_box' );
