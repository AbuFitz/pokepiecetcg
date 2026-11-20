<?php
/**
 * Product photo watermark. A semi-transparent, tilted tile of the PokePieceTCG logo is drawn OVER every product photo the theme shows: shop and category
 * cards, New arrivals, search results, quick view, the product page and its zoom view. It applies to every product, including ones added later, with
 * nothing to do per listing. The photo files themselves are never changed. Settings > PokePiece: on/off and strength.
 * Limit to be aware of: it is a visual layer, so someone who saves the raw image file (rather than a screenshot) gets the original photo.
 */
defined( 'ABSPATH' ) || exit;

function pp_wm_on() { return 'no' !== get_option( 'pokepiece_wm_on', 'yes' ); }
function pp_wm_strength() { return max( 5, min( 60, (int) get_option( 'pokepiece_wm_strength', 15 ) ) ); }

add_filter( 'body_class', function ( $c ) { if ( pp_wm_on() ) { $c[] = 'pp-wm'; } return $c; } );
add_action( 'wp_head', function () {
	if ( pp_wm_on() ) { echo '<style id="pp-wm-vars">:root{--pp-wm-o:' . esc_html( number_format( pp_wm_strength() / 100, 2, '.', '' ) ) . '}</style>' . "\n"; }
}, 5 );

// The product photo normally links to the full-size file (for the zoom popup). With the watermark on there is no zoom popup, and the photo must not link to the unmarked original.
add_filter( 'woocommerce_single_product_image_thumbnail_html', function ( $html ) { return pp_wm_on() ? preg_replace( '#<a\s[^>]*>(.*?)</a>#s', '$1', $html ) : $html; }, 20 );
