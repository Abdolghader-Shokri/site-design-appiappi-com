<?php
/**
 * Lightweight SEO foundation — not a full SEO plugin, and deliberately
 * skipped entirely if one is active (Yoast/Rank Math/etc. already do
 * all of this, usually better; we'd just be fighting over the same
 * meta tags). Covers: meta description, Open Graph + Twitter Card,
 * Organization/LocalBusiness JSON-LD, canonical (WP core already
 * outputs this), and a simple breadcrumbs helper.
 */

defined( 'ABSPATH' ) || exit;

function appiappi_has_seo_plugin() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || class_exists( 'All_in_One_SEO_Pack' );
}

function appiappi_seo_get_description() {
	if ( is_singular() ) {
		$excerpt = get_the_excerpt();
		if ( $excerpt ) {
			return wp_strip_all_tags( $excerpt );
		}
	}
	return appiappi_get_setting( 'seo_description' );
}

function appiappi_seo_get_title() {
	if ( is_singular() ) {
		return wp_strip_all_tags( get_the_title() ) . ' – ' . get_bloginfo( 'name' );
	}
	return appiappi_get_setting( 'seo_title' ) ?: get_bloginfo( 'name' ) . ' – ' . get_bloginfo( 'description' );
}

function appiappi_output_meta_tags() {
	if ( appiappi_has_seo_plugin() ) {
		return;
	}

	$description = appiappi_seo_get_description();
	$title       = appiappi_seo_get_title();
	$image       = '';
	if ( is_singular() && has_post_thumbnail() ) {
		$image = get_the_post_thumbnail_url( get_the_ID(), 'appiappi-hero' );
	}

	if ( $description ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
	}

	printf( '<meta property="og:type" content="%s">' . "\n", esc_attr( is_singular( 'post' ) ? 'article' : 'website' ) );
	printf( '<meta property="og:title" content="%s">' . "\n", esc_attr( $title ) );
	if ( $description ) {
		printf( '<meta property="og:description" content="%s">' . "\n", esc_attr( $description ) );
	}
	$current_url = ( is_singular() && wp_get_canonical_url() ) ? wp_get_canonical_url() : home_url( add_query_arg( null, null ) );
	printf( '<meta property="og:url" content="%s">' . "\n", esc_url( $current_url ) );
	printf( '<meta property="og:site_name" content="%s">' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	if ( $image ) {
		printf( '<meta property="og:image" content="%s">' . "\n", esc_url( $image ) );
	}

	printf( '<meta name="twitter:card" content="%s">' . "\n", esc_attr( $image ? 'summary_large_image' : 'summary' ) );
	printf( '<meta name="twitter:title" content="%s">' . "\n", esc_attr( $title ) );
	if ( $description ) {
		printf( '<meta name="twitter:description" content="%s">' . "\n", esc_attr( $description ) );
	}
}
add_action( 'wp_head', 'appiappi_output_meta_tags', 2 );

/**
 * Organization/LocalBusiness JSON-LD schema, built from the same
 * Customizer contact fields the header/footer already use — no
 * duplicate data entry for the site owner.
 */
function appiappi_output_schema() {
	if ( appiappi_has_seo_plugin() || is_admin() ) {
		return;
	}

	$phone   = get_theme_mod( 'appiappi_phone' );
	$email   = get_theme_mod( 'appiappi_email' );
	$address = get_theme_mod( 'appiappi_address' );

	$schema = array(
		'@context' => 'https://schema.org',
		'@type'    => 'LocalBusiness',
		'name'     => get_bloginfo( 'name' ),
		'url'      => home_url( '/' ),
	);

	if ( has_custom_logo() ) {
		$logo_id  = get_theme_mod( 'custom_logo' );
		$logo_src = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
		if ( $logo_src ) {
			$schema['logo'] = $logo_src;
		}
	}
	if ( $phone ) {
		$schema['telephone'] = $phone;
	}
	if ( $email ) {
		$schema['email'] = $email;
	}
	if ( $address ) {
		$schema['address'] = array(
			'@type'           => 'PostalAddress',
			'addressCountry'  => 'CA',
			'streetAddress'   => $address,
		);
	}

	$social_fields = array( 'appiappi_social_facebook', 'appiappi_social_linkedin', 'appiappi_social_instagram', 'appiappi_social_youtube' );
	$same_as       = array();
	foreach ( $social_fields as $field ) {
		$url = get_theme_mod( $field );
		if ( $url ) {
			$same_as[] = $url;
		}
	}
	if ( $same_as ) {
		$schema['sameAs'] = $same_as;
	}

	echo '<script type="application/ld+json">' . wp_json_encode( $schema ) . '</script>' . "\n";
}
add_action( 'wp_head', 'appiappi_output_schema', 3 );

/**
 * Analytics/tracking output — every script only loads if the admin
 * actually configured it (Settings > Appiappi Settings), so there's
 * zero third-party request overhead by default.
 */
function appiappi_output_tracking_head() {
	$ga  = appiappi_get_setting( 'ga_measurement_id' );
	$gsc = appiappi_get_setting( 'gsc_verification' );
	$fb  = appiappi_get_setting( 'meta_pixel_id' );

	if ( $gsc ) {
		printf( '<meta name="google-site-verification" content="%s">' . "\n", esc_attr( $gsc ) );
	}

	if ( $ga ) {
		printf( '<script async src="https://www.googletagmanager.com/gtag/js?id=%s"></script>' . "\n", esc_attr( $ga ) );
		printf(
			"<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','%s');</script>\n",
			esc_js( $ga )
		);
	}

	if ( $fb ) {
		printf(
			"<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','%s');fbq('track','PageView');</script>\n",
			esc_js( $fb )
		);
	}

	$header_scripts = appiappi_get_setting( 'header_scripts' );
	if ( $header_scripts ) {
		// phpcs:ignore -- admin-authored, deliberately unescaped, see settings-page.php.
		echo $header_scripts . "\n";
	}
}
add_action( 'wp_head', 'appiappi_output_tracking_head', 20 );

function appiappi_output_tracking_footer() {
	$footer_scripts = appiappi_get_setting( 'footer_scripts' );
	if ( $footer_scripts ) {
		// phpcs:ignore -- admin-authored, deliberately unescaped, see settings-page.php.
		echo $footer_scripts . "\n";
	}
}
add_action( 'wp_footer', 'appiappi_output_tracking_footer', 20 );

/**
 * Simple breadcrumbs for non-home pages. Call from a page template with
 * `appiappi_breadcrumbs()`. Not wired in automatically everywhere yet —
 * add it to a template's `<main>` opening where useful.
 */
function appiappi_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}
	$items = array( array( 'label' => __( 'Home', 'appiappi' ), 'url' => home_url( '/' ) ) );

	if ( is_singular( 'post' ) || is_home() ) {
		$items[] = array( 'label' => __( 'Blog', 'appiappi' ), 'url' => get_permalink( get_option( 'page_for_posts' ) ) );
		if ( is_singular( 'post' ) ) {
			$items[] = array( 'label' => get_the_title(), 'url' => '' );
		}
	} elseif ( is_singular( 'appiappi_template' ) ) {
		$items[] = array( 'label' => __( 'Website Designs', 'appiappi' ), 'url' => get_post_type_archive_link( 'appiappi_template' ) );
		$items[] = array( 'label' => get_the_title(), 'url' => '' );
	} elseif ( is_post_type_archive( 'appiappi_template' ) ) {
		$items[] = array( 'label' => __( 'Website Designs', 'appiappi' ), 'url' => '' );
	} elseif ( is_singular( 'page' ) ) {
		$items[] = array( 'label' => get_the_title(), 'url' => '' );
	} elseif ( is_category() || is_tag() || is_archive() ) {
		$items[] = array( 'label' => get_the_archive_title(), 'url' => '' );
	}

	if ( count( $items ) < 2 ) {
		return;
	}
	?>
	<nav class="breadcrumbs container" aria-label="<?php esc_attr_e( 'Breadcrumb', 'appiappi' ); ?>">
		<?php foreach ( $items as $index => $item ) : ?>
			<?php if ( $index > 0 ) : ?><span aria-hidden="true"> / </span><?php endif; ?>
			<?php if ( $item['url'] ) : ?>
				<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
			<?php else : ?>
				<span aria-current="page"><?php echo esc_html( $item['label'] ); ?></span>
			<?php endif; ?>
		<?php endforeach; ?>
	</nav>
	<?php
}
