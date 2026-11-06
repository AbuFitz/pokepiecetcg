<?php
/** WooCommerce presentation hooks. Woo owns price, stock, tax, cart, orders. */
defined( 'ABSPATH' ) || exit;
if ( ! class_exists( 'WooCommerce' ) ) { return; }

// Remove the default wrappers, sidebar, breadcrumb position and notices placement; we re-add them in our own order.
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );

add_filter( 'woocommerce_show_page_title', '__return_false' );
remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );

/** Full-bleed archive band in the colour world of the current game; plain ink on /shop/ and search. */
function pp_archive_band() {
	$game = ''; $desc = ''; $kicker = __( 'the shop', 'pokepiece' );
	if ( is_tax( 'product_cat' ) ) { $t = get_queried_object(); $game = pp_game_of_slug( $t->slug ); $desc = apply_filters( 'pp_band_description', term_description( $t->term_id, 'product_cat' ) ); $kicker = $game ? ucwords( str_replace( '-', ' ', $game ) ) : $kicker; }
	if ( is_search() ) { $kicker = __( 'search', 'pokepiece' ); }
	$title = is_search() ? sprintf( __( '“%s”', 'pokepiece' ), get_search_query() ) : woocommerce_page_title( false );
	echo '<header class="pp-band pp-game-' . esc_attr( $game ?: 'all' ) . '"><div class="pp-wrap pp-band__in">';
	echo '<p class="pp-script">' . esc_html( $kicker ) . '</p><h1 class="pp-band__title">' . esc_html( $title ) . '</h1>';
	if ( $desc ) { echo '<div class="pp-band__desc">' . wp_kses_post( $desc ) . '</div>'; }
	echo '</div></header>';
}

