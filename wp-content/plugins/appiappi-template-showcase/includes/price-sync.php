<?php
/**
 * Keeps each Website Design's price and rating in sync with its real
 * Envato Market (ThemeForest, etc.) listing, via the official Envato
 * API — never by scraping/browsing the listing page, which is behind
 * Cloudflare bot-protection and cannot (and should not) be automated
 * around. Requires a free Envato Personal Token from
 * https://build.envato.com/create-token/ (default scopes are enough —
 * this only reads the public catalogue).
 *
 * Runs two ways:
 * - Manually, via the "Run Sync Now" button on Website Designs → Price
 *   & Rating Sync (checks every design with a real Details Page URL,
 *   in one pass — fine at today's scale).
 * - Automatically, every 15 minutes via WP-Cron, processing a small
 *   batch (APPIAPPI_SHOWCASE_SYNC_BATCH_SIZE) starting from a saved
 *   cursor that advances (and wraps around) each run — so a catalogue
 *   of hundreds or thousands of designs gets checked continuously
 *   without ever running thousands of API calls in one PHP request.
 */

defined( 'ABSPATH' ) || exit;

define( 'APPIAPPI_SHOWCASE_SYNC_BATCH_SIZE', 50 );

/**
 * Envato Market domains this plugin knows how to sync against. Website
 * Designs realistically only ever use themeforest.net, but the other
 * Envato marketplaces share the exact same API/URL shape.
 */
function appiappi_showcase_envato_domains() {
	return array( 'themeforest.net', 'codecanyon.net', 'graphicriver.net', 'videohive.net', 'audiojungle.net', 'photodune.net', '3docean.net' );
}

/**
 * Pulls the numeric Envato item ID out of a Details Page URL, e.g.
 * https://themeforest.net/item/some-theme-name/61829280 → "61829280".
 * Returns '' if the URL isn't a real link or isn't an Envato domain.
 */
function appiappi_showcase_extract_envato_item_id( $url ) {
	$url = trim( (string) $url );
	if ( ! $url || '#' === $url ) {
		return '';
	}

	$host = wp_parse_url( $url, PHP_URL_HOST );
	if ( ! $host ) {
		return '';
	}
	$host = preg_replace( '/^www\./', '', strtolower( $host ) );

	if ( ! in_array( $host, appiappi_showcase_envato_domains(), true ) ) {
		return '';
	}

	$path = wp_parse_url( $url, PHP_URL_PATH );
	if ( ! $path ) {
		return '';
	}
	$segments = array_values( array_filter( explode( '/', $path ) ) );
	$last     = end( $segments );

	return ( $last && ctype_digit( $last ) ) ? $last : '';
}

/**
 * Calls the official Envato API for one catalogue item. Returns the
 * decoded item array, or a WP_Error (never triggers any browser
 * navigation or scraping — this is the documented, ToS-compliant way
 * to read public listing data).
 */
function appiappi_showcase_fetch_envato_item( $item_id, $token ) {
	$response = wp_remote_get( 'https://api.envato.com/v3/market/catalog/item?id=' . rawurlencode( $item_id ), array(
		'headers' => array(
			'Authorization' => 'Bearer ' . $token,
			'User-Agent'    => 'Appiappi Template Showcase (WordPress plugin)',
		),
		'timeout' => 15,
	) );

	if ( is_wp_error( $response ) ) {
		return $response;
	}

	$code = wp_remote_retrieve_response_code( $response );
	$body = wp_remote_retrieve_body( $response );

	if ( 200 !== $code ) {
		$message = $body ? wp_strip_all_tags( $body ) : '';
		return new WP_Error( 'appiappi_envato_http_' . $code, sprintf(
			/* translators: 1: HTTP status code, 2: response body/message */
			__( 'Envato API returned HTTP %1$d: %2$s', 'appiappi-template-showcase' ),
			$code,
			$message ? mb_substr( $message, 0, 200 ) : __( '(no message)', 'appiappi-template-showcase' )
		) );
	}

	$data = json_decode( $body, true );
	if ( ! is_array( $data ) ) {
		return new WP_Error( 'appiappi_envato_bad_json', __( 'Envato API returned an unreadable response.', 'appiappi-template-showcase' ) );
	}

	return $data;
}

/**
 * Extracts { price, rating, rating_count } from a raw Envato API item
 * response. Price formatting matches the plain "$NN" style already
 * used in this plugin (no decimals unless the price actually has
 * cents). NOTE: Envato's catalogue schema has shifted over time —
 * if `price_cents` is ever missing from real responses, this is the
 * one place to adjust once we see the actual field the API returns.
 */
