<?php
/**
 * LOCAL DEV ONLY (wp eval-file). Seeds a THROWAWAY site from a *public* Store API snapshot (products.json, categories) so the theme
 * can be rendered with realistic names/IDs/prices/stock. Not a production import; never run on staging/production.
 * Env: PP_SNAPSHOT=/path/products.json  PP_IMAGES=1 (download public product images locally)  PP_INSTOCK_FIXTURE=1 (adds clearly labelled fake in-stock items)
 */
if ( defined( 'PP_STAGING' ) || ! str_contains( home_url(), 'localhost' ) ) { WP_CLI::error( 'Refusing to seed: not a localhost dev site.' ); }
$snap = json_decode( file_get_contents( getenv( 'PP_SNAPSHOT' ) ), true );
require_once ABSPATH . 'wp-admin/includes/image.php'; require_once ABSPATH . 'wp-admin/includes/file.php'; require_once ABSPATH . 'wp-admin/includes/media.php';

foreach ( array( 'pokemon-english' => 'Pokémon English', 'pokemon-japanese' => 'Pokémon Japanese', 'one-piece-english' => 'One Piece English', 'one-piece-japanese' => 'One Piece Japanese', 'dragon-ball-english' => 'Dragon Ball English' ) as $slug => $name ) {
	if ( ! term_exists( $slug, 'product_cat' ) ) { wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) ); }
}
$pages = array(
	'about' => array( 'About', "<h2>FIXTURE TEXT</h2><p>Placeholder copy for layout review only. The real About content is supplied and approved by the merchant.</p>" ),
	'contact' => array( 'Contact', "<p>FIXTURE: the existing contact form/plugin output is preserved on staging; this page only demonstrates layout.</p><form><p><label>Name<br><input type='text'></label></p><p><label>Email<br><input type='email'></label></p><p><label>Message<br><textarea></textarea></label></p><p><button class='pp-btn pp-btn--primary' type='button'>Send (fixture)</button></p></form>" ),
	'shipping-policy' => array( 'Shipping policy', "<h2>FIXTURE TEXT</h2><p>Dispatched normally within 1 to 3 working days is the published wording to be reconfirmed by the owner. Replace with the approved policy.</p><h3>Heading three</h3><p>" . str_repeat( 'Policy body paragraph to test measure and rhythm. ', 18 ) . "</p><ul><li>List item</li><li>List item</li></ul>" ),
	'refund-return-policy' => array( 'Returns policy', '<p>FIXTURE TEXT.</p>' ), 'terms-conditions' => array( 'Terms &amp; conditions', '<p>FIXTURE TEXT.</p>' ), 'privacy-policy' => array( 'Privacy policy', '<p>FIXTURE TEXT.</p>' ),
);
foreach ( $pages as $slug => [ $title, $content ] ) { if ( ! get_page_by_path( $slug ) ) { wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => $slug, 'post_content' => $content ) ); } }
$hp = get_page_by_path( 'home-2' ); $home = $hp ? $hp->ID : wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'Home', 'post_name' => 'home-2', 'post_content' => '' ) );
update_option( 'show_on_front', 'page' ); update_option( 'page_on_front', $home );
// Classic cart/checkout shortcodes in the fixture (the live store type, classic or Blocks, is a discovery item).
foreach ( array( 'cart' => '[woocommerce_cart]', 'checkout' => '[woocommerce_checkout]', 'my-account' => '[woocommerce_my_account]' ) as $slug => $sc ) {
	$pg = get_page_by_path( $slug ); $id = $pg ? $pg->ID : wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => ucwords( str_replace( '-', ' ', $slug ) ), 'post_name' => $slug, 'post_content' => $sc ) );
	wp_update_post( array( 'ID' => $id, 'post_content' => $sc ) ); update_option( 'woocommerce_' . ( 'my-account' === $slug ? 'myaccount' : $slug ) . '_page_id', $id );
}
update_option( 'woocommerce_currency', 'GBP' ); update_option( 'woocommerce_default_country', 'GB' ); update_option( 'woocommerce_calc_taxes', 'no' );
update_option( 'woocommerce_cod_settings', array( 'enabled' => 'yes' ) ); // offline gateway only, so a local checkout can be exercised
update_option( 'blogname', 'PokePieceTCG (local fixture)' ); update_option( 'permalink_structure', '/%postname%/' );
update_option( 'woocommerce_permalinks', array( 'product_base' => '/product', 'category_base' => 'product-category' ) );
update_option( 'woocommerce_coming_soon', 'no' ); update_option( 'woocommerce_store_pages_only', 'no' );
update_option( 'woocommerce_ship_to_countries', 'specific' ); update_option( 'woocommerce_specific_ship_to_countries', array( 'GB' ) );

