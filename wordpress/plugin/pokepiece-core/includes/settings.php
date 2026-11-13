<?php
defined( 'ABSPATH' ) || exit;

function ppc_opt( $key, $default = '' ) {
	$o = get_option( 'ppc_settings', array() );
	return isset( $o[ $key ] ) && '' !== $o[ $key ] ? $o[ $key ] : $default;
}

add_action( 'admin_menu', function () {
	add_submenu_page( 'woocommerce', 'PokePiece shop rules', 'PokePiece rules', 'manage_woocommerce', 'ppc-rules', 'ppc_render_settings' );
} );

function ppc_render_settings() {
	if ( ! current_user_can( 'manage_woocommerce' ) ) { return; }
	if ( isset( $_POST['ppc_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['ppc_nonce'] ) ), 'ppc_save' ) ) {
		update_option( 'ppc_settings', array(
			'fs_enabled' => empty( $_POST['fs_enabled'] ) ? 'no' : 'yes',
			'fs_hours'   => max( 1, min( 168, absint( $_POST['fs_hours'] ?? 8 ) ) ),
			'fs_keyword' => sanitize_text_field( wp_unslash( $_POST['fs_keyword'] ?? 'standard' ) ),
			'fs_notice'  => empty( $_POST['fs_notice'] ) ? 'no' : 'yes',
			'fs_thanks'  => empty( $_POST['fs_thanks'] ) ? 'no' : 'yes',
		) );
		echo '<div class="notice notice-success"><p>Saved.</p></div>';
	}
	$on = 'yes' === ppc_opt( 'fs_enabled', 'no' );
	echo '<div class="wrap"><h1>PokePiece shop rules</h1><form method="post">'; wp_nonce_field( 'ppc_save', 'ppc_nonce' );
	echo '<h2>Free standard shipping window</h2><p>A customer who has placed an order gets free <em>standard</em> shipping on further orders for a set number of hours after that first order. The window starts at the first order and does not extend when they reorder inside it.</p><table class="form-table">';
	printf( '<tr><th>Offer enabled</th><td><label><input type="checkbox" name="fs_enabled" %s> On</label><p class="description">Leave OFF on staging until the old snippet is deactivated, or customers could get the offer twice.</p></td></tr>', checked( $on, true, false ) );
	printf( '<tr><th>Window (hours)</th><td><input type="number" min="1" max="168" name="fs_hours" value="%d"></td></tr>', (int) ppc_opt( 'fs_hours', 8 ) );
	printf( '<tr><th>Shipping method name contains</th><td><input type="text" name="fs_keyword" value="%s"><p class="description">Only shipping options whose name contains this word become free (default "standard"). Special Delivery and other methods are never changed.</p></td></tr>', esc_attr( ppc_opt( 'fs_keyword', 'standard' ) ) );
	printf( '<tr><th>Show a message in cart and checkout</th><td><label><input type="checkbox" name="fs_notice" %s> Tell the customer when the offer is applied</label></td></tr>', checked( 'yes' === ppc_opt( 'fs_notice', 'yes' ), true, false ) );
	printf( '<tr><th>Mention the offer on the thank-you page</th><td><label><input type="checkbox" name="fs_thanks" %s> After an order, say they have free standard shipping for the next hours</label></td></tr>', checked( 'yes' === ppc_opt( 'fs_thanks', 'no' ), true, false ) );
	echo '</table>'; submit_button(); echo '</form></div>';
}
