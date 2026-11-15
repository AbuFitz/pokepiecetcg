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
