<?php
/**
 * Template Name: Terms of Service Page
 * Auto-applies to a page with the slug "terms". Same reasoning as
 * page-privacy-policy.php: hard-coded legal boilerplate, not editorial
 * content, with every business-specific detail pulled from
 * Settings → Appiappi Settings → Legal & Company Information
 * (appiappi_get_setting()). A field left empty falls back to generic
 * (but still legally sound) wording rather than a bracketed placeholder.
 */

get_header();

$company_legal_name    = appiappi_get_setting( 'company_legal_name' );
$site_domain           = appiappi_get_setting( 'site_domain' ) ?: 'appiappi.com';
$display_name          = $company_legal_name ?: 'Appiappi';
$incorporation_province = appiappi_get_setting( 'incorporation_province' );
$company_address       = appiappi_get_setting( 'company_address' );
$general_email         = appiappi_get_setting( 'general_email' );
$payment_method        = appiappi_get_setting( 'payment_method' );
$cancellation_policy   = appiappi_get_setting( 'cancellation_policy' );
$support_response_time = appiappi_get_setting( 'support_response_time' );
$portfolio_policy      = appiappi_get_setting( 'portfolio_display_default' );
$ownership_details     = appiappi_get_setting( 'ownership_details' );
?>

<main id="main-content">
	<?php appiappi_breadcrumbs(); ?>
	<?php appiappi_page_header(); ?>

	<article class="section">
		<div class="container single-post">
			<div class="single-post__content">
				<p class="legal-updated"><?php esc_html_e( 'Last Updated: September 6, 2026', 'appiappi' ); ?></p>

				<p><?php printf( esc_html__( 'These Terms of Service govern your access to and use of %s and any website design, hosting, SEO, maintenance, content management, support or related services provided by Appiappi.', 'appiappi' ), '<code>' . esc_html( $site_domain ) . '</code>' ); ?></p>
				<p><?php esc_html_e( 'By accessing our website, submitting a request, approving a proposal, purchasing a plan, subscribing to a service or using our services, you agree to these Terms of Service. If you do not agree with these terms, you should not use the website or services.', 'appiappi' ); ?></p>
				<p>
					<?php
					printf(
						/* translators: %s: full legal company name */
						esc_html__( 'In these terms, "Appiappi," "we," "us" and "our" refer to %s, the service provider, while "you," "your" and "client" refer to the person or business using or purchasing our services.', 'appiappi' ),
						'<strong>' . esc_html( $display_name ) . '</strong>'
					);
					?>
				</p>

				<h2><?php esc_html_e( 'Services', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'Appiappi may provide services including:', 'appiappi' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Website design and development;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'WordPress installation and configuration;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Website customization;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Managed hosting;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Website maintenance;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Security and backup management;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Content updates;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Search engine optimization;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Website support;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Related consulting and digital services.', 'appiappi' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'The exact scope of services, deliverables, timelines, fees and responsibilities will be described in the applicable order, proposal, plan description, statement of work or service agreement.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'If there is a conflict between these Terms and a signed written agreement, the signed agreement will control to the extent of the conflict.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Website Designs and Customization', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'Our website designs, templates and demo websites may be used as starting points for client projects.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'Selecting a design does not necessarily mean that every element shown in the demonstration will be included in your final website. Final functionality, pages, content, integrations, images, plugins and customizations depend on the selected plan, project scope and agreed deliverables.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'We may modify the design to accommodate your branding, content, business requirements, technical environment and approved project scope.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Client Responsibilities', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'To provide services effectively, you agree to:', 'appiappi' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Provide accurate and complete business information;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Supply required content, images, logos and brand assets;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Provide timely feedback and approvals;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Maintain ownership or obtain permission to use materials supplied to us;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Provide accurate domain, hosting and technical information;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Ensure that submitted content complies with applicable laws;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Maintain appropriate access to required accounts;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Review deliverables within the agreed review period;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Pay all fees when due.', 'appiappi' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'Delays in receiving information, approvals, access or payments may affect project timelines and launch dates.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Client Content and Materials', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'You retain ownership of the content and materials you provide to us, subject to any rights held by third parties.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'You grant Appiappi a limited, non-exclusive permission to use, reproduce, modify, format, publish and display those materials as necessary to provide the services.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'You represent that:', 'appiappi' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'You own the materials or have the necessary rights to use them;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( "The materials do not infringe another party's copyright, trademark, privacy or other rights;", 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'The materials comply with applicable advertising and industry requirements.', 'appiappi' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'You are responsible for the accuracy, legality and suitability of your business claims, service descriptions, pricing, testimonials, licences, certifications and regulatory disclosures.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Fees and Payment', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'Fees will be shown in Canadian dollars unless otherwise stated.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'Depending on the selected service, fees may include:', 'appiappi' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'One-time setup or project fees;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Monthly subscription fees;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Hosting fees;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'SEO or content management fees;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Third-party software or licence fees;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Additional work outside the agreed scope;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Taxes required by applicable law.', 'appiappi' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'One-time fees are generally payable according to the payment terms shown in the applicable proposal, order or checkout process.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'Monthly subscription fees are billed in advance unless otherwise stated. You authorize Appiappi or its payment provider to charge the selected payment method for recurring fees until the subscription is cancelled or terminated.', 'appiappi' ); ?></p>
				<?php if ( $payment_method ) : ?>
					<p>
						<?php
						printf(
							/* translators: %s: payment method & provider */
							esc_html__( 'Payments are currently processed via %s.', 'appiappi' ),
							esc_html( $payment_method )
						);
						?>
					</p>
				<?php endif; ?>
				<p><?php esc_html_e( 'You are responsible for applicable taxes, payment processing charges and third-party costs unless expressly included in writing.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Additional Work and Scope Changes', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'Requests outside the agreed scope may require additional fees and may affect delivery timelines.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'Additional work may include:', 'appiappi' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'New pages or sections;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Custom functionality;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Additional design revisions;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Third-party integrations;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Copywriting or extensive content creation;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Migration of complex websites;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Advanced SEO campaigns;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Custom illustrations, photography or video;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Emergency or priority requests;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Work caused by unauthorized changes made by third parties.', 'appiappi' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'We will communicate the additional scope and pricing before beginning the work whenever reasonably possible.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Revisions and Approvals', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'The number of included revisions, review periods and approval procedures will depend on the selected plan or project agreement.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'You are responsible for reviewing content, design, functionality and business information before approval or launch. Once a deliverable has been approved or the website has been launched, further changes may be treated as additional work.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Website Launch', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'We may assist with technical launch activities such as:', 'appiappi' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Domain connection;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'DNS configuration;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'SSL setup;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Hosting configuration;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'WordPress deployment;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Basic performance and security checks;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Form and functionality testing.', 'appiappi' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'Launch timing depends on receiving required content, approvals, account access and domain information. We do not guarantee that third-party domain registrars, hosting providers or external services will be available without interruption.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Managed Hosting', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'Where managed hosting is included in your plan, we may provide hosting administration, backups, security monitoring, performance management and related technical services as described in the selected plan.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'Managed hosting does not guarantee that every third-party plugin, integration or custom code will remain compatible indefinitely. We may recommend replacing, updating or removing software that creates security, performance or compatibility risks.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'Unless expressly agreed otherwise, managed hosting does not include unlimited storage, unlimited traffic, email hosting, third-party licence costs or custom server development.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Website Management and Maintenance', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'Maintenance services may include WordPress, theme and plugin updates, backups, security checks, technical adjustments and other tasks specified in your plan.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'We will take reasonable steps to reduce the risk of interruptions during maintenance. However, updates and third-party software may occasionally cause compatibility issues. We may delay, modify or reverse an update when reasonably necessary to protect website stability or security.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'SEO Services', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'SEO services are intended to improve technical quality, search visibility and the likelihood of attracting relevant organic traffic.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'SEO results depend on many factors outside our control, including competition, search engine algorithms, industry demand, website history, content quality, technical limitations, client approvals and market conditions.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'Accordingly:', 'appiappi' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'We do not guarantee specific rankings;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'We do not guarantee a specific number of leads or sales;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'We do not guarantee a specific traffic increase;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Search engine results may change without notice;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'SEO work may require ongoing investment and cooperation.', 'appiappi' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'We will not use deceptive, unlawful or prohibited optimization practices.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Content Management', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'Content management may include updating text, images, business information, services, contact details, forms and selected website sections according to the applicable plan.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'The client remains responsible for the accuracy and legal compliance of supplied content. Extensive copywriting, strategy, photography, translation, video production or content creation may require a separate agreement or additional fee.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Support', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'Support is provided through the channels and within the response targets described in the selected plan or service agreement.', 'appiappi' ); ?></p>
				<?php if ( $support_response_time ) : ?>
					<p>
						<?php
						printf(
							/* translators: %s: support response time */
							esc_html__( 'We aim to respond to support requests %s.', 'appiappi' ),
							esc_html( $support_response_time )
						);
						?>
					</p>
				<?php endif; ?>
				<p><?php esc_html_e( 'Support response time is not the same as resolution time. Resolution may depend on the complexity of the issue, third-party providers, client access, approvals and the availability of required information.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'Support plans do not generally include unrelated IT support, device repair, email troubleshooting, third-party software support or work on websites not managed by Appiappi unless agreed in writing.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Third-Party Services', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'Our services may depend on third-party providers, including:', 'appiappi' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Domain registrars;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Hosting infrastructure;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Payment processors;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Email providers;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Analytics platforms;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Search engines;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Premium themes;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Plugins;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Fonts, stock images or other licensed resources.', 'appiappi' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'Third-party services are subject to their own terms, pricing, availability and policies. Appiappi is not responsible for changes, interruptions, failures or decisions made by third-party providers.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Intellectual Property', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'Appiappi retains ownership of its pre-existing materials, systems, processes, code libraries, frameworks, templates, design systems, documentation, know-how and other proprietary resources.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( "Unless otherwise stated in writing, purchasing services does not transfer ownership of Appiappi's reusable tools, frameworks, templates or systems.", 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'Subject to full payment of all amounts due, you may use the final website deliverables created specifically for your business for your business purposes, subject to third-party licences and the restrictions described in the applicable agreement.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( "Third-party themes, plugins, fonts, images, software and other resources remain subject to their respective licences.", 'appiappi' ); ?></p>
				<?php if ( $ownership_details ) : ?>
					<p><strong><?php esc_html_e( 'Ownership specific to your project:', 'appiappi' ); ?></strong> <?php echo nl2br( esc_html( $ownership_details ) ); ?></p>
				<?php endif; ?>

				<h2><?php esc_html_e( 'Portfolio and Marketing Use', 'appiappi' ); ?></h2>
				<?php if ( 'default_no' === $portfolio_policy ) : ?>
					<p><?php esc_html_e( "We will not identify your business name or display screenshots or links to your completed website in our portfolio, case studies or marketing materials unless you give us written permission to do so.", 'appiappi' ); ?></p>
				<?php else : ?>
					<p><?php esc_html_e( 'Unless otherwise agreed in writing, Appiappi may identify your business name and display screenshots or links to the completed website in our portfolio, case studies and marketing materials.', 'appiappi' ); ?></p>
					<p><?php esc_html_e( 'You may request that portfolio use be limited or excluded by contacting us before project completion or as otherwise specified in the applicable agreement.', 'appiappi' ); ?></p>
				<?php endif; ?>

				<h2><?php esc_html_e( 'Accounts and Security', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'You are responsible for maintaining the confidentiality of your account information and for notifying us promptly of suspected unauthorized access.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'You must not:', 'appiappi' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Share administrative credentials improperly;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Attempt to access systems without authorization;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Upload malicious code;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Use the website or services for unlawful activity;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Interfere with the security or operation of our systems;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Use the services to distribute spam, malware or fraudulent content.', 'appiappi' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'We may suspend access or services where necessary to protect systems, users or third parties.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Cancellation of Monthly Services', 'appiappi' ); ?></h2>
				<?php if ( $cancellation_policy ) : ?>
					<p><?php echo nl2br( esc_html( $cancellation_policy ) ); ?></p>
				<?php else : ?>
					<p><?php esc_html_e( 'Monthly services may be cancelled according to the cancellation terms presented at checkout, in your proposal or in the applicable service agreement.', 'appiappi' ); ?></p>
					<p><?php esc_html_e( 'Unless a different notice period is stated in writing, cancellation requests should be submitted before the next billing date. Fees already charged may not be refundable unless required by law or expressly agreed otherwise.', 'appiappi' ); ?></p>
				<?php endif; ?>
				<p><?php esc_html_e( 'Following cancellation, services may be discontinued at the end of the applicable paid period. Additional fees may apply for website migration, data export, account transfer or technical handover.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Suspension and Termination', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'We may suspend or terminate services if:', 'appiappi' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Fees remain unpaid;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'You materially breach these Terms;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Your use creates a security, legal or operational risk;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'You provide unlawful or infringing content;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'You engage in abusive, fraudulent or unauthorized activity;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Suspension is required by a third-party provider or applicable law.', 'appiappi' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'Where reasonably possible, we will provide notice and an opportunity to resolve the issue.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Disclaimer', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'The website and services are provided on an "as available" and "as is" basis, except where a written agreement provides otherwise.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'To the fullest extent permitted by applicable law, Appiappi does not guarantee that:', 'appiappi' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'The website will always be available;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'The website will be free from errors;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'All third-party services will remain available;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Search engine rankings will improve;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'The website will produce a specific number of leads, sales or conversions;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'The website will meet every business objective without ongoing cooperation and improvement.', 'appiappi' ); ?></li>
				</ul>
				<p><?php esc_html_e( 'Nothing in these Terms excludes any consumer protection, statutory warranty or other right that cannot legally be excluded or limited.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Limitation of Liability', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'To the fullest extent permitted by applicable law, Appiappi will not be liable for indirect, incidental, special, consequential or punitive damages, or for loss of profits, revenue, business opportunities, data or goodwill arising from or related to the use of our website or services.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( "Where liability cannot be excluded, Appiappi's total aggregate liability will be limited to the amount paid by the client to Appiappi for the specific services giving rise to the claim during the applicable period, unless a different limit is required by law or stated in a written agreement.", 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Indemnification', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'You agree to defend, indemnify and hold harmless Appiappi, its contractors, service providers and representatives from claims, damages, liabilities, costs and expenses arising from:', 'appiappi' ); ?></p>
				<ul>
					<li><?php esc_html_e( 'Your breach of these Terms;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Your content or materials;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Your violation of applicable law;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Your infringement of third-party rights;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Your misuse of the website or services;', 'appiappi' ); ?></li>
					<li><?php esc_html_e( 'Unauthorized activity associated with your account.', 'appiappi' ); ?></li>
				</ul>

				<h2><?php esc_html_e( 'Governing Law', 'appiappi' ); ?></h2>
				<?php if ( $incorporation_province ) : ?>
					<p>
						<?php
						printf(
							/* translators: %s: province or territory of incorporation */
							esc_html__( 'These Terms are governed by the laws of %s, Canada, without regard to conflict-of-law principles.', 'appiappi' ),
							'<strong>' . esc_html( $incorporation_province ) . '</strong>'
						);
						?>
					</p>
				<?php else : ?>
					<p><?php esc_html_e( 'These Terms are governed by the laws of Canada and the applicable province or territory of business operation, without regard to conflict-of-law principles.', 'appiappi' ); ?></p>
				<?php endif; ?>

				<h2><?php esc_html_e( 'Changes to These Terms', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'We may update these Terms from time to time. Updated Terms will be posted on this page with a revised "Last Updated" date.', 'appiappi' ); ?></p>
				<p><?php esc_html_e( 'Your continued use of the website or services after updated Terms are posted may constitute acceptance of the revised Terms, subject to applicable law.', 'appiappi' ); ?></p>

				<h2><?php esc_html_e( 'Contact Us', 'appiappi' ); ?></h2>
				<p><?php esc_html_e( 'If you have questions about these Terms of Service, please contact us:', 'appiappi' ); ?></p>
				<p class="legal-contact-block">
					<strong><?php echo esc_html( $display_name ); ?></strong><br>
					<?php esc_html_e( 'Website:', 'appiappi' ); ?> <code><?php echo esc_html( $site_domain ); ?></code><br>
					<?php if ( $general_email ) : ?>
						<?php esc_html_e( 'Email:', 'appiappi' ); ?> <a href="mailto:<?php echo esc_attr( $general_email ); ?>"><?php echo esc_html( $general_email ); ?></a><br>
					<?php endif; ?>
					<?php if ( $company_address ) : ?>
						<?php esc_html_e( 'Address:', 'appiappi' ); ?> <?php echo nl2br( esc_html( $company_address ) ); ?>
					<?php endif; ?>
				</p>
			</div>
		</div>
	</article>

	<?php get_template_part( 'template-parts/sections/final-cta' ); ?>
</main>

<?php
get_footer();
