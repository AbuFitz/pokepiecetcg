<?php
defined( 'ABSPATH' ) || exit;

/** Fulfilment modes. Unknown ('') is a real state and is NEVER shown as sealed. */
function pp_modes() {
	return array(
		'sealed'    => array( 'label' => __( 'Delivered sealed', 'pokepiece' ), 'short' => __( 'Sealed', 'pokepiece' ) ),
		'no_shrink' => array( 'label' => __( 'No shrink', 'pokepiece' ), 'short' => __( 'No shrink', 'pokepiece' ) ),
		'live'      => array( 'label' => __( 'Opened live, cards shipped', 'pokepiece' ), 'short' => __( 'Live opening', 'pokepiece' ) ),
		'preorder'  => array( 'label' => __( 'Preorder', 'pokepiece' ), 'short' => __( 'Preorder', 'pokepiece' ) ),
	);
}

function pp_product_mode( $product ) {
	$product = wc_get_product( $product );
	if ( ! $product ) {
		return '';
	}
	$mode = (string) $product->get_meta( '_pp_mode', true );
	return isset( pp_modes()[ $mode ] ) ? $mode : '';
}

/** IDs of service/add-on products (shipping upgrade). Merchant-editable option; 686 verified from the public Store API. */
function pp_service_ids() {
	$ids = get_option( 'pokepiece_service_product_ids', array( 686 ) );
	$ids = array_filter( array_map( 'absint', (array) $ids ) );
	$flagged = get_posts( array( 'post_type' => 'product', 'fields' => 'ids', 'posts_per_page' => 50, 'meta_key' => '_pp_is_service', 'meta_value' => '1', 'no_found_rows' => true ) );
	return array_values( array_unique( array_merge( $ids, array_map( 'absint', $flagged ) ) ) );
}

function pp_is_service( $product_id ) {
	return in_array( (int) $product_id, pp_service_ids(), true );
}

/** Language written out, derived from the existing category slug suffix (-english / -japanese). */
function pp_product_language( $product ) {
	$product = wc_get_product( $product );
	if ( ! $product ) {
		return '';
	}
	foreach ( $product->get_category_ids() as $tid ) {
		$t = get_term( $tid, 'product_cat' );
		if ( $t && ! is_wp_error( $t ) ) {
			if ( str_ends_with( $t->slug, '-english' ) ) {
				return __( 'English', 'pokepiece' );
			}
			if ( str_ends_with( $t->slug, '-japanese' ) ) {
				return __( 'Japanese', 'pokepiece' );
			}
		}
	}
	return '';
}

function pp_product_format_label( $product ) {
	$f = (string) wc_get_product( $product )->get_meta( '_pp_format', true );
	$map = array( 'box' => __( 'Booster box', 'pokepiece' ), 'pack' => __( 'Booster pack', 'pokepiece' ), 'etb' => __( 'Elite Trainer Box', 'pokepiece' ), 'bundle' => __( 'Bundle', 'pokepiece' ), 'collection' => __( 'Collection', 'pokepiece' ), 'other' => __( 'Other', 'pokepiece' ) );
	return $map[ $f ] ?? '';
}

function pp_formats() {
	return array( 'box' => __( 'Booster box', 'pokepiece' ), 'pack' => __( 'Booster pack', 'pokepiece' ), 'etb' => __( 'Elite Trainer Box', 'pokepiece' ), 'bundle' => __( 'Bundle', 'pokepiece' ), 'collection' => __( 'Collection', 'pokepiece' ), 'other' => __( 'Other', 'pokepiece' ) );
}

/** Stock state as text, never colour alone. */
function pp_stock_state( $product ) {
	if ( $product->is_on_backorder() ) {
		return array( 'backorder', __( 'Available on backorder', 'pokepiece' ) );
	}
	if ( $product->is_in_stock() ) {
		return array( 'in', __( 'In stock', 'pokepiece' ) );
	}
	return array( 'out', __( 'Out of stock', 'pokepiece' ) );
}

