<?php
/** Idempotent category setup: creates what is missing, never edits, moves or deletes an existing category or product. */
defined( 'ABSPATH' ) || exit;

function ppc_ensure_categories() {
	$plan = array(
		array( 'Other TCGs', 'other-tcgs', 0, array( array( 'Naruto', 'naruto' ), array( 'Demon Slayer', 'demon-slayer' ), array( 'Solo Leveling', 'solo-leveling' ) ) ),
		array( 'Accessories', 'accessories', 0, array() ),
	);
	$report = array();
	foreach ( $plan as [ $name, $slug, $parent, $kids ] ) {
		$t = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $t ) { $report[] = "exists: $slug"; $pid = $t->term_id; }
		else { $r = wp_insert_term( $name, 'product_cat', array( 'slug' => $slug, 'parent' => $parent ) ); if ( is_wp_error( $r ) ) { $report[] = "FAILED $slug: " . $r->get_error_message(); continue; } $pid = $r['term_id']; $report[] = "created: $slug"; }
		foreach ( $kids as [ $kn, $ks ] ) {
			if ( get_term_by( 'slug', $ks, 'product_cat' ) ) { $report[] = "exists: $ks"; continue; }
			$r = wp_insert_term( $kn, 'product_cat', array( 'slug' => $ks, 'parent' => $pid ) );
			$report[] = is_wp_error( $r ) ? "FAILED $ks: " . $r->get_error_message() : "created: $ks";
		}
	}
	return $report;
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'pokepiece ensure-categories', function () { foreach ( ppc_ensure_categories() as $l ) { WP_CLI::log( $l ); } WP_CLI::success( 'Done. Nothing existing was changed.' ); } );
}


/**
 * Privacy link: WooCommerce's checkout and registration text link to the page chosen under Settings > Privacy. If that is a draft or missing (WordPress's
 * default is a draft), the link is a 404. When a published page with the slug `privacy-policy` exists, use it instead. Nothing is written to the database.
 */
add_filter( 'pre_option_wp_page_for_privacy_policy', function ( $pre ) {
	static $busy = false; if ( $busy ) { return $pre; }
	$busy = true; $id = (int) get_option( 'wp_page_for_privacy_policy' ); $busy = false;
	if ( $id && 'publish' === get_post_status( $id ) ) { return $pre; }
	$pg = get_page_by_path( 'privacy-policy' );
	return $pg && 'publish' === $pg->post_status ? (int) $pg->ID : $pre;
} );


/**
 * Evergreen buying guides, created as DRAFTS for the owner to review, edit and publish (never auto-published, never overwritten). Wording is deliberately general;
 * the owner should correct anything that does not match how he sells. `wp pokepiece ensure-guides`.
 */
function ppc_ensure_guides() {
	$link = function ( $slug, $text ) { $t = get_term_by( 'slug', $slug, 'product_cat' ); $u = $t ? get_term_link( $t ) : ''; return $u && ! is_wp_error( $u ) ? '<a href="' . esc_url( $u ) . '">' . esc_html( $text ) . '</a>' : esc_html( $text ); };
	$guides = array(
		'guide-japanese-vs-english-booster-boxes' => array( 'Japanese vs English booster boxes: what is the difference?',
			'<p>Many Pokémon and One Piece sets are released in both Japanese and English. This guide explains what to look at before you choose.</p><h2>Language</h2><p>Japanese boxes contain Japanese-language cards; English boxes contain English-language cards. Collectors often choose Japanese for the original artwork and release timing, and English for play or for a collection in their own language.</p><h2>Release timing</h2><p>Japanese sets are often released before the English version, so a set can appear in Japanese first. Dates vary by set, so check the product page.</p><h2>Contents can differ</h2><p>The pack count, cards per pack and rarity make-up of a Japanese box are not always the same as the English box for the same set. Always check the listing for what is inside.</p><h2>Shop the ranges</h2><ul><li>' . '[[pokemon-japanese|Pokémon Japanese]]' . '</li><li>[[pokemon-english|Pokémon English]]</li><li>[[one-piece-japanese|One Piece Japanese]]</li><li>[[one-piece-english|One Piece English]]</li></ul>' ),
		'guide-sealed-booster-boxes-and-shrink-wrap' => array( 'Sealed booster boxes and shrink wrap: what to check',
			'<p>A sealed booster box is a box of trading card packs that has not been opened. Listings can differ in how the box is sealed, so it helps to know what to look for.</p><h2>Shrink wrap</h2><p>Many boxes come with a factory plastic wrap. Some listings are sold without that wrap and are marked accordingly. Each product page states which applies, so check the fulfilment label before you buy.</p><h2>Why it matters to collectors</h2><p>Collectors who keep boxes sealed often care about the condition of the box and its wrap. If this matters to you, read the listing and ask before ordering.</p><h2>How we pack</h2><p>Orders are packed securely to protect sealed products in transit.</p><h2>Shop sealed products</h2><ul><li>[[pokemon-japanese|Pokémon Japanese]]</li><li>[[one-piece-japanese|One Piece Japanese]]</li><li>[[dragon-ball-english|Dragon Ball English]]</li></ul>' ),
	);
	$out = array();
	foreach ( $guides as $slug => $g ) {
		if ( get_page_by_path( $slug, OBJECT, 'page' ) ) { $out[] = "$slug exists, left alone"; continue; }
		$body = preg_replace_callback( '/\[\[([a-z0-9-]+)\|([^\]]+)\]\]/', fn( $m ) => $link( $m[1], $m[2] ), $g[1] );
		$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => $g[0], 'post_name' => $slug, 'post_content' => $body ), true );
		$out[] = is_wp_error( $id ) ? "$slug failed" : "$slug created as DRAFT (#$id)";
	}
	return $out;
}
if ( defined( 'WP_CLI' ) && WP_CLI ) { WP_CLI::add_command( 'pokepiece ensure-guides', function () { foreach ( ppc_ensure_guides() as $l ) { WP_CLI::log( $l ); } } ); }
