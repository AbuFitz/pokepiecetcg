<?php
/** Read-only public endpoints: predictive search and quick view. Output is escaped; no admin credentials, no writes. */
defined( 'ABSPATH' ) || exit;

add_action( 'rest_api_init', function () {
	register_rest_route( 'pokepiece/v1', '/search', array(
		'methods' => 'GET', 'permission_callback' => '__return_true',
		'args' => array( 'q' => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ) ),
		'callback' => function ( WP_REST_Request $r ) {
			$q = trim( (string) $r['q'] );
			if ( mb_strlen( $q ) < 2 ) { return array( 'items' => array(), 'total' => 0 ); }
			$ids = array();
			// Title / content search, plus exact-ish SKU match; also tolerate OP14 vs OP-14.
			$variants = array_unique( array( $q, str_replace( '-', '', $q ), preg_replace( '/([a-z]+)(\d)/i', '$1-$2', $q ) ) );
			foreach ( $variants as $v ) {
				$res = new WP_Query( array( 'post_type' => 'product', 'post_status' => 'publish', 's' => $v, 'fields' => 'ids', 'posts_per_page' => 12, 'no_found_rows' => true, 'post__not_in' => pp_service_ids() ) );
				$ids = array_merge( $ids, $res->posts );
			}
			$sku = wc_get_product_id_by_sku( $q );
			if ( $sku && ! pp_is_service( $sku ) ) { array_unshift( $ids, $sku ); }
			$ids = array_slice( array_values( array_unique( $ids ) ), 0, 12 );
			$total = count( $ids );
			$items = array();
			foreach ( array_slice( $ids, 0, 8 ) as $id ) {
				$p = wc_get_product( $id ); if ( ! $p || ! $p->is_visible() ) { continue; }
				[ $k, $t ] = pp_stock_state( $p );
				$mode = pp_product_mode( $p );
				$items[] = array( 'id' => $id, 'title' => $p->get_name(), 'url' => get_permalink( $id ), 'img' => wp_get_attachment_image_url( $p->get_image_id(), 'pp-plate' ) ?: wp_get_attachment_image_url( $p->get_image_id(), 'medium' ) ?: '',
					'price' => wp_strip_all_tags( html_entity_decode( $p->get_price_html() ) ), 'stock' => $t, 'stockKey' => $k, 'mode' => $mode ? pp_modes()[ $mode ]['short'] : '', 'modeKey' => $mode );
			}
			return array( 'items' => $items, 'total' => $total, 'all' => esc_url_raw( add_query_arg( array( 's' => $q, 'post_type' => 'product' ), home_url( '/' ) ) ) );
		},
	) );
	register_rest_route( 'pokepiece/v1', '/quickview/(?P<id>\d+)', array(
		'methods' => 'GET', 'permission_callback' => '__return_true',
		'callback' => function ( WP_REST_Request $r ) {
			$p = wc_get_product( absint( $r['id'] ) );
			if ( ! $p || ! $p->is_visible() ) { return new WP_Error( 'pp_nf', 'Not found', array( 'status' => 404 ) ); }
			ob_start(); get_template_part( 'template-parts/quickview', null, array( 'product' => $p ) );
			return array( 'html' => ob_get_clean() );
		},
	) );
} );
