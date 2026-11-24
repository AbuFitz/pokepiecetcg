<?php
/**
 * LOCAL FIXTURE ONLY: awkward products for tests/e2e/resilience.cjs (no image, sale price, variable product, HTML/emoji/quotes in the title, a very long title,
 * sold out, no price, hidden from the catalogue, filed under several categories). Run: wp eval-file scripts/stress-fixture.php [cleanup]. Never run on a real shop.
 */
if ( ! defined( 'WP_CLI' ) ) { exit; }
require_once ABSPATH . 'wp-admin/includes/image.php'; require_once ABSPATH . 'wp-admin/includes/file.php'; require_once ABSPATH . 'wp-admin/includes/media.php';
$cleanup = isset( $GLOBALS['argv'] ) && in_array( 'cleanup', $GLOBALS['argv'], true );
foreach ( get_posts( array( 'post_type' => array( 'product', 'product_variation' ), 'post_status' => 'any', 'numberposts' => -1, 'meta_key' => '_pp_stress', 'meta_value' => '1' ) ) as $p ) { $a = get_post_thumbnail_id( $p->ID ); wp_delete_post( $p->ID, true ); if ( $a ) { wp_delete_attachment( $a, true ); } }
if ( $cleanup ) { WP_CLI::success( 'stress fixtures removed' ); return; }

$img = function ( $c ) { $im = imagecreatetruecolor( 800, 800 ); imagefill( $im, 0, 0, imagecolorallocate( $im, 255, 255, 255 ) ); imagefilledrectangle( $im, 160, 100, 640, 700, imagecolorallocate( $im, $c[0], $c[1], $c[2] ) ); $f = wp_tempnam( 's' ) . '.jpg'; imagejpeg( $im, $f, 85 ); imagedestroy( $im ); return media_handle_sideload( array( 'name' => 'stress-' . wp_rand() . '.jpg', 'tmp_name' => $f ), 0 ); };
$cats = array(); foreach ( array( 'pokemon-english', 'one-piece-english', 'pokemon-japanese' ) as $s ) { $t = get_term_by( 'slug', $s, 'product_cat' ); if ( $t ) { $cats[] = (int) $t->term_id; } }
$mk = function ( $name, $price, $opts = array() ) use ( $img, $cats ) {
	$p = new WC_Product_Simple(); $p->set_name( $name ); $p->set_status( 'publish' ); $p->set_regular_price( $price ); $p->set_stock_status( $opts['stock'] ?? 'instock' ); $p->set_category_ids( $opts['cats'] ?? array( $cats[0] ) );
	if ( isset( $opts['sale'] ) ) { $p->set_sale_price( $opts['sale'] ); } if ( ! empty( $opts['image'] ) ) { $p->set_image_id( $img( $opts['image'] ) ); } if ( isset( $opts['vis'] ) ) { $p->set_catalog_visibility( $opts['vis'] ); }
	$id = $p->save(); update_post_meta( $id, '_pp_stress', '1' ); return $id;
};
$mk( 'Stress no image', '12.50' );
$mk( 'Stress sale price', '100.00', array( 'sale' => '79.99', 'image' => array( 193, 35, 44 ) ) );
$mk( 'Stress sold out', '45.00', array( 'stock' => 'outofstock', 'image' => array( 36, 71, 179 ) ) );
$mk( 'Stress <b>html</b> & "quotes" \'single\' 🎴 émoji <script>alert(1)</script>', '9.99', array( 'image' => array( 244, 198, 65 ) ) );
$mk( 'Stress very long title ' . str_repeat( 'Pokémon Scarlet & Violet Booster Box Japanese Sealed Collection ', 3 ), '29.99', array( 'image' => array( 20, 20, 20 ) ) );
$mk( 'Stress no price', '', array( 'image' => array( 100, 100, 100 ) ) );
$mk( 'Stress hidden from catalogue', '15.00', array( 'vis' => 'hidden' ) );
$mk( 'Stress in three ranges', '33.00', array( 'cats' => $cats, 'image' => array( 232, 115, 42 ) ) );
// Variable product with two variations
$attr = new WC_Product_Attribute(); $attr->set_name( 'Language' ); $attr->set_options( array( 'English', 'Japanese' ) ); $attr->set_visible( true ); $attr->set_variation( true );
$v = new WC_Product_Variable(); $v->set_name( 'Stress variable language' ); $v->set_status( 'publish' ); $v->set_attributes( array( $attr ) ); $v->set_category_ids( array( $cats[0] ) ); $v->set_image_id( $img( array( 60, 150, 90 ) ) ); $vid = $v->save(); update_post_meta( $vid, '_pp_stress', '1' );
foreach ( array( 'English' => '40.00', 'Japanese' => '55.00' ) as $lang => $pr ) { $va = new WC_Product_Variation(); $va->set_parent_id( $vid ); $va->set_attributes( array( 'language' => $lang ) ); $va->set_regular_price( $pr ); $va->set_stock_status( 'instock' ); $va->save(); update_post_meta( $va->get_id(), '_pp_stress', '1' ); }
WC_Product_Variable::sync( $vid ); wc_delete_product_transients( $vid );
WP_CLI::success( 'stress fixtures created' );
