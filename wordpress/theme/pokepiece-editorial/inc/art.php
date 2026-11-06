<?php
/** Functional glyphs and colour-world helpers. */
defined( 'ABSPATH' ) || exit;

function pp_art( $name, $class = '' ) {
	// Only functional glyphs live here (no decorative artwork).
	$a = array( 'arrow' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7M8 7h9v9"/></svg>' );
	return '<span class="pp-art pp-art--' . esc_attr( $name . ' ' . $class ) . '" aria-hidden="true">' . ( $a[ $name ] ?? '' ) . '</span>';
}

/** Pill call-to-action with circled arrow. */
function pp_cta( $label, $url, $variant = 'light', $extra = '' ) {
	return '<a class="pp-cta pp-cta--' . esc_attr( $variant ) . '" href="' . esc_url( $url ) . '"' . $extra . '><span>' . esc_html( $label ) . '</span><i>' . pp_art( 'arrow' ) . '</i></a>';
}

/** Which franchise colour world a product / term belongs to (from the existing category slugs). */
function pp_game_of_slug( $slug ) {
	foreach ( array( 'pokemon' => 'pokemon-', 'one-piece' => 'one-piece-', 'dragon-ball' => 'dragon-ball-' ) as $k => $p ) {
		if ( str_starts_with( (string) $slug, $p ) ) { return $k; }
	}
	return '';
}
function pp_product_game( $product ) {
	$product = wc_get_product( $product );
	if ( ! $product ) { return ''; }
	foreach ( $product->get_category_ids() as $tid ) {
		$t = get_term( $tid, 'product_cat' );
		if ( $t && ! is_wp_error( $t ) && ( $g = pp_game_of_slug( $t->slug ) ) ) { return $g; }
	}
	return '';
}