/** Game → language categories, derived from real product_cat terms (no hard-coded IDs). */
function pp_game_groups() {
	static $cache = null; if ( null !== $cache ) { return $cache; }
	$games = array(
		'pokemon'     => array( 'name' => 'Pokémon', 'prefix' => 'pokemon-' ),
		'one-piece'   => array( 'name' => 'One Piece', 'prefix' => 'one-piece-' ),
		'dragon-ball' => array( 'name' => 'Dragon Ball', 'prefix' => 'dragon-ball-' ),
	);
	$terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
	if ( is_wp_error( $terms ) ) { return array(); }
	$out = array(); $used = array();
	// 1) The three existing games: categories named <game>-<language>.
	foreach ( $games as $key => $g ) {
		$items = array(); $slugs = array();
		foreach ( $terms as $t ) {
			if ( str_starts_with( $t->slug, $g['prefix'] ) ) {
				$items[] = array( 'name' => ucfirst( substr( $t->slug, strlen( $g['prefix'] ) ) ), 'url' => get_term_link( $t ), 'count' => pp_term_product_count( $t ), 'slug' => $t->slug );
				$slugs[] = $t->slug; $used[ $t->term_id ] = 1;
			}
		}
		if ( $items ) { usort( $items, fn( $a, $b ) => strcmp( $a['name'], $b['name'] ) ); $out[ $key ] = array( 'name' => $g['name'], 'items' => $items, 'slugs' => $slugs, 'kind' => 'game' ); }
	}
	// 2) Other TCGs and Accessories: ordinary top-level categories (children become the sub-menu).
	$special = array( 'other' => array( 'other-tcgs', 'other-tcg', 'other' ), 'accessories' => array( 'accessories', 'accessory' ) );
	$by_parent = array(); foreach ( $terms as $t ) { $by_parent[ $t->parent ][] = $t; }
	$build = function ( $root, $key, $kind ) use ( &$out, &$used, $by_parent ) {
		$kids = $by_parent[ $root->term_id ] ?? array(); $items = array(); $slugs = array( $root->slug ); $used[ $root->term_id ] = 1;
		if ( $kids ) { $items[] = array( 'name' => sprintf( __( 'All %s', 'pokepiece' ), $root->name ), 'url' => get_term_link( $root ), 'count' => pp_term_product_count( $root ), 'slug' => $root->slug ); }
		foreach ( $kids as $k ) { $items[] = array( 'name' => $k->name, 'url' => get_term_link( $k ), 'count' => pp_term_product_count( $k ), 'slug' => $k->slug ); $slugs[] = $k->slug; $used[ $k->term_id ] = 1; }
		$out[ $key ] = array( 'name' => $root->name, 'items' => $items, 'slugs' => $slugs, 'kind' => $kind, 'url' => get_term_link( $root ), 'count' => pp_term_product_count( $root ) );
	};
	foreach ( $special as $key => $slugs ) {
		foreach ( $terms as $t ) { if ( 0 === (int) $t->parent && in_array( $t->slug, $slugs, true ) ) { $build( $t, $key, $key ); break; } }
	}
	// 3) Any other top-level category the owner adds later appears automatically.
	foreach ( $by_parent[0] ?? array() as $t ) {
		if ( isset( $used[ $t->term_id ] ) || 'uncategorized' === $t->slug ) { continue; }
		$build( $t, 'cat-' . $t->term_id, 'extra' );
	}
	return $cache = $out;
}

/** Is the current category archive inside this nav group? */
function pp_group_active( $g, $cur_slug ) { return $cur_slug && in_array( $cur_slug, $g['slugs'] ?? array(), true ); }

function pp_dispatch_text() {
	// PROVISIONAL: wording published in the shipping policy (1 to 3 working days); owner must reconfirm.
	return apply_filters( 'pokepiece_dispatch_text', get_option( 'pokepiece_dispatch_text', __( 'Orders are usually dispatched within 1 to 3 working days unless stated otherwise.', 'pokepiece' ) ) );
}

/** Products for discovery rows: purchasable catalogue only, service products excluded. */
function pp_discovery_products( $args = array() ) {
	$args = wp_parse_args( $args, array(
		'status' => 'publish', 'limit' => 8, 'orderby' => 'date', 'order' => 'DESC',
		'exclude' => pp_service_ids(), 'visibility' => 'catalog', 'return' => 'objects',
	) );
	return wc_get_products( $args );
}

function pp_icon( $name ) {
	$p = array(
		'search' => '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 4.5 4.5"/>',
		'cart'   => '<path d="M3 4h2.4l2 10.2h9.4L19 7H6.2"/><circle cx="9" cy="19" r="1.3"/><circle cx="17" cy="19" r="1.3"/>',
		'user'   => '<circle cx="12" cy="8.5" r="3.6"/><path d="M5 20c.6-3.7 3.4-5.6 7-5.6s6.4 1.9 7 5.6"/>',
		'menu'   => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'  => '<path d="m6 6 12 12M18 6 6 18"/>',
		'chev'   => '<path d="m6 9 6 6 6-6"/>',
		'filter' => '<path d="M4 7h10M18 7h2M4 17h2M10 17h10"/><circle cx="16" cy="7" r="2"/><circle cx="8" cy="17" r="2"/>',
		'arrow'  => '<path d="M5 12h14m-5-5 5 5-5 5"/>',
	);
	return '<svg class="pp-i" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . ( $p[ $name ] ?? '' ) . '</svg>';
}