$img = (bool) getenv( 'PP_IMAGES' );
foreach ( array_reverse( $snap ) as $p ) {
	if ( get_post( $p['id'] ) ) { continue; }
	$id = wp_insert_post( array( 'import_id' => $p['id'], 'post_type' => 'product', 'post_status' => 'publish', 'post_title' => html_entity_decode( $p['name'] ), 'post_name' => $p['slug'], 'post_content' => 'FIXTURE description. Real product copy is merchant content.' ) );
	$prod = wc_get_product( $id ); if ( ! $prod ) { continue; }
	$prod->set_regular_price( number_format( $p['prices']['price'] / 100, 2, '.', '' ) );
	$prod->set_manage_stock( false ); $prod->set_stock_status( $p['is_in_stock'] ? 'instock' : 'outofstock' );
	// Public Store API omits category 18 (pokemon-japanese) from these products' `categories`, but ?category=18 lists them; mirror that observed membership.
	$cat18 = array( 782, 699, 686, 174, 172, 158 );
	$cats = array(); $slugs = array_column( $p['categories'], 'slug' );
	// Demo-only correction: his live data files #754 (a Pokémon Abyss Eye pack) under One Piece Japanese. Move it to Pokémon Japanese here; the real fix is in his WooCommerce admin.
	if ( 754 === (int) $p['id'] ) { $slugs = array( 'pokemon-japanese' ); } if ( in_array( $p['id'], $cat18, true ) ) { $slugs[] = 'pokemon-japanese'; }
	foreach ( array_map( fn( $s ) => array( 'slug' => $s ), array_unique( $slugs ) ) as $c ) { $t = get_term_by( 'slug', $c['slug'], 'product_cat' ); if ( $t ) { $cats[] = $t->term_id; } }
	$prod->set_category_ids( $cats ); $prod->save();
	update_post_meta( $id, '_pp_mode', pp_suggest_mode( $p['slug'] ) ); // fixture only: real merchant confirmation required
	if ( $img && ! empty( $p['images'][0]['src'] ) ) {
		$tmp = download_url( $p['images'][0]['src'] );
		if ( ! is_wp_error( $tmp ) ) { $aid = media_handle_sideload( array( 'name' => basename( parse_url( $p['images'][0]['src'], PHP_URL_PATH ) ), 'tmp_name' => $tmp ), $id ); if ( ! is_wp_error( $aid ) ) { set_post_thumbnail( $id, $aid ); } }
	}
}
update_option( 'pokepiece_service_product_ids', array( 686 ) );
if ( getenv( 'PP_INSTOCK_FIXTURE' ) ) {
	foreach ( array( array( 'FIXTURE, in-stock sealed box (not a real product)', 'sealed', 'box', 'pokemon-english', '1.00' ), array( 'FIXTURE, in-stock live rip example (not a real product)', 'live', 'pack', 'one-piece-english', '2.50' ) ) as [ $t, $m, $f, $c, $pr ] ) {
		$prod = new WC_Product_Simple(); $prod->set_name( $t ); $prod->set_regular_price( $pr ); $prod->set_stock_status( 'instock' ); $prod->set_category_ids( array( get_term_by( 'slug', $c, 'product_cat' )->term_id ) ); $prod->set_description( 'FIXTURE ONLY.' ); $id = $prod->save();
		update_post_meta( $id, '_pp_mode', $m ); update_post_meta( $id, '_pp_format', $f );
	}
}
wp_set_current_user( 0 ); switch_theme( 'pokepiece-editorial' ); flush_rewrite_rules();
WP_CLI::success( 'Seeded local fixture site.' );
