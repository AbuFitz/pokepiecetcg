<?php
/**
 * Read-only launch preflight. Reports what the shop REALLY has (gateways, shipping, pages, legacy snippets, tracking plugins, Special Delivery state) so
 * nothing about the live setup is guessed. Changes nothing. WP-CLI: `wp pokepiece preflight`; admin: WooCommerce > PokePiece audit.
 */
defined( 'ABSPATH' ) || exit;

function ppc_preflight_rows() {
	$r = array(); $add = function ( $check, $status, $detail ) use ( &$r ) { $r[] = array( 'check' => $check, 'status' => $status, 'detail' => $detail ); };
	global $wpdb;
	$add( 'Versions', 'info', 'WordPress ' . get_bloginfo( 'version' ) . ', WooCommerce ' . ( defined( 'WC_VERSION' ) ? WC_VERSION : '?' ) . ', PHP ' . PHP_VERSION );
	$hpos = class_exists( '\Automattic\WooCommerce\Utilities\OrderUtil' ) && \Automattic\WooCommerce\Utilities\OrderUtil::custom_orders_table_usage_is_enabled();
	$add( 'Order storage', 'info', $hpos ? 'High-performance order tables (HPOS) in use' : 'Classic post-based orders' );
	$th = wp_get_theme(); $add( 'Active theme', 'info', $th->get( 'Name' ) . ( $th->parent() ? ' (child of ' . $th->parent()->get( 'Name' ) . ')' : '' ) );

	$gw = array(); foreach ( WC()->payment_gateways()->payment_gateways() as $g ) { if ( 'yes' === $g->enabled ) { $gw[] = $g->get_title() . ' [' . $g->id . ']'; } }
	$add( 'Payment gateways enabled', $gw ? 'info' : 'warn', $gw ? implode( '; ', $gw ) . '. Test each in its own test mode on staging.' : 'None enabled' );

	$zones = WC_Shipping_Zones::get_zones(); $methods = array(); $has_std = false;
	foreach ( $zones as $z ) { foreach ( $z['shipping_methods'] as $m ) { $t = $m->get_title(); $methods[] = $z['zone_name'] . ': ' . $t . ' (' . $m->id . ')'; if ( false !== stripos( $t, ppc_opt( 'fs_keyword', 'standard' ) ) ) { $has_std = true; } } }
	$add( 'Shipping methods', $methods ? 'info' : 'warn', $methods ? implode( '; ', $methods ) : 'No shipping zones' );
	$add( 'Free-shipping rule target', $has_std ? 'ok' : 'warn', $has_std ? 'A method containing "' . ppc_opt( 'fs_keyword', 'standard' ) . '" exists, so the 8-hour rule has something to discount.' : 'No shipping method name contains "' . ppc_opt( 'fs_keyword', 'standard' ) . '": the 8-hour rule would never apply. Change the keyword or the method name.' );
	$add( 'Taxes', 'info', 'wc_calc_taxes = ' . get_option( 'woocommerce_calc_taxes', 'no' ) . '; prices ' . ( 'yes' === get_option( 'woocommerce_prices_include_tax' ) ? 'include' : 'exclude' ) . ' tax. Decide whether Special Delivery is taxable to match.' );

	foreach ( array( 'shop' => wc_get_page_id( 'shop' ), 'cart' => wc_get_page_id( 'cart' ), 'checkout' => wc_get_page_id( 'checkout' ), 'my account' => wc_get_page_id( 'myaccount' ), 'privacy policy' => (int) get_option( 'wp_page_for_privacy_policy' ) ?: (int) ( get_page_by_path( 'privacy-policy' )->ID ?? 0 ) ) as $n => $id ) {
		$ok = $id > 0 && 'publish' === get_post_status( $id ); $add( 'Page: ' . $n, $ok ? 'ok' : 'warn', $ok ? get_permalink( $id ) : 'missing or not published' );
	}
	foreach ( array( 'shipping-policy', 'refund-return-policy', 'terms-conditions', 'about', 'contact' ) as $slug ) { $pg = get_page_by_path( $slug ); $add( 'Page: ' . $slug, $pg && 'publish' === $pg->post_status ? 'ok' : 'warn', $pg ? get_permalink( $pg ) : 'not found (footer link will be hidden)' ); }
	$add( 'Permalinks', get_option( 'permalink_structure' ) ? 'ok' : 'warn', get_option( 'permalink_structure' ) ?: 'plain permalinks: product URLs would change' );

	$plugins = (array) get_option( 'active_plugins', array() ); $names = array_map( fn( $p ) => dirname( $p ) === '.' ? $p : dirname( $p ), $plugins );
	$snip = preg_grep( '/code-snippets/i', $names );
	if ( $snip ) {
		$tbl = $wpdb->prefix . 'snippets'; $list = array();
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $tbl ) ) === $tbl ) { foreach ( $wpdb->get_results( "SELECT name, active FROM `$tbl`" ) as $s ) { $list[] = $s->name . ( $s->active ? ' (active)' : ' (off)' ); } }
		$add( 'Code Snippets plugin', 'warn', 'Active. Snippets: ' . ( $list ? implode( '; ', $list ) : 'could not read' ) . '. Compare the shipping one with the 8-hour rule here, then switch it off when ours is on, never both.' );
	} else { $add( 'Code Snippets plugin', 'ok', 'Not active' ); }
	$trk = preg_grep( '/analytics|pixel|tracking|gtm|tag-manager|site-kit|sitekit|monsterinsights|facebook|tiktok|hotjar|clarity|cookie|consent|complianz|cookiebot/i', $names );
	$add( 'Tracking / cookie plugins', $trk ? 'warn' : 'ok', $trk ? implode( ', ', $trk ) . '. These set cookies of their own: list them in the cookie wording and either keep their consent tool or gate them with the PokePiece prompt, not both.' : 'None found' );

	$leg = ppc_sd_legacy_ids(); foreach ( $leg as $id ) { $st = get_post_status( $id ); $add( 'Old upgrade product #' . $id, 'publish' === $st && ppc_sd_active() ? 'warn' : 'info', $st ? 'status ' . $st . ( 'publish' === $st && ppc_sd_active() ? '. Set to Draft/Private now the option is on.' : '' ) : 'not found' ); }
	$add( 'Special Delivery option', ppc_sd_active() ? 'ok' : 'info', ppc_sd_active() ? 'On, ' . html_entity_decode( wp_strip_all_tags( wc_price( ppc_sd_price() ) ) ) . ( 'yes' === ppc_opt( 'sd_taxable', 'no' ) ? ', taxable' : ', not taxed' ) : 'Off (set price and tick Enabled in WooCommerce > PokePiece rules)' );
	$add( 'Free-shipping offer', 'yes' === ppc_opt( 'fs_enabled', 'no' ) ? 'ok' : 'info', 'yes' === ppc_opt( 'fs_enabled', 'no' ) ? 'On, ' . (int) ppc_opt( 'fs_hours', 8 ) . ' hours' : 'Off (keep off until the old snippet is off)' );
	$add( 'Cookie prompt', function_exists( 'pp_cookie_prompt_on' ) ? ( pp_cookie_prompt_on() ? 'ok' : 'warn' ) : 'info', function_exists( 'pp_cookie_prompt_on' ) ? ( pp_cookie_prompt_on() ? 'On (theme)' : 'Off in Settings > PokePiece' ) : 'Theme not active' );
	$add( 'Email', 'info', 'Order emails are sent by WordPress mail. Send a test order email on staging and check it arrives (spam too); IONOS may need SMTP.' );
	return $r;
}

add_action( 'admin_menu', function () {
	add_submenu_page( 'woocommerce', 'PokePiece launch checks', 'PokePiece launch checks', 'manage_woocommerce', 'ppc-preflight', function () {
		if ( ! current_user_can( 'manage_woocommerce' ) ) { return; }
		echo '<div class="wrap"><h1>Launch checks (read-only)</h1><p>What this shop actually has today. Nothing here changes anything.</p><table class="widefat striped"><thead><tr><th>Check</th><th>Status</th><th>Detail</th></tr></thead><tbody>';
		foreach ( ppc_preflight_rows() as $x ) { printf( '<tr><td>%s</td><td><strong style="color:%s">%s</strong></td><td>%s</td></tr>', esc_html( $x['check'] ), 'warn' === $x['status'] ? '#b32d2e' : ( 'ok' === $x['status'] ? '#1f6b45' : '#555' ), esc_html( strtoupper( $x['status'] ) ), esc_html( $x['detail'] ) ); }
		echo '</tbody></table></div>';
	} );
} );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'pokepiece preflight', function ( $a, $assoc ) { WP_CLI\Utils\format_items( $assoc['format'] ?? 'table', ppc_preflight_rows(), array( 'check', 'status', 'detail' ) ); } );
}