add_action( 'woocommerce_before_main_content', function () {
	echo '<main id="main" class="pp-main pp-wc" tabindex="-1">';
	if ( is_shop() || is_product_taxonomy() || is_search() ) { pp_archive_band(); }
	echo '<div class="pp-wrap">';
	if ( is_singular( 'product' ) || is_product_taxonomy() ) {
		woocommerce_breadcrumb( array( 'wrap_before' => '<nav class="pp-crumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'pokepiece' ) . '"><ol>', 'wrap_after' => '</ol></nav>', 'before' => '<li>', 'after' => '</li>', 'delimiter' => '' ) );
	}
}, 10 );
add_action( 'woocommerce_after_main_content', function () { echo '</div></main>'; }, 10 );

add_filter( 'woocommerce_breadcrumb_defaults', function ( $d ) { $d['home'] = __( 'Home', 'pokepiece' ); return $d; } );
add_filter( 'loop_shop_columns', fn() => 3 );
add_filter( 'body_class', function ( $c ) { if ( is_singular( 'product' ) ) { $c[] = 'pp-game-' . ( pp_product_game( get_the_ID() ) ?: 'all' ); } return $c; } );
add_filter( 'loop_shop_per_page', fn() => 12 );

/* ---- Facts + mode rendering -------------------------------------------------- */
function pp_render_mode_chip( $product ) {
	$mode = pp_product_mode( $product );
	if ( ! $mode ) { return ''; }
	$m = pp_modes()[ $mode ];
	return '<span class="pp-mode pp-mode--' . esc_attr( $mode ) . '"><span class="pp-mode__dot" aria-hidden="true"></span>' . esc_html( $m['short'] ) . '</span>';
}

function pp_render_facts( $product, $with_condition = true ) {
	$rows = array();
	if ( $l = pp_product_language( $product ) ) { $rows[ __( 'Language', 'pokepiece' ) ] = $l; }
	if ( $f = pp_product_format_label( $product ) ) { $rows[ __( 'Format', 'pokepiece' ) ] = $f; }
	$mode = pp_product_mode( $product );
	if ( $mode ) { $rows[ __( 'Fulfilment', 'pokepiece' ) ] = pp_modes()[ $mode ]['label']; }
	if ( $with_condition && ( $c = trim( (string) $product->get_meta( '_pp_condition', true ) ) ) ) { $rows[ __( 'Condition', 'pokepiece' ) ] = $c; }
	if ( $product->get_sku() ) { $rows[ __( 'SKU', 'pokepiece' ) ] = $product->get_sku(); }
	if ( ! $rows ) { return ''; }
	$h = '<dl class="pp-facts">';
	foreach ( $rows as $k => $v ) { $h .= '<div><dt>' . esc_html( $k ) . '</dt><dd>' . esc_html( $v ) . '</dd></div>'; }
	return $h . '</dl>';
}

/* ---- Single product ---------------------------------------------------------- */
function pp_single_product_hooks() {
	foreach ( array( 'woocommerce_template_single_title' => 5, 'woocommerce_template_single_rating' => 10, 'woocommerce_template_single_price' => 10, 'woocommerce_template_single_excerpt' => 20, 'woocommerce_template_single_add_to_cart' => 30, 'woocommerce_template_single_meta' => 40, 'woocommerce_template_single_sharing' => 50 ) as $fn => $pri ) {
		remove_action( 'woocommerce_single_product_summary', $fn, $pri );
	}
	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
	add_action( 'woocommerce_single_product_summary', function () {
	global $product;
	echo '<div class="pp-pdp-facts">' . pp_render_facts( $product ) . '</div>';
}, 6 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
	add_action( 'woocommerce_single_product_summary', function () {
	global $product;
	[ $k, $t ] = pp_stock_state( $product );
	echo '<p class="pp-stock pp-stock--' . esc_attr( $k ) . '"><span class="pp-stock__dot" aria-hidden="true"></span>' . esc_html( $t ) . '</p>';
	pp_mode_callout( $product );
}, 12 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
	add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	add_action( 'woocommerce_single_product_summary', function () {
	global $product;
	if ( pp_is_service( $product->get_id() ) ) {
		echo '<p class="pp-callout pp-callout--service"><strong>' . esc_html__( 'Postage service', 'pokepiece' ) . '</strong> ' . esc_html__( 'Add the TikTok username or order reference in the order notes at checkout, exactly as before.', 'pokepiece' ) . '</p>';
	}
	echo '<details class="pp-disclose"><summary>' . esc_html__( 'Delivery information', 'pokepiece' ) . pp_icon( 'chev' ) . '</summary><div><p>' . esc_html( pp_dispatch_text() ) . ' ';
	$sp = get_page_by_path( 'shipping-policy' );
	if ( $sp ) { echo '<a href="' . esc_url( get_permalink( $sp ) ) . '">' . esc_html__( 'Shipping policy', 'pokepiece' ) . '</a>'; }
	echo '</p><p>' . esc_html__( 'Delivery charges are calculated by the shop at checkout.', 'pokepiece' ) . '</p></div></details>';
}, 35 );

}
add_action( 'init', 'pp_single_product_hooks', 20 );

function pp_mode_callout( $product ) {
	$mode = pp_product_mode( $product );
	if ( ! $mode ) { return; }
	$msg = array(
		'sealed'    => __( 'This product is delivered sealed.', 'pokepiece' ),
		'no_shrink' => __( 'Sent without shrink-wrap. Read the condition note before you buy.', 'pokepiece' ),
		'live'      => __( 'Opened live, then the cards are shipped to you.', 'pokepiece' ),
		'preorder'  => __( 'This is a preorder.', 'pokepiece' ),
	);
	echo '<div class="pp-callout pp-callout--' . esc_attr( $mode ) . '" role="note"><strong>' . esc_html( pp_modes()[ $mode ]['label'] ) . '</strong><span>' . esc_html( $msg[ $mode ] ) . '</span></div>';
}

// Descriptions come after the purchase decision; related products exclude services and unavailable stock-less noise.
add_filter( 'woocommerce_related_products', function ( $ids ) { return array_values( array_diff( (array) $ids, pp_service_ids() ) ); } );
add_filter( 'woocommerce_output_related_products_args', function ( $a ) { $a['posts_per_page'] = 4; $a['columns'] = 4; return $a; } );
add_filter( 'woocommerce_product_tabs', function ( $t ) { unset( $t['additional_information'] ); return $t; } );

/* ---- Cart / misc ------------------------------------------------------------- */
add_filter( 'woocommerce_loop_add_to_cart_args', function ( $a ) { $a['class'] .= ' pp-btn pp-btn--primary'; return $a; } );
add_filter( 'woocommerce_cart_item_name', fn( $n ) => $n );

// A 404 for an empty product archive must not become a soft-404: keep Woo's no-products template (HTTP 200 with clear text is correct for empty filter results).

/* ---- Shop layout: filters column + results, no-results state ------------------- */
add_action( 'woocommerce_before_shop_loop', function () {
	echo '<div class="pp-shop"><aside class="pp-filter-panel" id="pp-filter-panel" aria-label="' . esc_attr__( 'Filters', 'pokepiece' ) . '"><div class="pp-filter-panel__head"><h2>' . esc_html__( 'Filters', 'pokepiece' ) . '</h2><button class="pp-iconbtn pp-close pp-filter-close" type="button" aria-label="' . esc_attr__( 'Close filters', 'pokepiece' ) . '">' . pp_icon( 'close' ) . '</button></div>';
	pp_render_filters();
	echo '</aside><div class="pp-results">';
}, 5 );
add_action( 'woocommerce_after_shop_loop', function () { echo '</div></div>'; }, 20 );
add_action( 'woocommerce_no_products_found', function () {
	$chips = pp_applied_chips();
	echo '<div class="pp-empty pp-empty--archive" role="status"><p><strong>' . esc_html__( 'No products match.', 'pokepiece' ) . '</strong></p>';
	if ( $chips || is_search() ) { echo '<p>' . esc_html__( 'Try removing a filter or a search term.', 'pokepiece' ) . '</p><p><a class="pp-btn pp-btn--primary" href="' . esc_url( $chips ? remove_query_arg( array( 'pp_mode', 'pp_format', 'pp_stock', 'paged' ) ) : wc_get_page_permalink( 'shop' ) ) . '">' . esc_html( $chips ? __( 'Clear all filters', 'pokepiece' ) : __( 'Browse all products', 'pokepiece' ) ) . '</a></p>'; }
	echo '</div>';
}, 5 );
remove_action( 'woocommerce_no_products_found', 'wc_no_products_found', 10 );

// Sticky purchase bar markup (single product); behaviour in main.js.
add_action( 'woocommerce_after_single_product', function () {
	global $product;
	[ $k, $t ] = pp_stock_state( $product );
	$can = $product->is_purchasable() && $product->is_in_stock();
	echo '<div class="pp-sticky" data-pp-sticky hidden><div class="pp-sticky__txt"><strong>' . esc_html( $product->get_name() ) . '</strong><span>' . wp_kses_post( $product->get_price_html() ) . ' · ' . esc_html( $t ) . '</span></div>';
	if ( $can ) { echo '<button type="button" class="pp-btn pp-btn--primary" data-pp-sticky-go>' . esc_html( $product->is_type( 'simple' ) ? __( 'Add to cart', 'pokepiece' ) : __( 'Select options', 'pokepiece' ) ) . '</button>'; }
	echo '</div>';
}, 5 );

// The theme prints its own stock line (text + dot) for simple products; suppress Woo's duplicate. Variation availability text is untouched.
add_filter( 'woocommerce_get_stock_html', fn( $html, $product ) => $product->is_type( 'simple' ) ? '' : $html, 10, 2 );
