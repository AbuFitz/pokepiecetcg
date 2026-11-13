<?php
/**
 * My Account dashboard. Replaces the stock "Hello … / From your account dashboard…" paragraphs; the designed tiles are added by inc/account.php on the same
 * `woocommerce_account_dashboard` action that WooCommerce core and extensions hook into, so nothing that attaches there is lost.
 *
 * @package pokepiece
 */
defined( 'ABSPATH' ) || exit;
do_action( 'woocommerce_account_dashboard' );
do_action( 'woocommerce_before_my_account' );
do_action( 'woocommerce_after_my_account' );
