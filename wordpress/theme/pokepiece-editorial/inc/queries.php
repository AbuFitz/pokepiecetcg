<?php
/** Server-rendered, URL-driven filters: ?pp_mode=live&pp_stock=1&pp_format=box (plus Woo's own orderby, paged, product_cat). */
defined( 'ABSPATH' ) || exit;

function pp_filter_args() {
	$mode = isset( $_GET['pp_mode'] ) ? sanitize_key( wp_unslash( $_GET['pp_mode'] ) ) : '';
	$fmt  = isset( $_GET['pp_format'] ) ? sanitize_key( wp_unslash( $_GET['pp_format'] ) ) : '';
	return array(
		'pp_mode'   => isset( pp_modes()[ $mode ] ) ? $mode : '',
		'pp_format' => isset( pp_formats()[ $fmt ] ) ? $fmt : '',
		'pp_stock'  => ! empty( $_GET['pp_stock'] ) ? '1' : '',
	);
}

add_action( 'woocommerce_product_query', function ( $q ) {
	$f = pp_filter_args();
	$meta = (array) $q->get( 'meta_query' );
	if ( $f['pp_mode'] ) { $meta[] = array( 'key' => '_pp_mode', 'value' => $f['pp_mode'] ); }
	if ( $f['pp_format'] ) { $meta[] = array( 'key' => '_pp_format', 'value' => $f['pp_format'] ); }
	if ( $f['pp_stock'] ) { $meta[] = array( 'key' => '_stock_status', 'value' => 'instock' ); }
	$q->set( 'meta_query', $meta );
} );

// Product search is products only; SKU matches too.
add_filter( 'pre_get_posts', function ( $q ) {
	if ( ! is_admin() && $q->is_main_query() && $q->is_search() && ! $q->get( 'post_type' ) ) {
		$q->set( 'post_type', 'product' );
	}
} );

/** Count of products per filter option for the current category, so impossible options can be disabled with accuracy. */
function pp_option_counts( $key, $value ) {
	$args = array( 'post_type' => 'product', 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => -1, 'no_found_rows' => true, 'post__not_in' => pp_service_ids(),
		'meta_query' => array( array( 'key' => $key, 'value' => $value ) ) );
	if ( is_tax( 'product_cat' ) ) {
		$args['tax_query'] = array( array( 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => get_queried_object_id() ) );
	}
	return count( get_posts( $args ) );
}

function pp_remove_arg_url( $keys ) {
	return esc_url( remove_query_arg( array_merge( (array) $keys, array( 'paged' ) ) ) );
}

function pp_applied_chips() {
	$f = pp_filter_args(); $chips = array();
	if ( $f['pp_mode'] ) { $chips[] = array( pp_modes()[ $f['pp_mode'] ]['label'], pp_remove_arg_url( 'pp_mode' ) ); }
	if ( $f['pp_format'] ) { $chips[] = array( pp_formats()[ $f['pp_format'] ], pp_remove_arg_url( 'pp_format' ) ); }
	if ( $f['pp_stock'] ) { $chips[] = array( __( 'In stock only', 'pokepiece' ), pp_remove_arg_url( 'pp_stock' ) ); }
	return $chips;
}

