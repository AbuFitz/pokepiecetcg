<?php
/**
 * Free standard shipping window.
 * Rule: if the customer has an earlier qualifying order whose window is still open, the "standard" shipping option costs nothing.
 * The window opens on the first order and is carried (not extended) by orders placed inside it, so a reorder never restarts the clock.
 * Customer identity: logged-in user ID, or billing email (case-insensitive) for guests.
 */
defined( 'ABSPATH' ) || exit;

const PPC_META_START = '_ppc_window_start';
const PPC_META_FREE  = '_ppc_window_free';

function ppc_fs_enabled() { return 'yes' === ppc_opt( 'fs_enabled', 'no' ); }
function ppc_fs_seconds() { return max( 1, (int) ppc_opt( 'fs_hours', 8 ) ) * HOUR_IN_SECONDS; }

/** @return array{user:int,email:string} */
function ppc_current_customer() {
	$c = array( 'user' => (int) get_current_user_id(), 'email' => '' );
	if ( function_exists( 'WC' ) && WC()->customer ) {
		$c['email'] = strtolower( trim( (string) WC()->customer->get_billing_email() ) );
		if ( ! $c['user'] ) { $c['user'] = (int) WC()->customer->get_id(); }
	}
	return apply_filters( 'ppc_current_customer', $c );
}

/** Start time of the customer's open window, or 0. $exclude_order_id ignores the order being created. */
function ppc_window_start( $user, $email, $exclude_order_id = 0, $now = null ) {
	$now = $now ?? time();
	$who = array_values( array_filter( array( $user ?: null, $email ?: null ) ) );
	if ( ! $who ) { return 0; }
	$orders = wc_get_orders( array(
		'limit' => 5, 'orderby' => 'date', 'order' => 'DESC', 'type' => 'shop_order',
		'status' => array( 'wc-processing', 'wc-completed', 'wc-on-hold' ),
		'customer' => $who,
		'date_created' => ( $now - ppc_fs_seconds() ) . '...' . $now,
		'exclude' => $exclude_order_id ? array( $exclude_order_id ) : array(),
	) );
	$best = 0;
	foreach ( $orders as $o ) {
		$start = (int) $o->get_meta( PPC_META_START ) ?: (int) $o->get_date_created()->getTimestamp();
		if ( $now - $start < ppc_fs_seconds() && $start > $best ) { $best = $start; }
	}
	return $best;
}

function ppc_window_open_now() {
	if ( ! ppc_fs_enabled() ) { return false; }
	$c = ppc_current_customer();
	return ppc_window_start( $c['user'], $c['email'] ) > 0;
}

// Make WooCommerce's shipping-rate cache aware of the offer, otherwise a cached paid rate could be served.
add_filter( 'woocommerce_cart_shipping_packages', function ( $packages ) {
	if ( ! ppc_fs_enabled() ) { return $packages; }
	$open = ppc_window_open_now() ? 'open' : 'closed';
	foreach ( $packages as $i => $p ) { $packages[ $i ]['ppc_window'] = $open; }
	return $packages;
} );

add_filter( 'woocommerce_package_rates', function ( $rates, $package ) {
	if ( ! ppc_fs_enabled() || ( $package['ppc_window'] ?? '' ) !== 'open' && ! ppc_window_open_now() ) { return $rates; }
	$kw = strtolower( trim( ppc_opt( 'fs_keyword', 'standard' ) ) ); if ( '' === $kw ) { return $rates; }
	foreach ( $rates as $id => $rate ) {
		if ( false === strpos( strtolower( $rate->get_label() ), $kw ) ) { continue; }
		$rate->set_cost( 0 ); $rate->set_taxes( array() );
		$rate->add_meta_data( 'ppc_free_window', '1' );
	}
	return $rates;
}, 20, 2 );

// Stamp the order with its window so a reorder inside the window does not restart the clock.
function ppc_stamp_order( $order ) {
	if ( ! $order instanceof WC_Order ) { $order = wc_get_order( $order ); }
	if ( ! $order || $order->get_meta( PPC_META_START ) ) { return; }
	$start = ppc_window_start( (int) $order->get_customer_id(), strtolower( (string) $order->get_billing_email() ), $order->get_id(), (int) $order->get_date_created()->getTimestamp() );
	$order->update_meta_data( PPC_META_START, $start ?: (int) $order->get_date_created()->getTimestamp() );
	if ( $start ) { $order->update_meta_data( PPC_META_FREE, '1' ); }
	$order->save();
}
add_action( 'woocommerce_checkout_order_created', 'ppc_stamp_order' );
add_action( 'woocommerce_store_api_checkout_order_processed', 'ppc_stamp_order' );

// Cart/checkout message (only when the offer is genuinely applied).
add_action( 'woocommerce_before_cart', 'ppc_notice' );
add_action( 'woocommerce_before_checkout_form', 'ppc_notice' );
function ppc_notice() {
	if ( 'yes' !== ppc_opt( 'fs_notice', 'yes' ) || ! ppc_window_open_now() ) { return; }
	wc_print_notice( sprintf( esc_html__( 'Free standard shipping is applied: you ordered within the last %d hours.', 'pokepiece-core' ), (int) ppc_opt( 'fs_hours', 8 ) ), 'notice' );
}
add_action( 'woocommerce_thankyou', function ( $order_id ) {
	if ( 'yes' !== ppc_opt( 'fs_thanks', 'no' ) || ! ppc_fs_enabled() ) { return; }
	$o = wc_get_order( $order_id ); if ( ! $o ) { return; }
	$start = (int) $o->get_meta( PPC_META_START ); $left = $start ? max( 0, $start + ppc_fs_seconds() - time() ) : 0;
	if ( $left > 0 ) { printf( '<p class="ppc-thanks">%s</p>', esc_html( sprintf( __( 'Order again within %d hours for free standard shipping.', 'pokepiece-core' ), (int) ceil( $left / HOUR_IN_SECONDS ) ) ) ); }
} );
