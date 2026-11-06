<?php
defined( 'ABSPATH' ) || exit;

/** Product meta box: fulfilment mode, format, condition note, service flag. Merchant data - never inferred silently. */
add_action( 'add_meta_boxes', function () {
	add_meta_box( 'pp_facts', __( 'PokePiece storefront facts', 'pokepiece' ), 'pp_facts_box', 'product', 'side', 'high' );
} );

function pp_suggest_mode( $title_or_slug ) {
	$s = strtolower( $title_or_slug );
	if ( str_contains( $s, 'live-rip' ) || str_contains( $s, 'live rip' ) ) { return 'live'; }
	if ( str_contains( $s, 'no-shrink' ) || str_contains( $s, 'no shrink' ) ) { return 'no_shrink'; }
	if ( str_contains( $s, 'sealed' ) ) { return 'sealed'; }
	return '';
}

function pp_facts_box( $post ) {
	wp_nonce_field( 'pp_facts', 'pp_facts_nonce' );
	$mode = get_post_meta( $post->ID, '_pp_mode', true );
	$fmt  = get_post_meta( $post->ID, '_pp_format', true );
	$cond = get_post_meta( $post->ID, '_pp_condition', true );
	$svc  = get_post_meta( $post->ID, '_pp_is_service', true );
	$sug  = pp_suggest_mode( $post->post_name . ' ' . $post->post_title );
	echo '<p><label for="pp_mode"><strong>' . esc_html__( 'Fulfilment mode', 'pokepiece' ) . '</strong></label><br><select name="pp_mode" id="pp_mode" style="width:100%"><option value="">' . esc_html__( 'Not set (no mode label shown)', 'pokepiece' ) . '</option>';
	foreach ( pp_modes() as $k => $m ) {
		printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $mode, $k, false ), esc_html( $m['label'] ) );
	}
	echo '</select></p>';
	if ( ! $mode && $sug ) {
		printf( '<p class="description">%s <strong>%s</strong>. %s</p>', esc_html__( 'The product title suggests:', 'pokepiece' ), esc_html( pp_modes()[ $sug ]['label'] ), esc_html__( 'Not applied automatically, confirm and select it above.', 'pokepiece' ) );
	}
	echo '<p><label for="pp_format"><strong>' . esc_html__( 'Format', 'pokepiece' ) . '</strong></label><br><select name="pp_format" id="pp_format" style="width:100%"><option value="">' . esc_html__( 'Not set', 'pokepiece' ) . '</option>';
	foreach ( pp_formats() as $k => $l ) {
		printf( '<option value="%s" %s>%s</option>', esc_attr( $k ), selected( $fmt, $k, false ), esc_html( $l ) );
	}
	echo '</select></p>';
	printf( '<p><label for="pp_condition"><strong>%s</strong></label><br><textarea name="pp_condition" id="pp_condition" rows="3" style="width:100%%" placeholder="%s">%s</textarea></p>',
		esc_html__( 'Shrink / condition note', 'pokepiece' ), esc_attr__( 'Only what is true for this exact product', 'pokepiece' ), esc_textarea( $cond ) );
	printf( '<p><label><input type="checkbox" name="pp_is_service" value="1" %s> %s</label><br><span class="description">%s</span></p>', checked( $svc, '1', false ),
		esc_html__( 'Service / add-on product', 'pokepiece' ), esc_html__( 'Excluded from hero, new-arrival, related and search-suggestion rows. Its direct product page and checkout flow are unchanged.', 'pokepiece' ) );
}

add_action( 'save_post_product', function ( $id ) {
	if ( ! isset( $_POST['pp_facts_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['pp_facts_nonce'] ) ), 'pp_facts' ) || ! current_user_can( 'edit_post', $id ) ) {
		return;
	}
	$mode = sanitize_key( wp_unslash( $_POST['pp_mode'] ?? '' ) );
	$fmt  = sanitize_key( wp_unslash( $_POST['pp_format'] ?? '' ) );
	update_post_meta( $id, '_pp_mode', isset( pp_modes()[ $mode ] ) ? $mode : '' );
	update_post_meta( $id, '_pp_format', isset( pp_formats()[ $fmt ] ) ? $fmt : '' );
	update_post_meta( $id, '_pp_condition', sanitize_textarea_field( wp_unslash( $_POST['pp_condition'] ?? '' ) ) );
	update_post_meta( $id, '_pp_is_service', empty( $_POST['pp_is_service'] ) ? '' : '1' );
} );

/** WP-CLI: `wp pokepiece suggest-modes` prints proposals from titles; it NEVER writes. `--apply-confirmed=file.csv` writes only rows the merchant confirmed (id,mode). */
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'pokepiece suggest-modes', function ( $args, $assoc ) {
		$rows = array();
		foreach ( wc_get_products( array( 'limit' => -1, 'status' => 'any' ) ) as $p ) {
			$rows[] = array( 'id' => $p->get_id(), 'title' => $p->get_name(), 'current' => $p->get_meta( '_pp_mode' ), 'suggested' => pp_suggest_mode( $p->get_slug() . ' ' . $p->get_name() ) );
		}
		WP_CLI\Utils\format_items( $assoc['format'] ?? 'table', $rows, array( 'id', 'title', 'current', 'suggested' ) );
	} );
	WP_CLI::add_command( 'pokepiece apply-modes', function ( $args, $assoc ) {
		$file = $assoc['file'] ?? '';
		if ( ! is_readable( $file ) ) { WP_CLI::error( 'Pass --file=confirmed.csv (id,mode).' ); }
		$h = fopen( $file, 'r' ); $n = 0;
		while ( ( $r = fgetcsv( $h ) ) !== false ) {
			if ( isset( $r[0], $r[1] ) && ctype_digit( $r[0] ) && isset( pp_modes()[ $r[1] ] ) ) { update_post_meta( (int) $r[0], '_pp_mode', $r[1] ); $n++; }
		}
		WP_CLI::success( "$n products updated from merchant-confirmed file." );
	} );
}