function pp_render_filters() {
	$f = pp_filter_args();
	$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( wp_unslash( $_GET['orderby'] ) ) : '';
	echo '<form class="pp-filters" method="get" action="' . esc_url( remove_query_arg( array( 'paged' ) ) ) . '" id="pp-filters">';
	foreach ( $_GET as $k => $v ) { // preserve search + sort; drop our own + paging
		if ( in_array( $k, array( 'pp_mode', 'pp_format', 'pp_stock', 'paged' ), true ) || is_array( $v ) ) { continue; }
		echo '<input type="hidden" name="' . esc_attr( $k ) . '" value="' . esc_attr( wp_unslash( $v ) ) . '">';
	}
	echo '<fieldset><legend>' . esc_html__( 'Fulfilment', 'pokepiece' ) . '</legend>';
	$any = false;
	foreach ( pp_modes() as $k => $m ) {
		$n = pp_option_counts( '_pp_mode', $k );
		if ( ! $n && $f['pp_mode'] !== $k ) { continue; }
		$any = true;
		printf( '<label class="pp-check"><input type="radio" name="pp_mode" value="%s" %s><span>%s <em>(%d)</em></span></label>', esc_attr( $k ), checked( $f['pp_mode'], $k, false ), esc_html( $m['label'] ), $n );
	}
	if ( $any ) { printf( '<label class="pp-check"><input type="radio" name="pp_mode" value="" %s><span>%s</span></label>', checked( $f['pp_mode'], '', false ), esc_html__( 'Any', 'pokepiece' ) ); }
	else { echo '<p class="pp-muted">' . esc_html__( 'Fulfilment labels have not been set on products yet.', 'pokepiece' ) . '</p>'; }
	echo '</fieldset>';
	$fmt_any = false; $h = '';
	foreach ( pp_formats() as $k => $l ) {
		$n = pp_option_counts( '_pp_format', $k );
		if ( ! $n && $f['pp_format'] !== $k ) { continue; }
		$fmt_any = true;
		$h .= sprintf( '<label class="pp-check"><input type="radio" name="pp_format" value="%s" %s><span>%s <em>(%d)</em></span></label>', esc_attr( $k ), checked( $f['pp_format'], $k, false ), esc_html( $l ), $n );
	}
	if ( $fmt_any ) { echo '<fieldset><legend>' . esc_html__( 'Format', 'pokepiece' ) . '</legend>' . $h . sprintf( '<label class="pp-check"><input type="radio" name="pp_format" value="" %s><span>%s</span></label>', checked( $f['pp_format'], '', false ), esc_html__( 'Any', 'pokepiece' ) ) . '</fieldset>'; }
	echo '<fieldset><legend>' . esc_html__( 'Availability', 'pokepiece' ) . '</legend><label class="pp-check"><input type="checkbox" name="pp_stock" value="1" ' . checked( $f['pp_stock'], '1', false ) . '><span>' . esc_html__( 'In stock only', 'pokepiece' ) . '</span></label></fieldset>';
	echo '<div class="pp-filters__actions"><button class="pp-btn pp-btn--primary" type="submit">' . esc_html__( 'Apply', 'pokepiece' ) . '</button><a class="pp-btn pp-btn--ghost" href="' . esc_url( remove_query_arg( array( 'pp_mode', 'pp_format', 'pp_stock', 'paged' ) ) ) . '">' . esc_html__( 'Clear all', 'pokepiece' ) . '</a></div></form>';
}

// Toolbar above results: count, filter trigger (phone), sort, applied chips.
add_action( 'woocommerce_before_shop_loop', function () {
	$total = wc_get_loop_prop( 'total' );
	$active = count( pp_applied_chips() );
	echo '<div class="pp-toolbar"><p class="pp-count" id="pp-count" role="status">' . esc_html( sprintf( _n( '%d product', '%d products', $total, 'pokepiece' ), $total ) ) . '</p>';
	echo '<button class="pp-btn pp-btn--ghost pp-filter-open" type="button" aria-controls="pp-filter-panel" aria-expanded="false">' . pp_icon( 'filter' ) . esc_html__( 'Filter', 'pokepiece' ) . ( $active ? ' <span class="pp-badge">' . (int) $active . '</span>' : '' ) . '</button>';
	woocommerce_catalog_ordering();
	echo '</div>';
	$chips = pp_applied_chips();
	if ( $chips ) {
		echo '<ul class="pp-chips" aria-label="' . esc_attr__( 'Applied filters', 'pokepiece' ) . '">';
		foreach ( $chips as [ $l, $u ] ) { echo '<li><a class="pp-chip" href="' . $u . '">' . esc_html( $l ) . ' <span class="screen-reader-text">' . esc_html__( 'remove filter', 'pokepiece' ) . '</span>' . pp_icon( 'close' ) . '</a></li>'; }
		echo '<li><a class="pp-chip pp-chip--clear" href="' . esc_url( remove_query_arg( array( 'pp_mode', 'pp_format', 'pp_stock', 'paged' ) ) ) . '">' . esc_html__( 'Clear all', 'pokepiece' ) . '</a></li></ul>';
	}
}, 20 );
