<?php
/** Functional glyphs and colour-world helpers. */
defined( 'ABSPATH' ) || exit;

function pp_art( $name, $class = '' ) {
	// Only functional glyphs live here (no decorative artwork).
	$a = array(
		'tiktok' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M16.6 5.8A4.3 4.3 0 0 1 15.5 3h-3.1v12.3a2.6 2.6 0 1 1-2.6-2.6c.3 0 .5 0 .8.1V9.6a5.7 5.7 0 1 0 4.9 5.7V9a7.4 7.4 0 0 0 4.3 1.4V7.3a4.3 4.3 0 0 1-3.2-1.5z"/></svg>',
		'discord' => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M19.3 5.3A16 16 0 0 0 15.3 4l-.5 1a14.8 14.8 0 0 0-4.4 0L9.9 4a16 16 0 0 0-4 1.3C3.4 9 2.7 12.6 3 16.2a16.2 16.2 0 0 0 4.9 2.5l1-1.7a10.5 10.5 0 0 1-1.6-.8l.4-.3a11.6 11.6 0 0 0 9.8 0l.4.3a10.5 10.5 0 0 1-1.6.8l1 1.7a16.2 16.2 0 0 0 4.9-2.5c.4-4.2-.7-7.8-2.9-10.9zM9.3 14c-.9 0-1.6-.8-1.6-1.8s.7-1.8 1.6-1.8 1.6.8 1.6 1.8-.7 1.8-1.6 1.8zm5.4 0c-.9 0-1.6-.8-1.6-1.8s.7-1.8 1.6-1.8 1.6.8 1.6 1.8-.7 1.8-1.6 1.8z"/></svg>',
		'arrow' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17 17 7M8 7h9v9"/></svg>' );
	return '<span class="pp-art pp-art--' . esc_attr( $name . ' ' . $class ) . '" aria-hidden="true">' . ( $a[ $name ] ?? '' ) . '</span>';
}

/** Pill call-to-action with circled arrow. */
function pp_cta( $label, $url, $variant = 'light', $extra = '', $icon = '' ) {
	return '<a class="pp-cta pp-cta--' . esc_attr( $variant ) . '" href="' . esc_url( $url ) . '"' . $extra . '>' . ( $icon ? '<b class="pp-cta__ico">' . pp_art( $icon ) . '</b>' : '' ) . '<span>' . esc_html( $label ) . '</span><i>' . pp_art( 'arrow' ) . '</i></a>';
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
