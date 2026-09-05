<?php
/**
 * Template Name: Privacy Policy Page
 * Auto-applies to a page with the slug "privacy-policy". Unlike
 * page-about.php, this copy is hard-coded here (not the_content()) —
 * it's boilerplate legal text, not marketing copy the business owner
 * should freehand edit — but every business-specific detail (legal
 * name, jurisdiction, address, privacy contact, retention period) is
 * pulled from Settings → Appiappi Settings → Legal & Company
 * Information (appiappi_get_setting(), inc/admin/settings-page.php) so
 * nothing is hard-coded or fabricated. A field left empty simply omits
 * the sentence/row that depends on it rather than showing a
 * placeholder in a published legal page.
 */

get_header();

$company_legal_name = appiappi_get_setting( 'company_legal_name' );
$display_name        = $company_legal_name ?: 'Appiappi';
$company_address      = appiappi_get_setting( 'company_address' );
$privacy_email        = appiappi_get_setting( 'privacy_email' ) ?: appiappi_get_setting( 'general_email' );
$privacy_officer_name = appiappi_get_setting( 'privacy_officer_name' );
$data_retention_period = appiappi_get_setting( 'data_retention_period' );
?>

<main id="main-content">
	<?php appiappi_breadcrumbs(); ?>
	<?php appiappi_page_header(); ?>

	<article class="section">
		<div class="container single-post">
			<div class="single-post__content">
				<p class="legal-updated"><?php esc_html_e( 'Last Updated: September 6, 2026', 'appiappi' ); ?></p>

				<p><?php esc_html_e( 'Appiappi respects your privacy and is committed to protecting the personal information entrusted to us.', 'appiappi' ); ?></p>
				<p>
					<?php
					printf(
						/* translators: %s: website URL */
						esc_html__( 'This Privacy Policy explains how Appiappi collects, uses, discloses, stores and protects personal information when you visit %s, contact us, request a consultation, purchase or subscribe to our services, or otherwise interact with us.', 'appiappi' ),
						'<code>https://appiappi.com</code>'
					);
					?>
				</p>
				<p>
					<?php
					printf(
						/* translators: %s: full legal company name */
						esc_html__( 'For the purposes of this Privacy Policy, "Appiappi," "we," "us" and "our" refer to %s, operating the website at https://appiappi.com.', 'appiappi' ),
						'<strong>' . esc_html( $display_name ) . '</strong>'
					);
					?>
				</p>

				<h2><?php esc_html_e( 'Scope of This Policy', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'This Privacy Policy applies to personal information collected through:', 'appiappi' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Our website;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Contact and consultation forms;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Website design or service request forms;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Email, telephone or other communications;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Client onboarding processes;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Service delivery and support interactions;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Marketing and analytics activities related to our website.', 'appiappi' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'This policy does not apply to third-party websites, platforms or services that may be linked from our website. Those services have their own privacy policies, and we encourage you to review them before providing personal information.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Information We Collect', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'Depending on how you interact with us, we may collect the following categories of information.', 'appiappi' ); ?></p>

				<h3><?php esc_html_e( 'Contact and Business Information', 'appiappi' ); ?></h3>
				<p><?php esc_html_e( 'This may include:', 'appiappi' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Name;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Business name;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Email address;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Telephone number;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Business address or service area;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Website address;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Industry or business type;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Information about your services and customers.', 'appiappi' ); ?></li>
				</ul>

				<h3><?php esc_html_e( 'Project and Service Information', 'appiappi' ); ?></h3>
				<p><?php esc_html_e( 'If you request or purchase our services, we may collect information required to deliver those services, such as:', 'appiappi' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Branding information;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Logo files;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Brand colours and preferences;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Service descriptions;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Website content;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Images and media;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Domain and hosting details;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Website login or technical access information;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Project instructions and communications;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Billing and account information.', 'appiappi' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'You should not provide passwords or sensitive credentials through an unsecured form or ordinary email unless we specifically instruct you to do so using an approved secure process.', 'appiappi' ); ?></p>

				<h3><?php esc_html_e( 'Payment Information', 'appiappi' ); ?></h3>
				<p><?php esc_html_e( 'If payments are processed through a third-party payment provider, that provider may collect and process your payment card or financial information. Appiappi may receive transaction details such as payment status, invoice information and limited billing information, but we generally do not store complete payment card numbers.', 'appiappi' ); ?></p>

				<h3><?php esc_html_e( 'Technical and Usage Information', 'appiappi' ); ?></h3>
				<p><?php esc_html_e( 'When you use our website, certain technical information may be collected automatically, including:', 'appiappi' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'IP address;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Browser type;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Device type;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Operating system;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Referring website;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Pages viewed;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Time spent on pages;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'General location information;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Website interaction data;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Error and performance information.', 'appiappi' ); ?></li>
				</ul>

				<h3><?php esc_html_e( 'Cookies and Similar Technologies', 'appiappi' ); ?></h3>
				<p><?php esc_html_e( 'We may use cookies, pixels, analytics tools and similar technologies to:', 'appiappi' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Operate and secure the website;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Remember user preferences;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Understand how visitors use the website;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Measure website performance;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Improve content and user experience;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Support marketing activities where permitted.', 'appiappi' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'You can modify your browser settings to refuse or limit cookies. Some website features may not function properly if certain cookies are disabled.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'How We Use Personal Information', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'We may use personal information for the following purposes:', 'appiappi' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Responding to inquiries;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Scheduling consultations;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Preparing proposals or service recommendations;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Providing website design, hosting, SEO, maintenance and support services;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Creating and managing client accounts;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Processing invoices and payments;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Communicating about projects, services and support requests;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Maintaining, securing and improving our website;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Monitoring service performance;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Preventing fraud, abuse or unauthorized activity;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Complying with legal, regulatory and contractual obligations;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Sending marketing communications where permitted and where you have provided consent or where otherwise permitted by law.', 'appiappi' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'We will not use personal information for purposes that are materially different from those described above without providing additional notice or obtaining consent where required.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Consent', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'By providing personal information to us, you consent to its collection, use and disclosure as described in this Privacy Policy, subject to applicable law.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'You may withdraw consent to certain uses of your personal information by contacting us. Withdrawal of consent does not affect the legality of processing that occurred before consent was withdrawn. In some cases, withdrawing consent may limit our ability to provide certain services.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'Where appropriate, we may rely on other legal grounds recognized by applicable privacy laws, including contractual necessity, legitimate business purposes and legal obligations.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Disclosure of Personal Information', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'We may disclose personal information to service providers and business partners who assist us with:', 'appiappi' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Website hosting and infrastructure;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Email and communication systems;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Payment processing;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Scheduling and appointment management;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Customer relationship management;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Analytics and performance monitoring;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Security and fraud prevention;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Website development and technical support;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Accounting, legal and professional services.', 'appiappi' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'These providers may process personal information on our behalf and are expected to use appropriate safeguards and handle information only for authorized purposes.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'We may also disclose information:', 'appiappi' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'When required by law, court order or legal process;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'To protect the rights, safety, property or security of Appiappi, our clients or others;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'In connection with a merger, acquisition, financing, restructuring or sale of all or part of our business;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'With your consent or at your direction.', 'appiappi' ); ?></li>
				</ul>

				<h2><?php esc_html_e( 'Service Providers Outside Canada', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'Some service providers may store or process information outside Canada, including in the United States or other jurisdictions. Personal information processed outside Canada may be subject to the laws of those jurisdictions and may be accessible to their governments, courts, law enforcement or regulatory authorities.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'We take reasonable steps to select service providers that maintain appropriate privacy and security safeguards.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Retention of Personal Information', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'We retain personal information only for as long as reasonably necessary for the purposes described in this Privacy Policy, including to:', 'appiappi' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Provide and administer services;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Maintain business and financial records;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Resolve disputes;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Enforce agreements;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Meet legal, accounting and regulatory requirements;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Protect our legitimate business interests.', 'appiappi' ); ?></li>
				</ul>
				<?php if ( $data_retention_period ) : ?>
					<p>
						<?php
						printf(
							/* translators: %s: data retention period */
							esc_html__( 'Specifically, personal information collected through this website is generally retained %s.', 'appiappi' ),
							esc_html( $data_retention_period )
						);
						?>
					</p>
				<?php endif; ?>
				<p><?php esc_html_e( 'When information is no longer required, we take reasonable steps to securely delete, destroy or anonymize it.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Security', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'We use reasonable administrative, technical and physical safeguards designed to protect personal information against unauthorized access, use, disclosure, alteration, loss or destruction.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'Depending on the nature of the information, safeguards may include:', 'appiappi' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Access controls;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Password protection;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Secure hosting environments;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Encryption where appropriate;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Backups;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Security monitoring;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Staff and contractor confidentiality obligations;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Regular review of technical and operational practices.', 'appiappi' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'No method of transmission or storage is completely secure. Therefore, we cannot guarantee absolute security.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Access and Correction', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'Subject to applicable law, you may request access to the personal information we hold about you and request correction of inaccurate or incomplete information.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'To make a request, please contact us using the details below. We may need to verify your identity before processing the request. Certain legal exceptions may limit access to some information.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Marketing Communications', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'You may unsubscribe from promotional emails by using the unsubscribe link included in the message or by contacting us directly.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'Even if you opt out of marketing communications, we may continue to send non-promotional messages related to invoices, security, support requests or your contractual relationship with us.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( "Children's Privacy", 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'Our website and services are intended for businesses and general audiences. We do not knowingly collect personal information from children in circumstances where such collection is prohibited by applicable law.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Third-Party Links', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'Our website may contain links to third-party websites or services. We are not responsible for the privacy practices, security or content of those third parties. Your use of third-party services is governed by their own terms and privacy policies.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Changes to This Privacy Policy', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'We may update this Privacy Policy from time to time to reflect changes in our practices, services, technology or legal requirements.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'The updated version will be posted on this page with a revised "Last Updated" date. Your continued use of the website after an update may be subject to the revised policy.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Contact Us', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'If you have questions about this Privacy Policy, wish to request access or correction, or want to withdraw consent, please contact us:', 'appiappi' ); ?></p>
				<p class="legal-contact-block">
					<strong><?php echo esc_html( $display_name ); ?></strong><br>
					<?php esc_html_e( 'Website:', 'appiappi' ); ?> <code>https://appiappi.com</code><br>
					<?php if ( $privacy_email ) : ?>
						<?php esc_html_e( 'Email:', 'appiappi' ); ?> <a href="mailto:<?php echo esc_attr( $privacy_email ); ?>"><?php echo esc_html( $privacy_email ); ?></a><br>
					<?php endif; ?>
					<?php if ( $company_address ) : ?>
						<?php esc_html_e( 'Address:', 'appiappi' ); ?> <?php echo nl2br( esc_html( $company_address ) ); ?><br>
					<?php endif; ?>
					<?php if ( $privacy_officer_name ) : ?>
						<?php esc_html_e( 'Privacy Contact:', 'appiappi' ); ?> <?php echo esc_html( $privacy_officer_name ); ?>
					<?php endif; ?>
				</p>
			</div>
		</div>
	</article>

	<?php get_template_part( 'template-parts/sections/final-cta' ); ?>
</main>

<?php
get_footer();
