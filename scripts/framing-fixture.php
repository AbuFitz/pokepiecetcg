<?php
/**
 * LOCAL FIXTURE ONLY: creates six throwaway products with awkward photo shapes (wide, tall, tiny, transparent, dark, cream) in the category
 * "Framing test", for tests/e2e/framing.cjs. Run: wp eval-file scripts/framing-fixture.php [cleanup]. Never run on a real shop.
 */
if ( ! defined( 'WP_CLI' ) ) { exit; }
require_once ABSPATH . 'wp-admin/includes/image.php'; require_once ABSPATH . 'wp-admin/includes/file.php'; require_once ABSPATH . 'wp-admin/includes/media.php';
$cleanup = in_array( 'cleanup', $args ?? array(), true ) || ( isset( $GLOBALS['argv'] ) && in_array( 'cleanup', $GLOBALS['argv'], true ) );
$names = array( 'Frame wide', 'Frame tall', 'Frame tiny', 'Frame transparent', 'Frame dark', 'Frame offwhite' );
foreach ( $names as $n ) { foreach ( get_posts( array( 'post_type' => 'product', 'title' => $n, 'numberposts' => -1, 'post_status' => 'any' ) ) as $p ) { $aid = get_post_thumbnail_id( $p->ID ); wp_delete_post( $p->ID, true ); if ( $aid ) { wp_delete_attachment( $aid, true ); } } }
$term = get_term_by( 'slug', 'framing-test', 'product_cat' );
if ( $cleanup ) { if ( $term ) { wp_delete_term( $term->term_id, 'product_cat' ); } WP_CLI::success( 'framing fixtures removed' ); return; }
$t = $term ? array( 'term_id' => $term->term_id ) : wp_insert_term( 'Framing test', 'product_cat', array( 'slug' => 'framing-test' ) );
$spec = array( // name => [w, h, bg rgb|null (transparent), box rgb]
	'Frame wide' => array( 1600, 500, array( 255, 255, 255 ), array( 193, 35, 44 ) ), 'Frame tall' => array( 500, 1500, array( 255, 255, 255 ), array( 36, 71, 179 ) ),
	'Frame tiny' => array( 180, 180, array( 255, 255, 255 ), array( 244, 198, 65 ) ), 'Frame transparent' => array( 800, 1000, null, array( 232, 115, 42 ) ),
	'Frame dark' => array( 1000, 1000, array( 15, 15, 15 ), array( 244, 198, 65 ) ), 'Frame offwhite' => array( 1200, 1200, array( 242, 239, 230 ), array( 193, 35, 44 ) ),
);
foreach ( $spec as $name => $s ) {
	list( $w, $h, $bg, $fg ) = $s; $im = imagecreatetruecolor( $w, $h ); imagesavealpha( $im, true ); imagealphablending( $im, false );
	imagefill( $im, 0, 0, $bg ? imagecolorallocate( $im, ...$bg ) : imagecolorallocatealpha( $im, 0, 0, 0, 127 ) ); imagealphablending( $im, true );
	$m = (int) ( min( $w, $h ) * 0.12 ); imagefilledrectangle( $im, $m, $m, $w - $m, $h - $m, imagecolorallocate( $im, ...$fg ) );
	$file = wp_tempnam( 'frame' ); $png = null === $bg; $path = $file . ( $png ? '.png' : '.jpg' ); $png ? imagepng( $im, $path ) : imagejpeg( $im, $path, 88 ); imagedestroy( $im );
	$aid = media_handle_sideload( array( 'name' => sanitize_title( $name ) . ( $png ? '.png' : '.jpg' ), 'tmp_name' => $path ), 0 );
	$p = new WC_Product_Simple(); $p->set_name( $name ); $p->set_regular_price( '9.99' ); $p->set_stock_status( 'instock' ); $p->set_image_id( $aid ); $p->set_category_ids( array( (int) $t['term_id'] ) ); $p->set_status( 'publish' ); $p->save();
}
WP_CLI::success( 'framing fixtures created' );
