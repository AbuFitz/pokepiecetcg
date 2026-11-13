<?php
/**
 * Special Delivery as an OPTION, not a product. When enabled in WooCommerce > PokePiece rules it is a fee line the customer opts into on a product page,
 * in the cart drawer, on the cart page or at checkout. No catalogue listing is needed; the old upgrade product (default id 686) can be set to Draft/Private
 * once this is switched on. Orders carry the fee line (default name matches the old product so fulfilment can still find it), `_ppc_sd` = yes and the
 * customer's reference (e.g. TikTok username) in `_ppc_sd_ref`.
 */
defined( 'ABSPATH' ) || exit;

function ppc_sd_price() { return max( 0, (float) wc_format_decimal( ppc_opt( 'sd_price', '0' ) ) ); }
/** Active only when switched on AND a price is set: never a free, accidental upgrade. */
function ppc_sd_active() { return 'yes' === ppc_opt( 'sd_enabled', 'no' ) && ppc_sd_price() > 0; }
function ppc_sd_label() { return ppc_opt( 'sd_label', 'Special Delivery Shipping Upgrade' ); }
function ppc_sd_legacy_ids() { return array_map( 'intval', (array) get_option( 'pokepiece_service_product_ids', array( 686 ) ) ); }

function ppc_sd_chosen() {
	return ppc_sd_active() && WC()->session && 'yes' === WC()->session->get( 'ppc_sd' ) && WC()->cart && ! WC()->cart->is_empty();
}
function ppc_sd_set( $on ) {
	if ( ! WC()->session ) { return; }
	if ( ! WC()->session->has_session() ) { WC()->session->set_customer_session_cookie( true ); }
	WC()->session->set( 'ppc_sd', $on ? 'yes' : 'no' );
}

/** The checkbox used in the cart drawer, cart page and checkout. $ctx only changes a CSS class. */
function ppc_sd_toggle_html( $ctx = 'cart' ) {
	if ( ! ppc_sd_active() ) { return ''; }
	return '<label class="ppc-sd ppc-sd--' . esc_attr( $ctx ) . '"><input type="checkbox" data-ppc-sd value="1"' . checked( ppc_sd_chosen(), true, false ) . '><span class="ppc-sd__box" aria-hidden="true"></span><span class="ppc-sd__txt"><strong>' . esc_html__( 'Add Special Delivery', 'pokepiece-core' ) . '</strong> <em>+' . wp_kses_post( wc_price( ppc_sd_price() ) ) . '</em></span></label>';
}

// A finished order (or an emptied cart) must not leave the option ticked for the customer's next basket.
add_action( 'woocommerce_cart_emptied', function () { if ( WC()->session ) { WC()->session->set( 'ppc_sd', 'no' ); } } );

// Fee line.
add_action( 'woocommerce_cart_calculate_fees', function ( $cart ) {
	if ( is_admin() && ! wp_doing_ajax() ) { return; }
	if ( ppc_sd_chosen() ) { $cart->add_fee( ppc_sd_label(), ppc_sd_price(), 'yes' === ppc_opt( 'sd_taxable', 'no' ) ); }
} );

// Old product line in a returning customer's cart: convert it to the option so they are never charged twice.
add_action( 'woocommerce_cart_loaded_from_session', function ( $cart ) {
	if ( ! ppc_sd_active() ) { return; }
	foreach ( $cart->get_cart() as $key => $item ) {
		if ( in_array( (int) $item['product_id'], ppc_sd_legacy_ids(), true ) ) { $cart->remove_cart_item( $key ); ppc_sd_set( true ); }
	}
} );

// Product page: ticking "Add Special Delivery" while adding to cart (theme sends pp_upgrade=1).
add_action( 'woocommerce_add_to_cart', function () {
	if ( ppc_sd_active() && ! empty( $_POST['pp_upgrade'] ) ) { ppc_sd_set( true ); } // phpcs:ignore WordPress.Security.NonceVerification
} );

// Toggle endpoint: /?wc-ajax=ppc_sd_toggle (POST on=1|0, nonce).
add_action( 'wc_ajax_ppc_sd_toggle', function () {
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ?? '' ) ), 'ppc_sd' ) ) { wp_send_json_error( array( 'message' => 'expired' ), 403 ); }
	if ( ! ppc_sd_active() ) { wp_send_json_error( array( 'message' => 'off' ), 409 ); }
	ppc_sd_set( ! empty( $_POST['on'] ) && '0' !== $_POST['on'] );
	WC()->cart->calculate_totals();
	wp_send_json_success( array( 'on' => ppc_sd_chosen() ) );
} );

