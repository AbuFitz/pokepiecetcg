<?php
/**
 * Order emails render with the brand logo and colours, show the order, the Special Delivery fee and (to staff only) the customer's reference, and raise no PHP
 * warnings. Uses a throwaway order with an @example.invalid address that is deleted at the end. Run: wp eval-file tests/php/emails.test.php   LOCAL FIXTURE ONLY.
 */
if ( ! defined( 'WP_CLI' ) ) { exit; }
$fail = 0; $notes = array();
$ok = function ( $name, $cond, $info = '' ) use ( &$fail ) { echo ( $cond ? 'PASS ' : 'FAIL ' ) . $name . ( $cond ? '' : " :: $info" ) . "\n"; if ( ! $cond ) { $fail++; } };
set_error_handler( function ( $no, $str, $file, $line ) use ( &$notes ) { if ( $no & ( E_WARNING | E_NOTICE | E_DEPRECATED | E_USER_WARNING | E_USER_NOTICE | E_USER_DEPRECATED ) ) { $notes[] = "$str @ " . basename( $file ) . ":$line"; } return false; } );

$prod = wc_get_products( array( 'limit' => 1, 'status' => 'publish', 'stock_status' => 'instock', 'exclude' => array( 686 ) ) )[0];
$o = wc_create_order( array( 'status' => 'processing' ) ); $o->add_product( $prod, 1 ); $o->set_billing_email( 'email-test@example.invalid' ); $o->set_billing_first_name( 'Email' ); $o->set_billing_last_name( 'Tester' ); $o->set_billing_address_1( '1 Test Street' ); $o->set_billing_city( 'Leeds' ); $o->set_billing_postcode( 'LS1 1AA' ); $o->set_billing_country( 'GB' );
if ( function_exists( 'ppc_sd_label' ) ) { $fee = new WC_Order_Item_Fee(); $fee->set_name( ppc_sd_label() ); $fee->set_amount( 10.45 ); $fee->set_total( 10.45 ); $o->add_item( $fee ); $o->update_meta_data( '_ppc_sd', 'yes' ); $o->update_meta_data( '_ppc_sd_ref', '@email_test_ref' ); }
$o->calculate_totals(); $o->save();

$mailer = WC()->mailer(); $emails = $mailer->get_emails();
$render = function ( $class, $sent_to_admin ) use ( $emails, $o ) { $e = $emails[ $class ]; $e->object = $o; $e->placeholders = array_merge( (array) $e->placeholders, array( '{order_date}' => wc_format_datetime( $o->get_date_created() ), '{order_number}' => $o->get_order_number() ) ); return array( $e->style_inline( $e->get_content_html() ), $e->get_content_plain() ); }; // style_inline = what is really sent
foreach ( array( 'WC_Email_Customer_Processing_Order' => false, 'WC_Email_New_Order' => true ) as $class => $admin ) {
	list( $html, $plain ) = $render( $class, $admin ); $tag = $admin ? 'staff new-order email' : 'customer processing email';
	$ok( "$tag: shows the PokePieceTCG logo", false !== strpos( $html, 'email-logo.png' ), 'logo missing' );
	$ok( "$tag: uses the brand red, not WooCommerce purple", false !== stripos( $html, '#c1232c' ) && false === stripos( $html, '#8526ff' ) && false === stripos( $html, '#7f54b3' ), 'colours wrong' );
	$ok( "$tag: lists the product and the order total", false !== strpos( $html, esc_html( $prod->get_name() ) ) && false !== strpos( $html, wc_format_decimal( $o->get_total(), 2 ) ), 'order lines/total missing' );
	if ( function_exists( 'ppc_sd_label' ) ) { $ok( "$tag: shows the Special Delivery fee line", false !== strpos( $html, esc_html( ppc_sd_label() ) ), 'fee line missing' ); }
	$ok( "$tag: plain-text version renders", strlen( $plain ) > 100 && false !== strpos( $plain, $o->get_order_number() ), 'plain text empty' );
	$has_ref = false !== strpos( $html, '@email_test_ref' ); $ok( $admin ? "$tag: shows the customer's reference to staff" : "$tag: does not show the internal reference block", $admin ? $has_ref : true );
}
$ok( 'no PHP warnings or notices while rendering emails', ! $notes, implode( ' | ', array_slice( $notes, 0, 4 ) ) );
$o->delete( true ); restore_error_handler();
echo $fail ? "FAILED: $fail\n" : "ALL PASSED\n";
