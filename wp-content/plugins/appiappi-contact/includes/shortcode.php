<?php
/**
 * [appiappi_contact_form] shortcode. Renders the form itself; submission
 * is handled separately on `template_redirect` (see handler.php) so the
 * page can redirect before output starts. Reads `?appiappi_contact=` to
 * show a success/error banner after the redirect back.
 *
 * Deliberately does NOT go through a theme shared-render-function like
 * the other companion plugins: an inert "placeholder form" with no
 * working handler behind it would be misleading, so the theme instead
 * shows a simple mailto/phone fallback (see page-contact.php) when this
 * plugin isn't active, rather than a fake copy of this form.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_contact_form_shortcode() {
	$services = function_exists( 'appiappi_get_services' ) ? wp_list_pluck( appiappi_get_services(), 'name' ) : array();

	// Selection workflow: arrived via a template's "Choose This Design" link.
	$selected_design = isset( $_GET['design'] ) ? sanitize_text_field( wp_unslash( $_GET['design'] ) ) : '';
	$selected_plan   = isset( $_GET['plan'] ) ? sanitize_text_field( wp_unslash( $_GET['plan'] ) ) : '';
	$plan_labels     = array( 'starter' => __( 'Starter', 'appiappi-contact' ), 'business' => __( 'Business', 'appiappi-contact' ), 'professional' => __( 'Professional', 'appiappi-contact' ), 'growth' => __( 'Growth', 'appiappi-contact' ) );
	$selected_plan_label = $plan_labels[ strtolower( $selected_plan ) ] ?? $selected_plan;

	ob_start();
	?>
	<div id="contact-form">
		<?php if ( isset( $_GET['appiappi_contact'] ) && 'success' === $_GET['appiappi_contact'] ) : ?>
			<div class="form-notice form-notice--success"><?php esc_html_e( 'Thanks — your message has been sent. We\'ll be in touch soon.', 'appiappi-contact' ); ?></div>
		<?php elseif ( isset( $_GET['appiappi_contact'] ) && 'error' === $_GET['appiappi_contact'] ) : ?>
			<div class="form-notice form-notice--error"><?php esc_html_e( 'Please fill in your name, email and message, then try again.', 'appiappi-contact' ); ?></div>
		<?php endif; ?>

		<?php if ( $selected_design ) : ?>
			<div class="form-notice form-notice--success">
				<?php
				printf(
					/* translators: 1: selected design name, 2: recommended plan name */
					esc_html__( 'You selected: %1$s — Recommended plan: %2$s', 'appiappi-contact' ),
					'<strong>' . esc_html( $selected_design ) . '</strong>',
					'<strong>' . esc_html( $selected_plan_label ?: __( 'Not sure yet', 'appiappi-contact' ) ) . '</strong>'
				);
				?>
			</div>
		<?php endif; ?>

		<form method="post" action="<?php echo esc_url( add_query_arg( null, null ) ); ?>">
			<?php wp_nonce_field( 'appiappi_contact_submit', 'appiappi_contact_nonce' ); ?>
			<input type="text" name="appiappi_contact_hp" value="" autocomplete="off" tabindex="-1" aria-hidden="true" style="position:absolute; left:-9999px;">
			<?php if ( $selected_design ) : ?>
				<input type="hidden" name="appiappi_contact_selected_design" value="<?php echo esc_attr( $selected_design ); ?>">
				<input type="hidden" name="appiappi_contact_selected_plan" value="<?php echo esc_attr( $selected_plan_label ); ?>">
			<?php endif; ?>

			<div class="form-grid">
				<div class="form-field">
					<label for="appiappi_contact_name"><?php esc_html_e( 'Name', 'appiappi-contact' ); ?> *</label>
					<input type="text" id="appiappi_contact_name" name="appiappi_contact_name" required>
				</div>
				<div class="form-field">
					<label for="appiappi_contact_business"><?php esc_html_e( 'Business Name', 'appiappi-contact' ); ?></label>
					<input type="text" id="appiappi_contact_business" name="appiappi_contact_business">
				</div>
				<div class="form-field">
					<label for="appiappi_contact_email"><?php esc_html_e( 'Email', 'appiappi-contact' ); ?> *</label>
					<input type="email" id="appiappi_contact_email" name="appiappi_contact_email" required>
				</div>
				<div class="form-field">
					<label for="appiappi_contact_phone"><?php esc_html_e( 'Phone', 'appiappi-contact' ); ?></label>
					<input type="tel" id="appiappi_contact_phone" name="appiappi_contact_phone">
				</div>
				<div class="form-field">
					<label for="appiappi_contact_website"><?php esc_html_e( 'Current Website (if any)', 'appiappi-contact' ); ?></label>
					<input type="text" id="appiappi_contact_website" name="appiappi_contact_website" placeholder="yourbusiness.ca">
				</div>
				<div class="form-field">
					<label for="appiappi_contact_interested_service"><?php esc_html_e( 'Interested Service', 'appiappi-contact' ); ?></label>
					<select id="appiappi_contact_interested_service" name="appiappi_contact_interested_service">
						<option value=""><?php esc_html_e( 'Select one', 'appiappi-contact' ); ?></option>
						<?php foreach ( $services as $service ) : ?>
							<option value="<?php echo esc_attr( $service ); ?>"><?php echo esc_html( $service ); ?></option>
						<?php endforeach; ?>
						<option value="<?php esc_attr_e( 'Not sure yet', 'appiappi-contact' ); ?>"><?php esc_html_e( 'Not sure yet', 'appiappi-contact' ); ?></option>
					</select>
				</div>
				<div class="form-field form-field--full">
					<label for="appiappi_contact_message"><?php esc_html_e( 'Message', 'appiappi-contact' ); ?> *</label>
					<textarea id="appiappi_contact_message" name="appiappi_contact_message" rows="5" required></textarea>
				</div>
			</div>

			<button type="submit" name="appiappi_contact_submit" value="1" class="btn btn-primary"><?php esc_html_e( 'Send Message', 'appiappi-contact' ); ?></button>
		</form>
	</div>
	<?php
	return ob_get_clean();
}
add_shortcode( 'appiappi_contact_form', 'appiappi_contact_form_shortcode' );
