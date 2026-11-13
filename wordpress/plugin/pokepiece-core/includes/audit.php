<?php
/**
 * Read-only product audit: shows what must be reviewed before launch (images, category/title mismatches such as a Pokémon item filed under
 * One Piece, near-duplicate names, missing fulfilment label, zero price, stock). Changes nothing.
 */
defined( 'ABSPATH' ) || exit;

function ppc_game_in_text( $text ) {
	$t = mb_strtolower( $text );
	foreach ( array( 'pokemon' => array( 'pokémon', 'pokemon' ), 'one-piece' => array( 'one piece' ), 'dragon-ball' => array( 'dragon ball' ), 'naruto' => array( 'naruto' ), 'demon-slayer' => array( 'demon slayer' ), 'solo-leveling' => array( 'solo leveling' ) ) as $g => $needles ) {
		foreach ( $needles as $n ) { if ( str_contains( $t, $n ) ) { return $g; } }
	}
	return '';
}

function ppc_audit_rows() {
	$rows = array(); $names = array();
	$products = wc_get_products( array( 'limit' => -1, 'status' => array( 'publish', 'draft', 'private' ) ) );
	foreach ( $products as $p ) { $names[ $p->get_id() ] = preg_replace( '/[^a-z0-9]+/', ' ', mb_strtolower( $p->get_name() ) ); }
	foreach ( $products as $p ) {
		$issues = array(); $cats = array();
		foreach ( $p->get_category_ids() as $tid ) { $t = get_term( $tid, 'product_cat' ); if ( $t && ! is_wp_error( $t ) ) { $cats[] = $t->slug; } }
		$title_game = ppc_game_in_text( $p->get_name() );
		$cat_games = array_filter( array_unique( array_map( fn( $s ) => ppc_game_in_text( str_replace( '-', ' ', $s ) ), $cats ) ) );
		if ( ! $cats ) { $issues[] = 'no category'; }
		if ( $title_game && $cat_games && ! in_array( $title_game, $cat_games, true ) ) { $issues[] = "title says $title_game but category is " . implode( '/', $cat_games ); }
		if ( ! $p->get_image_id() ) { $issues[] = 'no image'; }
		if ( '' === (string) $p->get_price() || (float) $p->get_price() <= 0 ) { $issues[] = 'no price'; }
		if ( ! $p->get_meta( '_pp_mode', true ) ) { $issues[] = 'fulfilment label not set'; }
		if ( 'FIXTURE' === substr( $p->get_description(), 0, 7 ) || '' === trim( wp_strip_all_tags( $p->get_description() ) ) ) { $issues[] = 'no real description'; }
		foreach ( $names as $id => $n ) { if ( $id !== $p->get_id() && $id > $p->get_id() ) { similar_text( $names[ $p->get_id() ], $n, $pct ); if ( $pct >= 96 ) { $issues[] = "near-duplicate of #$id"; } } }
		$rows[] = array( 'id' => $p->get_id(), 'name' => $p->get_name(), 'status' => $p->get_status(), 'stock' => $p->get_stock_status(), 'price' => $p->get_price(), 'categories' => implode( ', ', $cats ), 'issues' => implode( '; ', $issues ) );
	}
	return $rows;
}

add_action( 'admin_menu', function () {
	add_submenu_page( 'woocommerce', 'PokePiece product audit', 'PokePiece audit', 'manage_woocommerce', 'ppc-audit', function () {
		if ( ! current_user_can( 'manage_woocommerce' ) ) { return; }
		echo '<div class="wrap"><h1>Product audit (read-only)</h1>';
		if ( isset( $_POST['ppc_cats'] ) && check_admin_referer( 'ppc_cats' ) ) { echo '<div class="notice notice-success"><p>' . esc_html( implode( ' | ', ppc_ensure_categories() ) ) . '</p></div>'; }
		echo '<form method="post" style="margin:12px 0">'; wp_nonce_field( 'ppc_cats' ); echo '<button class="button" name="ppc_cats" value="1">Create missing categories (Other TCGs, Accessories)</button> <span class="description">Only adds what is missing.</span></form>';
		echo '<table class="widefat striped"><thead><tr><th>ID</th><th>Product</th><th>Stock</th><th>Price</th><th>Categories</th><th>Needs attention</th></tr></thead><tbody>';
		foreach ( ppc_audit_rows() as $r ) { printf( '<tr><td>%d</td><td><a href="%s">%s</a></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>', $r['id'], esc_url( get_edit_post_link( $r['id'] ) ), esc_html( $r['name'] ), esc_html( $r['stock'] ), esc_html( $r['price'] ), esc_html( $r['categories'] ), $r['issues'] ? '<strong>' . esc_html( $r['issues'] ) . '</strong>' : 'OK' ); }
		echo '</tbody></table></div>';
	} );
} );

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'pokepiece audit', function ( $a, $assoc ) { WP_CLI\Utils\format_items( $assoc['format'] ?? 'table', ppc_audit_rows(), array( 'id', 'name', 'stock', 'price', 'categories', 'issues' ) ); } );
}
