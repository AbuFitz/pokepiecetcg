<?php
/**
 * Google Merchant Center product feed (free product listings). Public, read-only, regenerated hourly: {home}/?ppc_feed=google
 * Contains published, visible products only (never the old shipping-upgrade product, never zero-price items). Images are the clean originals because Merchant Center
 * does not accept watermarked photos. Shipping and returns are configured inside the Merchant Center account, not here.
 */
defined( 'ABSPATH' ) || exit;

function ppc_feed_clean( $t, $max = 4900 ) { return mb_substr( trim( preg_replace( '/\s+/', ' ', html_entity_decode( wp_strip_all_tags( (string) $t ), ENT_QUOTES, 'UTF-8' ) ) ), 0, $max ); }
function ppc_feed_brand( $p ) {
	foreach ( $p->get_category_ids() as $tid ) { $t = get_term( $tid, 'product_cat' ); while ( $t && ! is_wp_error( $t ) ) { foreach ( array( 'pokemon-' => 'Pokémon', 'one-piece-' => 'One Piece', 'dragon-ball-' => 'Dragon Ball' ) as $pre => $name ) { if ( str_starts_with( $t->slug, $pre ) ) { return $name; } } $t = $t->parent ? get_term( $t->parent, 'product_cat' ) : null; } }
	return '';
}
function ppc_feed_xml( $fresh = false ) {
	$c = $fresh ? false : get_transient( 'ppc_feed_google' ); if ( is_string( $c ) && '' !== $c ) { return $c; }
	$skip = ppc_sd_legacy_ids(); $cur = get_woocommerce_currency();
	$x = new XMLWriter(); $x->openMemory(); $x->setIndent( true ); $x->startDocument( '1.0', 'UTF-8' );
	$x->startElement( 'rss' ); $x->writeAttribute( 'version', '2.0' ); $x->writeAttribute( 'xmlns:g', 'http://base.google.com/ns/1.0' ); $x->startElement( 'channel' );
	$x->writeElement( 'title', get_bloginfo( 'name' ) ); $x->writeElement( 'link', home_url( '/' ) ); $x->writeElement( 'description', get_bloginfo( 'name' ) . ' product feed' );
	foreach ( wc_get_products( array( 'limit' => -1, 'status' => 'publish', 'visibility' => 'catalog', 'type' => array( 'simple', 'variable' ) ) ) as $p ) {
		if ( in_array( $p->get_id(), $skip, true ) || (float) $p->get_price() <= 0 || ! $p->get_image_id() ) { continue; }
		$d = ppc_feed_clean( $p->get_short_description() ?: $p->get_description() );
		if ( strlen( $d ) < 40 || preg_match( '/^(fixture|lorem|placeholder|test)/i', $d ) ) { $d = $p->get_name() . ' from ' . get_bloginfo( 'name' ) . ', a UK based trading card store.'; }
		$x->startElement( 'item' );
		$x->writeElement( 'g:id', (string) $p->get_id() ); $x->writeElement( 'title', mb_substr( ppc_feed_clean( $p->get_name(), 150 ), 0, 150 ) ); $x->writeElement( 'description', $d );
		$x->writeElement( 'link', $p->get_permalink() ); $x->writeElement( 'g:image_link', wp_get_attachment_image_url( $p->get_image_id(), 'large' ) ?: wp_get_attachment_url( $p->get_image_id() ) );
		$x->writeElement( 'g:availability', $p->is_in_stock() ? 'in_stock' : 'out_of_stock' ); $x->writeElement( 'g:price', wc_format_decimal( wc_get_price_to_display( $p ), 2 ) . ' ' . $cur );
		$x->writeElement( 'g:condition', 'new' ); if ( $b = ppc_feed_brand( $p ) ) { $x->writeElement( 'g:brand', $b ); } else { $x->writeElement( 'g:brand', get_bloginfo( 'name' ) ); }
		$x->writeElement( 'g:identifier_exists', 'no' );
		$cats = wp_get_post_terms( $p->get_id(), 'product_cat', array( 'fields' => 'names' ) ); if ( $cats && ! is_wp_error( $cats ) ) { $x->writeElement( 'g:product_type', ppc_feed_clean( implode( ' > ', $cats ), 200 ) ); }
		$x->endElement();
	}
	$x->endElement(); $x->endElement(); $x->endDocument(); $out = $x->outputMemory();
	set_transient( 'ppc_feed_google', $out, HOUR_IN_SECONDS ); return $out;
}
add_action( 'template_redirect', function () {
	if ( ! isset( $_GET['ppc_feed'] ) || 'google' !== $_GET['ppc_feed'] ) { return; } // phpcs:ignore WordPress.Security.NonceVerification
	nocache_headers(); header( 'Content-Type: application/xml; charset=UTF-8' ); header( 'X-Robots-Tag: noindex' ); echo ppc_feed_xml(); exit; // phpcs:ignore WordPress.Security.EscapeOutput
} );
foreach ( array( 'woocommerce_update_product', 'woocommerce_new_product', 'woocommerce_delete_product', 'woocommerce_product_set_stock', 'woocommerce_variation_set_stock', 'woocommerce_product_set_stock_status' ) as $h ) { add_action( $h, function () { delete_transient( 'ppc_feed_google' ); } ); }
