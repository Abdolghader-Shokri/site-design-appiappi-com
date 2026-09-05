<?php
/**
 * Processes the contact form POST on `template_redirect` — early enough
 * to redirect before any theme output starts (a shortcode callback runs
 * too late in the page lifecycle to safely redirect). Post/Redirect/Get:
 * always redirects back to the referring page with a query flag so a
 * page refresh never resubmits the form.
 *
 * Required fields: name, email, message. A honeypot field
 * (appiappi_contact_hp) silently drops obvious bot submissions without
 * telling the bot anything failed.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_contact_handle_submission() {
	if ( ! isset( $_POST['appiappi_contact_submit'] ) ) {
		return;
	}

	$redirect_to = wp_get_referer() ?: home_url( '/' );

	if ( ! isset( $_POST['appiappi_contact_nonce'] ) || ! wp_verify_nonce( $_POST['appiappi_contact_nonce'], 'appiappi_contact_submit' ) ) {
		wp_safe_redirect( add_query_arg( 'appiappi_contact', 'error', $redirect_to ) . '#contact-form' );
		exit;
	}

	// Honeypot: bots fill every field, including this hidden one. Pretend success.
	if ( ! empty( $_POST['appiappi_contact_hp'] ) ) {
		wp_safe_redirect( add_query_arg( 'appiappi_contact', 'success', $redirect_to ) . '#contact-form' );
		exit;
	}

	$name    = isset( $_POST['appiappi_contact_name'] ) ? sanitize_text_field( wp_unslash( $_POST['appiappi_contact_name'] ) ) : '';
	$email   = isset( $_POST['appiappi_contact_email'] ) ? sanitize_email( wp_unslash( $_POST['appiappi_contact_email'] ) ) : '';
	$message = isset( $_POST['appiappi_contact_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['appiappi_contact_message'] ) ) : '';

	if ( ! $name || ! $email || ! is_email( $email ) || ! $message ) {
		wp_safe_redirect( add_query_arg( 'appiappi_contact', 'error', $redirect_to ) . '#contact-form' );
		exit;
	}

	$business           = isset( $_POST['appiappi_contact_business'] ) ? sanitize_text_field( wp_unslash( $_POST['appiappi_contact_business'] ) ) : '';
	$phone              = isset( $_POST['appiappi_contact_phone'] ) ? sanitize_text_field( wp_unslash( $_POST['appiappi_contact_phone'] ) ) : '';
	$business_type      = isset( $_POST['appiappi_contact_business_type'] ) ? sanitize_text_field( wp_unslash( $_POST['appiappi_contact_business_type'] ) ) : '';
	$interested_service = isset( $_POST['appiappi_contact_interested_service'] ) ? sanitize_text_field( wp_unslash( $_POST['appiappi_contact_interested_service'] ) ) : '';
	$budget_range       = isset( $_POST['appiappi_contact_budget_range'] ) ? sanitize_text_field( wp_unslash( $_POST['appiappi_contact_budget_range'] ) ) : '';

	$post_id = wp_insert_post( array(
		'post_type'   => 'appiappi_lead',
		'post_title'  => $business ? sprintf( '%s — %s', $name, $business ) : $name,
		'post_status' => 'publish',
	) );

	if ( ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, '_appiappi_lead_email', $email );
		update_post_meta( $post_id, '_appiappi_lead_business', $business );
		update_post_meta( $post_id, '_appiappi_lead_phone', $phone );
		update_post_meta( $post_id, '_appiappi_lead_business_type', $business_type );
		update_post_meta( $post_id, '_appiappi_lead_interested_service', $interested_service );
		update_post_meta( $post_id, '_appiappi_lead_budget_range', $budget_range );
		update_post_meta( $post_id, '_appiappi_lead_message', $message );
		update_post_meta( $post_id, '_appiappi_lead_status', 'new' );

		appiappi_contact_send_notification_email( $name, $email, $business, $phone, $interested_service, $message, $post_id );
	}

	wp_safe_redirect( add_query_arg( 'appiappi_contact', 'success', $redirect_to ) . '#contact-form' );
	exit;
}
add_action( 'template_redirect', 'appiappi_contact_handle_submission' );

function appiappi_contact_send_notification_email( $name, $email, $business, $phone, $interested_service, $message, $post_id ) {
	$to      = get_option( 'admin_email' );
	$subject = sprintf( '[%s] New contact form submission from %s', get_bloginfo( 'name' ), $name );
	$body    = implode( "\n", array(
		"Name: {$name}",
		"Business: {$business}",
		"Email: {$email}",
		"Phone: {$phone}",
		"Interested Service: {$interested_service}",
		'',
		'Message:',
		$message,
		'',
		'View this lead: ' . admin_url( 'post.php?post=' . $post_id . '&action=edit' ),
	) );

	wp_mail( $to, $subject, $body, array( 'Reply-To: ' . $email ) );
}
