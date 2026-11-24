<?php
/**
 * Order emails in the shop's own look. Out of the box WooCommerce sends purple emails with no logo. When enabled (default), the logo and the brand colours are applied
 * to every WooCommerce email without touching the saved email settings (switch off in WooCommerce > PokePiece rules to go back to whatever is saved there).
 */
defined( 'ABSPATH' ) || exit;

function ppc_emails_branded() { return 'no' !== ppc_opt( 'email_brand', 'yes' ); }
foreach ( array(
	'woocommerce_email_header_image' => fn() => plugins_url( 'assets/email-logo.png', PPC_FILE ),
	'woocommerce_email_base_color' => fn() => '#c1232c', 'woocommerce_email_background_color' => fn() => '#eef0f2',
	'woocommerce_email_body_background_color' => fn() => '#ffffff', 'woocommerce_email_text_color' => fn() => '#14171a',
) as $ppc_opt => $ppc_val ) {
	add_filter( 'pre_option_' . $ppc_opt, function ( $pre ) use ( $ppc_val ) { return ppc_emails_branded() && ! is_admin() ? $ppc_val() : $pre; } ); // not in the admin screen, so the saved values stay editable there
}