function appiappi_showcase_parse_envato_item( array $data ) {
	$result = array( 'price' => null, 'rating' => null, 'rating_count' => null );

	// Confirmed 2026-09-06 against a real API response: `rating` is a
	// flat float and `rating_count` a separate top-level int — not the
	// nested { rating: { rating, count } } object originally guessed
	// from the docs. See DEVELOPMENT_LOG.md.
	if ( isset( $data['rating'] ) && is_numeric( $data['rating'] ) ) {
		$result['rating'] = round( (float) $data['rating'], 1 );
	}
	if ( isset( $data['rating_count'] ) && is_numeric( $data['rating_count'] ) ) {
		$result['rating_count'] = (int) $data['rating_count'];
	}

	if ( isset( $data['price_cents'] ) && is_numeric( $data['price_cents'] ) ) {
		$cents   = (int) $data['price_cents'];
		$dollars = $cents / 100;
		$result['price'] = '$' . number_format( $dollars, ( 0 === $cents % 100 ) ? 0 : 2 );
	}

	return $result;
}

/**
 * Syncs one appiappi_template post against its Envato listing. Returns
 * a result row (always includes 'name' + 'status') for the log —
 * status is one of: updated / unchanged / skipped / error.
 */
function appiappi_showcase_sync_one_item( $post_id, $token ) {
	$name        = get_the_title( $post_id );
	$details_url = get_post_meta( $post_id, '_appiappi_template_details_url', true );
	$item_id     = appiappi_showcase_extract_envato_item_id( $details_url );

	if ( ! $item_id ) {
		return array( 'name' => $name, 'status' => 'skipped', 'message' => __( 'No Envato Details Page URL set.', 'appiappi-template-showcase' ) );
	}

	$data = appiappi_showcase_fetch_envato_item( $item_id, $token );
	if ( is_wp_error( $data ) ) {
		return array( 'name' => $name, 'status' => 'error', 'message' => $data->get_error_message() );
	}

	$parsed = appiappi_showcase_parse_envato_item( $data );
	if ( null === $parsed['price'] && null === $parsed['rating'] ) {
		return array( 'name' => $name, 'status' => 'error', 'message' => __( 'Envato response did not include a recognisable price or rating.', 'appiappi-template-showcase' ) );
	}

	$old_price        = get_post_meta( $post_id, '_appiappi_template_price', true );
	$old_rating       = get_post_meta( $post_id, '_appiappi_template_rating', true );
	$old_rating_count = get_post_meta( $post_id, '_appiappi_template_rating_count', true );

	$changes = array();

	if ( null !== $parsed['price'] && $parsed['price'] !== $old_price ) {
		update_post_meta( $post_id, '_appiappi_template_price', $parsed['price'] );
		update_post_meta( $post_id, '_appiappi_template_price_value', appiappi_showcase_parse_price_value( $parsed['price'] ) );
		$changes[] = sprintf( 'price %s → %s', $old_price ?: '—', $parsed['price'] );
	}
	if ( null !== $parsed['rating'] && (string) $parsed['rating'] !== (string) $old_rating ) {
		update_post_meta( $post_id, '_appiappi_template_rating', $parsed['rating'] );
		$changes[] = sprintf( 'rating %s → %s', $old_rating ?: '—', $parsed['rating'] );
	}
	if ( null !== $parsed['rating_count'] && (string) $parsed['rating_count'] !== (string) $old_rating_count ) {
		update_post_meta( $post_id, '_appiappi_template_rating_count', $parsed['rating_count'] );
		$changes[] = sprintf( 'rating count %s → %s', $old_rating_count ?: '—', $parsed['rating_count'] );
	}

	if ( $changes ) {
		return array( 'name' => $name, 'status' => 'updated', 'message' => implode( '; ', $changes ) );
	}
	return array( 'name' => $name, 'status' => 'unchanged', 'message' => __( 'Already matches the Envato listing.', 'appiappi-template-showcase' ) );
}

/**
 * Runs the sync. $batch_size = 0 (default) checks every design with a
 * real Details Page URL in one pass — used by "Run Sync Now". A
 * positive $batch_size checks only that many, starting from and
 * advancing a saved cursor (wrapping back to the start once it
 * reaches the end) — used by the recurring cron job so a large
 * catalogue never runs thousands of API calls in one request.
 */