// Checkout: option + reference field (static DOM, outside the refreshed fragments, so typed text is never lost).
add_action( 'woocommerce_review_order_before_payment', function () {
	if ( ! ppc_sd_active() ) { return; }
	$req = 'yes' === ppc_opt( 'sd_ref_required', 'yes' ); $label = ppc_opt( 'sd_ref_label', 'TikTok username or order reference' );
	echo '<div class="ppc-sd-block">' . ppc_sd_toggle_html( 'checkout' ); // phpcs:ignore
	echo '<p class="form-row ppc-sd-ref" id="ppc_sd_ref_field"' . ( ppc_sd_chosen() ? '' : ' hidden' ) . '><label for="ppc_sd_ref">' . esc_html( $label ) . ( $req ? ' <abbr class="required" title="required">*</abbr>' : '' ) . '</label><input type="text" class="input-text" name="ppc_sd_ref" id="ppc_sd_ref" maxlength="120" value="' . esc_attr( isset( $_POST['ppc_sd_ref'] ) ? sanitize_text_field( wp_unslash( $_POST['ppc_sd_ref'] ) ) : '' ) . '"' . ( $req ? ' aria-required="true"' : '' ) . '></p></div>'; // phpcs:ignore WordPress.Security.NonceVerification
} );
add_action( 'woocommerce_checkout_process', function () {
	if ( ppc_sd_chosen() && 'yes' === ppc_opt( 'sd_ref_required', 'yes' ) && '' === trim( sanitize_text_field( wp_unslash( $_POST['ppc_sd_ref'] ?? '' ) ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		wc_add_notice( sprintf( /* translators: %s field label */ __( '%s is required for Special Delivery.', 'pokepiece-core' ), ppc_opt( 'sd_ref_label', 'TikTok username or order reference' ) ), 'error' );
	}
} );
add_action( 'woocommerce_checkout_create_order', function ( $order ) {
	if ( ! ppc_sd_chosen() ) { return; }
	$order->update_meta_data( '_ppc_sd', 'yes' );
	$ref = sanitize_text_field( wp_unslash( $_POST['ppc_sd_ref'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification
	if ( '' !== $ref ) { $order->update_meta_data( '_ppc_sd_ref', $ref ); }
} );
add_action( 'woocommerce_store_api_checkout_order_processed', function ( $order ) { if ( ppc_sd_chosen() ) { $order->update_meta_data( '_ppc_sd', 'yes' ); $order->save(); } } );
add_action( 'woocommerce_admin_order_data_after_shipping_address', function ( $order ) {
	if ( 'yes' !== $order->get_meta( '_ppc_sd' ) ) { return; }
	echo '<p><strong>' . esc_html( ppc_sd_label() ) . ':</strong> ' . esc_html( $order->get_meta( '_ppc_sd_ref' ) ?: 'no reference given' ) . '</p>';
} );
add_action( 'woocommerce_email_order_meta', function ( $order, $sent_to_admin ) {
	if ( $sent_to_admin && 'yes' === $order->get_meta( '_ppc_sd' ) ) { echo '<p><strong>' . esc_html( ppc_sd_label() ) . ':</strong> ' . esc_html( $order->get_meta( '_ppc_sd_ref' ) ?: 'no reference given' ) . '</p>'; }
}, 10, 2 );

// Front-end script + minimal styles (theme-neutral; the PokePiece theme restyles .ppc-sd).
add_action( 'wp_enqueue_scripts', function () {
	if ( ! ppc_sd_active() ) { return; }
	wp_enqueue_script( 'ppc-sd', plugins_url( 'assets/sd.js', PPC_FILE ), array( 'jquery' ), PPC_VERSION, true );
	wp_localize_script( 'ppc-sd', 'PPC_SD', array( 'url' => add_query_arg( 'wc-ajax', 'ppc_sd_toggle', home_url( '/' ) ), 'nonce' => wp_create_nonce( 'ppc_sd' ) ) );
	wp_add_inline_style( 'woocommerce-general', '.ppc-sd{display:flex;gap:.6em;align-items:center;cursor:pointer}.ppc-sd-block{margin:0 0 1.2em}.ppc-sd-ref{margin-top:.6em}' );
}, 30 );
