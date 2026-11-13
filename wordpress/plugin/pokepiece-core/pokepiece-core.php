<?php
/**
 * Plugin Name: PokePiece Core
 * Description: PokePieceTCG shop rules that must survive any theme change: the 8-hour free standard shipping offer, category setup and a read-only product audit. Works with WooCommerce only; changes no orders, customers or stock.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 8.0
 * Requires Plugins: woocommerce
 * Author: OneSimpleSite
 * License: Proprietary - client work
 * Text Domain: pokepiece-core
 */
defined( 'ABSPATH' ) || exit;

define( 'PPC_VERSION', '0.1.0' );
define( 'PPC_DIR', plugin_dir_path( __FILE__ ) );

add_action( 'plugins_loaded', function () {
	if ( ! class_exists( 'WooCommerce' ) ) {
		add_action( 'admin_notices', function () { echo '<div class="notice notice-error"><p>PokePiece Core needs WooCommerce to be active.</p></div>'; } );
		return;
	}
	foreach ( array( 'settings', 'free-shipping', 'setup', 'audit' ) as $f ) { require_once PPC_DIR . "includes/$f.php"; }
} );

// HPOS (custom order tables) compatibility: this plugin only uses the WooCommerce CRUD API.
add_action( 'before_woocommerce_init', function () {
	if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
	}
} );
