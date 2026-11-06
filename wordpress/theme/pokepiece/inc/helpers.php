<?php
defined( 'ABSPATH' ) || exit;

/** Fulfilment modes. Unknown ('') is a real state and is NEVER shown as sealed. */
function pp_modes() {
	return array(
		'sealed'    => array( 'label' => __( 'Delivered sealed', 'pokepiece' ), 'short' => __( 'Sealed', 'pokepiece' ) ),
		'no_shrink' => array( 'label' => __( 'No shrink', 'pokepiece' ), 'short' => __( 'No shrink', 'pokepiece' ) ),
		'live'      => array( 'label' => __( 'Opened live — cards shipped', 'pokepiece' ), 'short' => __( 'Live opening', 'pokepiece' ) ),
		'preorder'  => array( 'label' => __( 'Preorder', 'pokepiece' ), 'short' => __( 'Preorder', 'pokepiece' ) ),
	);
}

function pp_product_mode( $product ) {
	$product = wc_get_product( $product );
	if ( ! $product ) {
		return '';
	}
	$mode = (string) $product->get_meta( '_pp_mode', true );
	return isset( pp_modes()[ $mode ] ) ? $mode : '';
}

/** IDs of service/add-on products (shipping upgrade). Merchant-editable option; 686 verified from the public Store API. */
function pp_service_ids() {
	$ids = get_option( 'pokepiece_service_product_ids', array( 686 ) );
	$ids = array_filter( array_map( 'absint', (array) $ids ) );
	$flagged = get_posts( array( 'post_type' => 'product', 'fields' => 'ids', 'posts_per_page' => 50, 'meta_key' => '_pp_is_service', 'meta_value' => '1', 'no_found_rows' => true ) );
	return array_values( array_unique( array_merge( $ids, array_map( 'absint', $flagged ) ) ) );
}

function pp_is_service( $product_id ) {
	return in_array( (int) $product_id, pp_service_ids(), true );
}

/** Language written out, derived from the existing category slug suffix (-english / -japanese). */
function pp_product_language( $product ) {
	$product = wc_get_product( $product );
	if ( ! $product ) {
		return '';
	}
	foreach ( $product->get_category_ids() as $tid ) {
		$t = get_term( $tid, 'product_cat' );
		if ( $t && ! is_wp_error( $t ) ) {
			if ( str_ends_with( $t->slug, '-english' ) ) {
				return __( 'English', 'pokepiece' );
			}
			if ( str_ends_with( $t->slug, '-japanese' ) ) {
				return __( 'Japanese', 'pokepiece' );
			}
		}
	}
	return '';
}

function pp_product_format_label( $product ) {
	$f = (string) wc_get_product( $product )->get_meta( '_pp_format', true );
	$map = array( 'box' => __( 'Booster box', 'pokepiece' ), 'pack' => __( 'Booster pack', 'pokepiece' ), 'etb' => __( 'Elite Trainer Box', 'pokepiece' ), 'bundle' => __( 'Bundle', 'pokepiece' ), 'collection' => __( 'Collection', 'pokepiece' ), 'other' => __( 'Other', 'pokepiece' ) );
	return $map[ $f ] ?? '';
}

function pp_formats() {
	return array( 'box' => __( 'Booster box', 'pokepiece' ), 'pack' => __( 'Booster pack', 'pokepiece' ), 'etb' => __( 'Elite Trainer Box', 'pokepiece' ), 'bundle' => __( 'Bundle', 'pokepiece' ), 'collection' => __( 'Collection', 'pokepiece' ), 'other' => __( 'Other', 'pokepiece' ) );
}

/** Stock state as text — never colour alone. */
function pp_stock_state( $product ) {
	if ( $product->is_on_backorder() ) {
		return array( 'backorder', __( 'Available on backorder', 'pokepiece' ) );
	}
	if ( $product->is_in_stock() ) {
		return array( 'in', __( 'In stock', 'pokepiece' ) );
	}
	return array( 'out', __( 'Out of stock', 'pokepiece' ) );
}

/** Game → language categories, derived from real product_cat terms (no hard-coded IDs). */
function pp_game_groups() {
	$games = array(
		'pokemon'    => array( 'name' => 'Pokémon', 'prefix' => 'pokemon-' ),
		'one-piece'  => array( 'name' => 'One Piece', 'prefix' => 'one-piece-' ),
		'dragon-ball' => array( 'name' => 'Dragon Ball', 'prefix' => 'dragon-ball-' ),
	);
	$terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	$out = array();
	foreach ( $games as $key => $g ) {
		$items = array();
		foreach ( $terms as $t ) {
			if ( str_starts_with( $t->slug, $g['prefix'] ) ) {
				$lang = ucfirst( substr( $t->slug, strlen( $g['prefix'] ) ) );
				$items[] = array( 'name' => $lang, 'url' => get_term_link( $t ), 'count' => (int) $t->count, 'slug' => $t->slug );
			}
		}
		if ( $items ) {
			usort( $items, fn( $a, $b ) => strcmp( $a['name'], $b['name'] ) );
			$out[ $key ] = $g + array( 'items' => $items );
		}
	}
	return $out;
}

function pp_dispatch_text() {
	// PROVISIONAL: wording published in the shipping policy (1–3 working days); owner must reconfirm.
	return apply_filters( 'pokepiece_dispatch_text', get_option( 'pokepiece_dispatch_text', __( 'Dispatched normally within 1–3 working days.', 'pokepiece' ) ) );
}

/** Products for discovery rows: purchasable catalogue only, service products excluded. */
function pp_discovery_products( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'status' => 'publish', 'limit' => 8, 'orderby' => 'date', 'order' => 'DESC',
		'exclude' => pp_service_ids(), 'visibility' => 'catalog', 'return' => 'objects',
	) );
	return wc_get_products( $args );
}

function pp_icon( $name ) {
	$p = array(
		'search' => '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/>',
		'cart'   => '<path d="M3 4h2.4l2 10.2h9.4L19 7H6.2"/><circle cx="9" cy="19" r="1.3"/><circle cx="17" cy="19" r="1.3"/>',
		'user'   => '<circle cx="12" cy="8.5" r="3.6"/><path d="M5 20c.6-3.7 3.4-5.6 7-5.6s6.4 1.9 7 5.6"/>',
		'menu'   => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'  => '<path d="m6 6 12 12M18 6 6 18"/>',
		'chev'   => '<path d="m6 9 6 6 6-6"/>',
		'filter' => '<path d="M4 7h10M18 7h2M4 17h2M10 17h10"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/>',
		'arrow'  => '<path d="M5 12h14m-5-5 5 5-5 5"/>',
	);
	return '<svg class="pp-i" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . ( $p[ $name ] ?? '' ) . '</svg>';
}
