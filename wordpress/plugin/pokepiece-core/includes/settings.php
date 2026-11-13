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
		$old = (array) get_option( 'ppc_settings', array() );
		update_option( 'ppc_settings', array_merge( $old, array(
			'fs_enabled' => empty( $_POST['fs_enabled'] ) ? 'no' : 'yes',
			'fs_hours'   => max( 1, min( 168, absint( $_POST['fs_hours'] ?? 8 ) ) ),
			'fs_keyword' => sanitize_text_field( wp_unslash( $_POST['fs_keyword'] ?? 'standard' ) ),
			'fs_notice'  => empty( $_POST['fs_notice'] ) ? 'no' : 'yes',
			'fs_thanks'  => empty( $_POST['fs_thanks'] ) ? 'no' : 'yes',
			'sd_enabled' => empty( $_POST['sd_enabled'] ) ? 'no' : 'yes',
			'sd_price'   => (string) max( 0, (float) wc_format_decimal( wp_unslash( $_POST['sd_price'] ?? '0' ) ) ),
			'sd_label'   => sanitize_text_field( wp_unslash( $_POST['sd_label'] ?? '' ) ),
			'sd_taxable' => empty( $_POST['sd_taxable'] ) ? 'no' : 'yes',
			'sd_ref_required' => empty( $_POST['sd_ref_required'] ) ? 'no' : 'yes',
			'sd_ref_label' => sanitize_text_field( wp_unslash( $_POST['sd_ref_label'] ?? '' ) ),
		) ) );
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
	echo '<h2>Special Delivery option</h2><p>Offered as a tick-box on product pages, in the cart and at checkout, charged as a fee line. It is not a product, so it never appears in the shop. After switching it on, set the old upgrade product to Draft or Private.</p><table class="form-table">';
	printf( '<tr><th>Option enabled</th><td><label><input type="checkbox" name="sd_enabled" %s> On</label><p class="description">Needs a price above 0 to take effect.</p></td></tr>', checked( 'yes' === ppc_opt( 'sd_enabled', 'no' ), true, false ) );
	printf( '<tr><th>Price (%s)</th><td><input type="text" inputmode="decimal" name="sd_price" value="%s"></td></tr>', esc_html( get_woocommerce_currency_symbol() ), esc_attr( ppc_opt( 'sd_price', '' ) ) );
	printf( '<tr><th>Name on the order</th><td><input class="regular-text" type="text" name="sd_label" value="%s"><p class="description">Default matches the old product name so fulfilment still finds it.</p></td></tr>', esc_attr( ppc_opt( 'sd_label', 'Special Delivery Shipping Upgrade' ) ) );
	printf( '<tr><th>Charge tax on it</th><td><label><input type="checkbox" name="sd_taxable" %s> Taxable (follows the standard tax class)</label></td></tr>', checked( 'yes' === ppc_opt( 'sd_taxable', 'no' ), true, false ) );
	printf( '<tr><th>Ask for a reference</th><td><label><input type="checkbox" name="sd_ref_required" %s> Required at checkout</label> <input class="regular-text" type="text" name="sd_ref_label" value="%s"></td></tr>', checked( 'yes' === ppc_opt( 'sd_ref_required', 'yes' ), true, false ), esc_attr( ppc_opt( 'sd_ref_label', 'TikTok username or order reference' ) ) );
	foreach ( ppc_sd_legacy_ids() as $lid ) { $st = get_post_status( $lid ); if ( 'publish' === $st ) { printf( '<tr><th>Old upgrade product</th><td><strong style="color:#b32d2e">#%d is still published.</strong> <a href="%s">Edit it</a> and set it to Draft or Private once the option is on.</td></tr>', (int) $lid, esc_url( get_edit_post_link( $lid ) ) ); } }
	echo '</table>';
	submit_button(); echo '</form></div>';
}