function appiappi_showcase_sync_prices( $batch_size = 0 ) {
	$token = trim( (string) get_option( 'appiappi_showcase_envato_token', '' ) );
	if ( ! $token ) {
		return array( 'ran' => false, 'reason' => __( 'No Envato Personal Token configured.', 'appiappi-template-showcase' ) );
	}

	$all_ids = get_posts( array(
		'post_type'      => 'appiappi_template',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'orderby'        => 'ID',
		'order'          => 'ASC',
	) );

	if ( ! $all_ids ) {
		return array( 'ran' => true, 'items' => array() );
	}

	if ( $batch_size > 0 && count( $all_ids ) > $batch_size ) {
		$cursor       = (int) get_option( 'appiappi_showcase_sync_cursor', 0 );
		$start        = $cursor % count( $all_ids );
		$ids_to_check = array();
		for ( $i = 0; $i < $batch_size; $i++ ) {
			$ids_to_check[] = $all_ids[ ( $start + $i ) % count( $all_ids ) ];
		}
		update_option( 'appiappi_showcase_sync_cursor', $start + $batch_size, false );
	} else {
		$ids_to_check = $all_ids;
	}

	$results = array();
	foreach ( $ids_to_check as $post_id ) {
		$results[] = appiappi_showcase_sync_one_item( $post_id, $token );
		// A second of courtesy pacing between requests — this is a
		// background/manual sync, never a page load, so it's fine to
		// spend a little real time being a good API citizen.
		if ( count( $ids_to_check ) > 1 ) {
			sleep( 1 );
		}
	}

	update_option( 'appiappi_showcase_sync_log', array(
		'timestamp' => current_time( 'mysql' ),
		'items'     => $results,
	), false );

	return array( 'ran' => true, 'items' => $results );
}

/**
 * Custom 15-minute cron interval — daily would be too slow to cover a
 * large catalogue in small batches; every 15 minutes gets a
 * multi-thousand-item catalogue through a full cycle in under a day
 * without ever making a request from a normal page load.
 */
function appiappi_showcase_cron_schedules( $schedules ) {
	$schedules['appiappi_15min'] = array(
		'interval' => 15 * MINUTE_IN_SECONDS,
		'display'  => __( 'Every 15 Minutes (Appiappi price sync)', 'appiappi-template-showcase' ),
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'appiappi_showcase_cron_schedules' );

add_action( 'appiappi_showcase_price_sync_cron', function () {
	appiappi_showcase_sync_prices( APPIAPPI_SHOWCASE_SYNC_BATCH_SIZE );
} );

function appiappi_showcase_activate_price_sync() {
	if ( ! wp_next_scheduled( 'appiappi_showcase_price_sync_cron' ) ) {
		wp_schedule_event( time(), 'appiappi_15min', 'appiappi_showcase_price_sync_cron' );
	}
}
register_activation_hook( APPIAPPI_SHOWCASE_DIR . 'appiappi-template-showcase.php', 'appiappi_showcase_activate_price_sync' );

function appiappi_showcase_deactivate_price_sync() {
	wp_clear_scheduled_hook( 'appiappi_showcase_price_sync_cron' );
}
register_deactivation_hook( APPIAPPI_SHOWCASE_DIR . 'appiappi-template-showcase.php', 'appiappi_showcase_deactivate_price_sync' );

// The cron event was introduced after this plugin's original activation
// (activation hooks only fire on (re)activation) — schedule it on the
// next admin page load if it's somehow still missing.
add_action( 'admin_init', function () {
	if ( ! wp_next_scheduled( 'appiappi_showcase_price_sync_cron' ) ) {
		wp_schedule_event( time(), 'appiappi_15min', 'appiappi_showcase_price_sync_cron' );
	}
} );

/**
 * Admin screen: the Envato Personal Token, a "Run Sync Now" button,
 * and the most recent sync's per-item log.
 */
function appiappi_showcase_price_sync_menu() {
	add_submenu_page(
		'edit.php?post_type=appiappi_template',
		__( 'Price & Rating Sync', 'appiappi-template-showcase' ),
		__( 'Price & Rating Sync', 'appiappi-template-showcase' ),
		'manage_options',
		'appiappi-showcase-price-sync',
		'appiappi_showcase_render_price_sync_page'
	);
}
add_action( 'admin_menu', 'appiappi_showcase_price_sync_menu' );

function appiappi_showcase_price_sync_register() {
	register_setting( 'appiappi_showcase_price_sync_group', 'appiappi_showcase_envato_token', array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => '',
	) );
}
add_action( 'admin_init', 'appiappi_showcase_price_sync_register' );

