<?php
/** LOCAL ONLY (wp eval-file). Verifies the 8-hour free standard shipping rule against throwaway orders. Refuses non-localhost. */
if ( ! str_contains( home_url(), 'localhost' ) ) { WP_CLI::error( 'localhost only' ); }
update_option( 'ppc_settings', array( 'fs_enabled' => 'yes', 'fs_hours' => 8, 'fs_keyword' => 'standard', 'fs_notice' => 'yes', 'fs_thanks' => 'no' ) );
$fail = 0; $ok = function ( $name, $cond ) use ( &$fail ) { echo ( $cond ? 'PASS ' : 'FAIL ' ) . $name . "\n"; if ( ! $cond ) { $fail++; } };
$mk = function ( $email, $hours_ago, $status = 'processing' ) {
	$o = wc_create_order(); $o->set_billing_email( $email ); $o->set_date_created( time() - (int) ( $hours_ago * 3600 ) ); $o->set_status( $status ); $o->save(); ppc_stamp_order( $o ); return wc_get_order( $o->get_id() );
};
$rates = function ( $email ) {
	add_filter( 'ppc_current_customer', $f = fn() => array( 'user' => 0, 'email' => $email ) );
	$r = array( 'flat:1' => new WC_Shipping_Rate( 'flat:1', 'Standard shipping', 3.95, array( 0.79 ), 'flat_rate' ), 'flat:2' => new WC_Shipping_Rate( 'flat:2', 'Tracked 24', 5.95, array(), 'flat_rate' ) );
	$pkgs = apply_filters( 'woocommerce_cart_shipping_packages', array( array() ) ); $r = apply_filters( 'woocommerce_package_rates', $r, $pkgs[0] ); remove_filter( 'ppc_current_customer', $f ); return $r;
};
$e = 'a' . wp_rand( 1000, 9999 ) . '@example.invalid';
$r = $rates( $e ); $ok( 'no previous order: standard costs 3.95', (float) $r['flat:1']->get_cost() === 3.95 );
$a = $mk( $e, 2 ); $ok( 'first order opens a window (not itself free)', $a->get_meta( PPC_META_FREE ) === '' );
$r = $rates( $e ); $ok( 'reorder 2h after first: standard is free', (float) $r['flat:1']->get_cost() === 0.0 && $r['flat:1']->get_taxes() === array() );
$ok( 'other shipping methods are never changed', (float) $r['flat:2']->get_cost() === 5.95 );
$b = $mk( $e, 1 ); $ok( 'second order inside window is stamped free and inherits the first order start', $b->get_meta( PPC_META_FREE ) === '1' && (int) $b->get_meta( PPC_META_START ) === (int) $a->get_meta( PPC_META_START ) );
$e2 = 'b' . wp_rand( 1000, 9999 ) . '@example.invalid'; $mk( $e2, 9 ); $r = $rates( $e2 ); $ok( 'first order 9h ago: window closed, standard costs 3.95', (float) $r['flat:1']->get_cost() === 3.95 );
$e3 = 'c' . wp_rand( 1000, 9999 ) . '@example.invalid'; $x = $mk( $e3, 9 ); $y = $mk( $e3, 4 ); $r = $rates( $e3 );
$ok( 'order 5h after the first is inside its window (free), and the window does NOT extend: 9h after the first, standard costs 3.95 again', $y->get_meta( PPC_META_FREE ) === '1' && (float) $r['flat:1']->get_cost() === 3.95 );
$e4 = 'd' . wp_rand( 1000, 9999 ) . '@example.invalid'; $o1 = $mk( $e4, 7.5 ); $o2 = $mk( $e4, 0.5 ); $ok( 'order at 7.5h then 0.5h ago: second is inside (7h gap < 8h) and free', $o2->get_meta( PPC_META_FREE ) === '1' ); $r = $rates( $e4 ); $ok( 'window anchored at 7.5h ago closes in 30 min but is still open', (float) $r['flat:1']->get_cost() === 0.0 );
$e5 = 'e' . wp_rand( 1000, 9999 ) . '@example.invalid'; $mk( $e5, 1, 'cancelled' ); $mk( $e5, 1, 'failed' ); $mk( $e5, 1, 'refunded' ); $r = $rates( $e5 ); $ok( 'cancelled, failed and refunded orders do not open a window', (float) $r['flat:1']->get_cost() === 3.95 );
update_option( 'ppc_settings', array( 'fs_enabled' => 'no' ) ); $r = $rates( $e ); $ok( 'offer switched off: nothing is free', (float) $r['flat:1']->get_cost() === 3.95 );
foreach ( wc_get_orders( array( 'limit' => -1, 'billing_email' => '', 'type' => 'shop_order' ) ) as $o ) { if ( str_ends_with( (string) $o->get_billing_email(), '@example.invalid' ) ) { $o->delete( true ); } }
echo $fail ? "FAILED: $fail\n" : "ALL PASSED\n";
