<?php
/**
 * Shipping upgrade as an OPTION, not a listing. The existing WooCommerce service product (the "Special Delivery Shipping Upgrade", id in
 * `pokepiece_service_product_ids`) keeps its price, page, stock and checkout-notes workflow untouched. The theme only (1) hides it from catalogue
 * listings/search/related and (2) offers it as an add-on card in the cart drawer, cart page, product pages and shop pages. Adding it uses WooCommerce's
 * normal add-to-cart, so the order contains the same line item as before.
 */
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WooCommerce' ) ) { return; }

function pp_upgrade_product() {
	foreach ( pp_service_ids() as $id ) {
		$p = wc_get_product( $id );
		if ( $p && 'publish' === get_post_status( $id ) && $p->is_purchasable() && $p->is_in_stock() ) { return $p; }
	}
	return null;
}
function pp_upgrade_in_cart( $p ) {
	if ( ! WC()->cart ) { return false; }
	foreach ( WC()->cart->get_cart() as $i ) { if ( (int) $i['product_id'] === $p->get_id() ) { return true; } }
	return false;
}

/** Product page: a checkbox option inside the purchase form. Cart/drawer: one compact line. */
function pp_upgrade_option_pdp() {
	$p = pp_upgrade_product(); if ( ! $p ) { return ''; }
	return '<label class="pp-opt"><input type="checkbox" name="pp_upgrade" value="1"><span class="pp-opt__box" aria-hidden="true"></span><span class="pp-opt__txt"><strong>' . esc_html__( 'Add Special Delivery', 'pokepiece' ) . '</strong> <em>+' . wp_kses_post( $p->get_price_html() ) . '</em></span><a class="pp-opt__info" href="' . esc_url( $p->get_permalink() ) . '">' . esc_html__( 'Info', 'pokepiece' ) . '</a></label>';
}
function pp_upgrade_card( $ctx = 'cart' ) {
	$p = pp_upgrade_product(); if ( ! $p || pp_upgrade_in_cart( $p ) ) { return ''; }
	return '<div class="pp-upgrade-line pp-upgrade-line--' . esc_attr( $ctx ) . '"><span>' . esc_html__( 'Need it faster?', 'pokepiece' ) . '</span><a class="ajax_add_to_cart add_to_cart_button" href="' . esc_url( $p->add_to_cart_url() ) . '" data-product_id="' . (int) $p->get_id() . '" data-quantity="1" rel="nofollow">' . esc_html__( 'Add Special Delivery', 'pokepiece' ) . ' · ' . wp_kses_post( $p->get_price_html() ) . '</a></div>';
}

// Option ticked on the product page: add the existing service product alongside, through WooCommerce's own cart (same line item as before).
add_action( 'woocommerce_add_to_cart', function ( $key, $pid ) {
	static $busy = false;
	if ( $busy || empty( $_POST['pp_upgrade'] ) || pp_is_service( $pid ) ) { return; } // phpcs:ignore WordPress.Security.NonceVerification
	$p = pp_upgrade_product(); if ( ! $p || pp_upgrade_in_cart( $p ) ) { return; }
	$busy = true; WC()->cart->add_to_cart( $p->get_id(), 1 ); $busy = false;
}, 10, 2 );

// Not a catalogue product: gone from shop/category/search listings (page, price, stock and order flow unchanged).
add_action( 'woocommerce_product_query', function ( $q ) {
	$q->set( 'post__not_in', array_values( array_unique( array_merge( (array) $q->get( 'post__not_in' ), pp_service_ids() ) ) ) );
}, 20 );

// Placement: cart page only; product page option is injected in the purchase form.
add_action( 'woocommerce_after_cart_table', function () { echo pp_upgrade_card( 'cart' ); } ); // phpcs:ignore