/** Owner-supplied social links (supplied by the project owner 6 Nov 2026). Overridable via options. */
function pp_social( $key ) {
	$d = array( 'tiktok' => 'https://www.tiktok.com/@pokepiecetcgg', 'discord' => 'https://discord.com/invite/54bNErEDb' );
	$v = trim( (string) get_option( 'pokepiece_' . $key . '_url', '' ) );
	return esc_url( $v ?: ( $d[ $key ] ?? '' ) );
}

/** Reviews copied verbatim from the shop's existing homepage (labelled there "Verified Vinted customer"). They are seller reviews from Vinted, NOT product verified-purchase ratings; no aggregate score is shown. Owner permission to reuse them is assumed from the owner's instruction and should be confirmed. */
function pp_testimonials() {
	return apply_filters( 'pokepiece_testimonials', array(
		array( 'q' => 'Great experience… really responsive great communication… fast shipment and well packaged no damage everything as it says it was really happy will 100% use in future too man 5 stars', 'n' => 'Sandro P.' ),
		array( 'q' => 'Very happy with my order, the seller is amazing and goes above and beyond for his orders. Great communication and product was very securely packaged! Highly recommend 😊', 'n' => 'Jack R.' ),
		array( 'q' => 'Great experience with this seller. went above and beyond and sent my parcel super quick and well packaged. would recommend to anyone interested in pokemon 🙂', 'n' => 'Ryan T.' ),
		array( 'q' => 'Excellent seller, friendly and great communication. Items were very well packaged. Would recommend and would buy from this seller again.', 'n' => 'noon626' ),
	) );
}

/** Payment methods shown in the footer, as brand marks. MUST be matched to the gateways actually enabled in WooCommerce before launch (filter `pokepiece_payment_methods`, return array() to hide the row). slug => array( label, brand colour ). */
function pp_payment_methods() {
	return apply_filters( 'pokepiece_payment_methods', array(
		'visa' => array( 'Visa', '#1A1F71' ), 'mastercard' => array( 'Mastercard', '#EB001B' ), 'americanexpress' => array( 'American Express', '#2E77BC' ),
		'applepay' => array( 'Apple Pay', '#000000' ), 'googlepay' => array( 'Google Pay', '#4285F4' ),
	) );
}
function pp_pay_icon( $slug, $colour ) {
	$f = PP_DIR . '/assets/img/pay/' . sanitize_file_name( $slug ) . '.svg';
	if ( ! is_readable( $f ) ) { return ''; }
	$svg = file_get_contents( $f );
	$svg = preg_replace( '#<title>.*?</title>#', '', $svg );
	return str_replace( '<svg ', '<svg fill="' . esc_attr( $colour ) . '" aria-hidden="true" focusable="false" ', $svg );
}

/** Published on the existing Contact page (owner's site). */
function pp_contact_email() { return sanitize_email( get_option( 'pokepiece_contact_email', 'info@pokepiecetcg.co.uk' ) ); }

/** About page imagery: the owner's own TikTok snapshots + real product photos (no stock imagery). */
function pp_about_gallery() {
	$prods = array_slice( array_values( array_filter( pp_discovery_products( array( 'limit' => 12, 'stock_status' => 'instock' ) ) ?: pp_discovery_products( array( 'limit' => 12 ) ), fn( $p ) => $p->get_image_id() ) ), 0, 4 );
	if ( $prods ) {
		echo '<section class="pp-about-gal" aria-labelledby="pp-ag2"><h2 id="pp-ag2">' . esc_html__( 'In the shop', 'pokepiece' ) . '</h2><ul class="pp-about-gal__grid pp-about-gal__prod">';
		foreach ( $prods as $p ) { echo '<li><a href="' . esc_url( $p->get_permalink() ) . '" aria-label="' . esc_attr( $p->get_name() ) . '">' . $p->get_image( 'pp-plate', array( 'alt' => $p->get_name(), 'loading' => 'lazy' ) ) . '</a></li>'; }
		echo '</ul></section>';
	}
}