function appiappi_showcase_render_price_sync_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$run_result = null;
	if ( isset( $_POST['appiappi_showcase_run_sync'] ) && check_admin_referer( 'appiappi_showcase_run_sync_action', 'appiappi_showcase_run_sync_nonce' ) ) {
		$run_result = appiappi_showcase_sync_prices( 0 );
	}

	$token = get_option( 'appiappi_showcase_envato_token', '' );
	$log   = get_option( 'appiappi_showcase_sync_log', array() );
	$next_cron = wp_next_scheduled( 'appiappi_showcase_price_sync_cron' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Website Designs — Price & Rating Sync', 'appiappi-template-showcase' ); ?></h1>
		<p><?php esc_html_e( 'Keeps each design\'s price and rating matched to its real Envato Market (ThemeForest) listing via the official Envato API — never by opening the listing page, which is blocked by that site\'s own bot protection.', 'appiappi-template-showcase' ); ?></p>

		<?php if ( $run_result ) : ?>
			<?php if ( empty( $run_result['ran'] ) ) : ?>
				<div class="notice notice-error"><p><?php echo esc_html( $run_result['reason'] ); ?></p></div>
			<?php else : ?>
				<div class="notice notice-success"><p><?php esc_html_e( 'Sync finished — see the results below.', 'appiappi-template-showcase' ); ?></p></div>
			<?php endif; ?>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'appiappi_showcase_price_sync_group' ); ?>
			<table class="form-table">
				<tr>
					<th><label for="appiappi_showcase_envato_token"><?php esc_html_e( 'Envato Personal Token', 'appiappi-template-showcase' ); ?></label></th>
					<td>
						<input type="password" id="appiappi_showcase_envato_token" name="appiappi_showcase_envato_token" value="<?php echo esc_attr( $token ); ?>" class="regular-text" autocomplete="off">
						<p class="description">
							<?php
							printf(
								/* translators: %s: link to build.envato.com */
								wp_kses_post( __( 'Create a free token at %s (default scopes are enough — this only reads public catalogue data).', 'appiappi-template-showcase' ) ),
								'<a href="https://build.envato.com/create-token/" target="_blank" rel="noopener noreferrer">build.envato.com</a>'
							);
							?>
						</p>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Save Token', 'appiappi-template-showcase' ) ); ?>
		</form>

		<hr>

		<h2><?php esc_html_e( 'Manual Sync', 'appiappi-template-showcase' ); ?></h2>
		<p>
			<?php
			if ( $next_cron ) {
				printf(
					/* translators: %s: human-readable time until next run */
					esc_html__( 'Runs automatically every 15 minutes in the background (next run in %s). You can also run it immediately below.', 'appiappi-template-showcase' ),
					esc_html( human_time_diff( time(), $next_cron ) )
				);
			} else {
				esc_html_e( 'The automatic background sync is not currently scheduled — it will be as soon as this page loads again.', 'appiappi-template-showcase' );
			}
			?>
		</p>
		<form method="post" action="">
			<?php wp_nonce_field( 'appiappi_showcase_run_sync_action', 'appiappi_showcase_run_sync_nonce' ); ?>
			<?php submit_button( __( 'Run Sync Now', 'appiappi-template-showcase' ), 'secondary', 'appiappi_showcase_run_sync', false ); ?>
		</p>

		<?php if ( ! empty( $log['items'] ) ) : ?>
			<h2><?php esc_html_e( 'Last Sync Results', 'appiappi-template-showcase' ); ?></h2>
			<p class="description">
				<?php
				printf(
					/* translators: %s: date/time of last sync */
					esc_html__( 'Last run: %s', 'appiappi-template-showcase' ),
					esc_html( $log['timestamp'] )
				);
				?>
			</p>
			<table class="widefat striped">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Design', 'appiappi-template-showcase' ); ?></th>
						<th><?php esc_html_e( 'Status', 'appiappi-template-showcase' ); ?></th>
						<th><?php esc_html_e( 'Details', 'appiappi-template-showcase' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $log['items'] as $row ) : ?>
						<tr>
							<td><?php echo esc_html( $row['name'] ); ?></td>
							<td><?php echo esc_html( ucfirst( $row['status'] ) ); ?></td>
							<td><?php echo esc_html( $row['message'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
	</div>
	<?php
}
