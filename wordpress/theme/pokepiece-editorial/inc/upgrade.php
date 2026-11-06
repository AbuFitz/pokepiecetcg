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

/** @param string $ctx drawer|pdp|cart|listing */
function pp_upgrade_card( $ctx = 'pdp' ) {
	$p = pp_upgrade_product(); if ( ! $p ) { return ''; }
	$listing = ( 'listing' === $ctx );
	if ( ! $listing && pp_upgrade_in_cart( $p ) ) { return ''; }
	$img = wp_get_attachment_image_url( $p->get_image_id(), 'thumbnail' ) ?: '';
	$h = '<div class="pp-upgrade pp-upgrade--' . esc_attr( $ctx ) . '" data-pid="' . (int) $p->get_id() . '" data-name="' . esc_attr( $p->get_name() ) . '" data-price="' . esc_attr( (float) $p->get_price() ) . '" data-img="' . esc_url( $img ) . '" data-url="' . esc_url( $p->get_permalink() ) . '">';
	if ( $img ) { $h .= '<span class="pp-upgrade__img"><img src="' . esc_url( $img ) . '" alt="" width="64" height="64" loading="lazy"></span>'; }
	$h .= '<div class="pp-upgrade__txt"><strong>' . esc_html__( 'Upgrade your postage', 'pokepiece' ) . '</strong><span>' . esc_html__( 'Add Special Delivery to your order. Put your TikTok username or order reference in the order notes at checkout.', 'pokepiece' ) . '</span></div>';
	if ( $listing ) {
		$h .= '<a class="pp-btn pp-btn--ghost" href="' . esc_url( $p->get_permalink() ) . '">' . esc_html__( 'Details', 'pokepiece' ) . ' · ' . wp_kses_post( $p->get_price_html() ) . '</a>';
	} else {
		$h .= '<a class="pp-btn pp-btn--primary ajax_add_to_cart add_to_cart_button" href="' . esc_url( $p->add_to_cart_url() ) . '" data-product_id="' . (int) $p->get_id() . '" data-quantity="1" rel="nofollow">' . esc_html__( 'Add', 'pokepiece' ) . ' · ' . wp_kses_post( $p->get_price_html() ) . '</a>';
	}
	return $h . '</div>';
}

// Not a catalogue product: gone from shop/category/search listings (page, price, stock and order flow unchanged).
add_action( 'woocommerce_product_query', function ( $q ) {
	$q->set( 'post__not_in', array_values( array_unique( array_merge( (array) $q->get( 'post__not_in' ), pp_service_ids() ) ) ) );
}, 20 );

// Promo placements.
add_action( 'woocommerce_single_product_summary', function () {
	global $product; if ( $product && ! pp_is_service( $product->get_id() ) && $product->is_in_stock() ) { echo pp_upgrade_card( 'pdp' ); } // phpcs:ignore
}, 36 );
add_action( 'woocommerce_after_cart_table', function () { echo pp_upgrade_card( 'cart' ); } ); // phpcs:ignore
add_action( 'woocommerce_after_shop_loop', function () { echo '<div class="pp-upgrade-wrap">' . pp_upgrade_card( 'listing' ) . '</div>'; }, 15 ); // phpcs:ignore
