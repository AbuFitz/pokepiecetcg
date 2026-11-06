<?php
/** Cart drawer: renders the authoritative Woo cart. Quantity/remove call WC()->cart server-side. Totals are Woo's. */
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WooCommerce' ) ) { return; }

function pp_cart_count_html() {
	$n = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	return '<span class="pp-cart-count" data-count="' . (int) $n . '"' . ( $n ? '' : ' hidden' ) . '>' . (int) $n . '</span>';
}

function pp_cart_body_html() {
	ob_start();
	$cart = WC()->cart;
	echo '<div class="pp-drawer__body" data-pp-cart-body>';
	if ( ! $cart || $cart->is_empty() ) {
		echo '<div class="pp-empty"><p><strong>' . esc_html__( 'Your cart is empty.', 'pokepiece' ) . '</strong></p><p>' . esc_html__( 'Browse the range to find a product.', 'pokepiece' ) . '</p><a class="pp-btn pp-btn--primary" href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'Shop all products', 'pokepiece' ) . '</a></div>';
	} else {
		echo '<ul class="pp-lines">';
		foreach ( $cart->get_cart() as $key => $item ) {
			$p = $item['data']; if ( ! $p ) { continue; }
			$max = $p->get_max_purchase_quantity();
			echo '<li class="pp-line" data-key="' . esc_attr( $key ) . '"><a class="pp-line__img" href="' . esc_url( $p->get_permalink() ) . '">' . $p->get_image( 'thumbnail' ) . '</a><div class="pp-line__main"><a class="pp-line__name" href="' . esc_url( $p->get_permalink() ) . '">' . esc_html( $p->get_name() ) . '</a>';
			echo wc_get_formatted_cart_item_data( $item );
			$mode = pp_product_mode( $p ); if ( $mode ) { echo '<span class="pp-line__mode">' . esc_html( pp_modes()[ $mode ]['label'] ) . '</span>'; }
			echo '<div class="pp-line__row"><div class="pp-qty" role="group" aria-label="' . esc_attr( sprintf( __( 'Quantity for %s', 'pokepiece' ), $p->get_name() ) ) . '"><button type="button" data-pp-qty="-1" aria-label="' . esc_attr__( 'Decrease', 'pokepiece' ) . '">−</button><output>' . (int) $item['quantity'] . '</output><button type="button" data-pp-qty="1" aria-label="' . esc_attr__( 'Increase', 'pokepiece' ) . '"' . ( $max > 0 && $item['quantity'] >= $max ? ' disabled' : '' ) . '>+</button></div><span class="pp-line__price">' . wp_kses_post( $cart->get_product_subtotal( $p, $item['quantity'] ) ) . '</span></div></div><button type="button" class="pp-line__remove" data-pp-remove aria-label="' . esc_attr( sprintf( __( 'Remove %s', 'pokepiece' ), $p->get_name() ) ) . '">' . pp_icon( 'close' ) . '</button></li>';
		}
		echo '</ul>';
	}
	echo '</div>';
	return ob_get_clean();
}

function pp_cart_foot_html() {
	ob_start();
	$cart = WC()->cart;
	if ( ! $cart || $cart->is_empty() ) {
		return '<div class="pp-drawer__foot" data-pp-cart-foot hidden></div>';
	}
	{
		echo '<div class="pp-drawer__foot" data-pp-cart-foot><p class="pp-subtotal"><span>' . esc_html__( 'Subtotal', 'pokepiece' ) . '</span><strong>' . wp_kses_post( $cart->get_cart_subtotal() ) . '</strong></p><p class="pp-muted">' . esc_html__( 'Shipping and tax are calculated by the shop at checkout.', 'pokepiece' ) . '</p><a class="pp-btn pp-btn--primary pp-btn--block" href="' . esc_url( wc_get_checkout_url() ) . '">' . esc_html__( 'Checkout', 'pokepiece' ) . '</a><a class="pp-btn pp-btn--ghost pp-btn--block" href="' . esc_url( wc_get_cart_url() ) . '">' . esc_html__( 'View cart', 'pokepiece' ) . '</a></div>';
	}
	return ob_get_clean();
}

function pp_cart_drawer_body() { return pp_cart_body_html() . pp_cart_foot_html(); }

add_filter( 'woocommerce_add_to_cart_fragments', function ( $f ) {
	$f['span.pp-cart-count'] = pp_cart_count_html();
	$f['div[data-pp-cart-body]'] = pp_cart_body_html();
	$f['div[data-pp-cart-foot]'] = pp_cart_foot_html();
	return $f;
} );

// Server-side quantity/remove. Nonce protects against CSRF; the Woo session decides what exists.
function pp_ajax_cart_update() {
	check_ajax_referer( 'pp_cart', 'nonce' );
	$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
	$qty = isset( $_POST['qty'] ) ? absint( $_POST['qty'] ) : 0;
	$cart = WC()->cart;
	$item = $cart->get_cart_item( $key );
	if ( ! $item ) { wp_send_json_error( array( 'message' => __( 'That item is no longer in your cart.', 'pokepiece' ), 'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array() ) ), 409 ); }
	if ( $qty > 0 ) {
		$p = $item['data'];
		if ( ! $p->has_enough_stock( $qty ) || ( $p->get_max_purchase_quantity() > 0 && $qty > $p->get_max_purchase_quantity() ) ) {
			wp_send_json_error( array( 'message' => sprintf( __( 'Only %d available of “%s”.', 'pokepiece' ), max( 0, (int) $p->get_max_purchase_quantity() ), $p->get_name() ), 'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array() ) ), 409 );
		}
		$cart->set_quantity( $key, $qty, true );
	} else {
		$cart->remove_cart_item( $key );
	}
	$cart->calculate_totals();
	wp_send_json_success( array( 'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array() ) ) );
}
add_action( 'wp_ajax_pokepiece_cart_update', 'pp_ajax_cart_update' );
add_action( 'wp_ajax_nopriv_pokepiece_cart_update', 'pp_ajax_cart_update' );