/** Popup search: products first (in stock first), never the service product. */
function pp_search_featured( $n = 8 ) {
	$in = pp_discovery_products( array( 'stock_status' => 'instock', 'limit' => $n ) );
	$out = count( $in ) < $n ? pp_discovery_products( array( 'limit' => $n * 2 ) ) : array();
	$have = wp_list_pluck( $in, 'id' ); $list = $in;
	foreach ( $out as $p ) { if ( count( $list ) >= $n ) { break; } if ( ! in_array( $p->get_id(), $have, true ) && $p->get_image_id() ) { $list[] = $p; } }
	return array_filter( $list, fn( $p ) => $p->get_image_id() );
}
function pp_search_tile( $p ) {
	[ $k, $t ] = pp_stock_state( $p ); $mode = pp_product_mode( $p );
	echo '<li><a class="pp-sp__tile" href="' . esc_url( $p->get_permalink() ) . '"><span class="pp-sp__img">' . $p->get_image( 'pp-plate', array( 'alt' => '', 'loading' => 'lazy' ) ) . ( $mode ? '<span class="pp-mode pp-mode--' . esc_attr( $mode ) . '">' . esc_html( pp_modes()[ $mode ]['short'] ) . '</span>' : '' ) . '</span><span class="pp-sp__t">' . esc_html( $p->get_name() ) . '</span><span class="pp-sp__p">' . wp_kses_post( $p->get_price_html() ) . '</span><span class="pp-stock pp-stock--' . esc_attr( $k ) . '"><span class="pp-stock__dot" aria-hidden="true"></span>' . esc_html( $t ) . '</span></a></li>';
}

/** Published, visible products in a category, excluding service/add-on products. */
function pp_term_product_count( $t ) {
	$ids = get_posts( array( 'post_type' => 'product', 'post_status' => 'publish', 'fields' => 'ids', 'posts_per_page' => -1, 'no_found_rows' => true, 'post__not_in' => pp_service_ids(), 'tax_query' => array( array( 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $t->term_id ) ) ) );
	return count( $ids );
}

/** Rotating banner: array( text, url ). Only wording the owner already publishes. Filter `pokepiece_banner_messages` to edit. */
function pp_banner_messages() {
	$svc = pp_service_ids(); $svc_url = ( $svc && 'publish' === get_post_status( $svc[0] ) ) ? get_permalink( $svc[0] ) : wc_get_page_permalink( 'shop' );
	$page = fn( $slug, $fallback ) => ( $p = get_page_by_path( $slug ) ) ? get_permalink( $p ) : $fallback;
	return apply_filters( 'pokepiece_banner_messages', array(
		array( __( 'Special Delivery available', 'pokepiece' ), $svc_url ),
		array( __( 'Fast UK dispatch on every order', 'pokepiece' ), $page( 'shipping-policy', home_url( '/' ) ) ),
		array( __( 'Authentic sealed trading card products', 'pokepiece' ), $page( 'about', home_url( '/' ) ) ),
		array( __( 'Live TCG Rip & Ship on TikTok @pokepiecetcgg', 'pokepiece' ), pp_social( 'tiktok' ) ),
		array( __( 'Secure packaging to protect sealed condition', 'pokepiece' ), $page( 'shipping-policy', home_url( '/' ) ) ),
		array( __( 'Join the PokePieceTCG Discord community', 'pokepiece' ), pp_social( 'discord' ) ),
	) );
}

/** Flat list of category links (label, url) for search chips, 404 and empty-cart helpers. */
function pp_category_links() {
	$o = array();
	foreach ( pp_game_groups() as $g ) {
		if ( empty( $g['items'] ) ) { $o[] = array( $g['name'], $g['url'] ); continue; }
		foreach ( $g['items'] as $it ) { $o[] = array( 'game' === $g['kind'] ? $g['name'] . ' ' . $it['name'] : ( str_starts_with( $it['name'], 'All ' ) ? $it['name'] : $it['name'] ), $it['url'] ); }
	}
	return $o;
}

/** Hero wording (owner-editable in Settings > PokePiece). Default reflects the wider range. */
function pp_hero_text() {
	$v = trim( (string) get_option( 'pokepiece_hero_text', '' ) );
	return $v ?: __( 'An online trading card store for Pokémon, One Piece, Dragon Ball and more, plus collector accessories.', 'pokepiece' );
}
/** Hero banner image: owner picks one from the Media Library; falls back to the bundled artwork. */
function pp_hero_image() {
	$id = (int) get_option( 'pokepiece_hero_image_id', 0 );
	$alt = trim( (string) get_option( 'pokepiece_hero_image_alt', '' ) );
	if ( $id && wp_attachment_is_image( $id ) ) {
		return wp_get_attachment_image( $id, 'full', false, array( 'class' => 'pp-hero__art', 'alt' => $alt, 'fetchpriority' => 'high', 'decoding' => 'async', 'sizes' => '(min-width: 900px) 56vw, 92vw' ) );
	}
	return '<img class="pp-hero__art" src="' . esc_url( PP_URI . '/assets/img/hero-collection.webp' ) . '" srcset="' . esc_url( PP_URI . '/assets/img/hero-collection-768.webp' ) . ' 768w, ' . esc_url( PP_URI . '/assets/img/hero-collection.webp' ) . ' 1536w" sizes="(min-width: 900px) 56vw, 92vw" width="1536" height="1024" alt="' . esc_attr( $alt ) . '" fetchpriority="high" decoding="async">';
}
