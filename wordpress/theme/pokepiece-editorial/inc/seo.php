<?php
/**
 * SEO layer. The live shop runs Rank Math (public REST namespaces). When Rank Math/Yoast is active they own titles, meta, canonicals, sitemaps and schema, and this file does nothing
 * except supply visible, crawlable content. Without one it supplies: title, meta description, Open Graph/Twitter, Organization + WebSite(SearchAction) + FAQPage JSON-LD.
 * Product/Offer/BreadcrumbList schema is WooCommerce's own and is never duplicated here.
 */
defined( 'ABSPATH' ) || exit;

function pp_seo_plugin_active() { return defined( 'RANK_MATH_VERSION' ) || defined( 'WPSEO_VERSION' ) || defined( 'AIOSEO_VERSION' ); }

/** Owner-published questions answered only with wording already on the existing site (shipping, returns, about, contact pages). */
function pp_faqs() {
	return apply_filters( 'pokepiece_faqs', array(
		array( __( 'Where does PokePieceTCG ship from?', 'pokepiece' ), __( 'We ship from the United Kingdom.', 'pokepiece' ) ),
		array( __( 'How quickly are orders dispatched?', 'pokepiece' ), pp_dispatch_text() . ' ' . __( 'Shipping times may vary depending on courier and location.', 'pokepiece' ) ),
		array( __( 'Are your products genuine?', 'pokepiece' ), __( 'We offer authentic sealed products sourced from trusted suppliers, including Japanese and English Pokémon and One Piece releases.', 'pokepiece' ) ),
		array( __( 'How are orders packed?', 'pokepiece' ), __( 'All items are packed securely to protect sealed trading card products during transit.', 'pokepiece' ) ),
		array( __( 'Can I return a product?', 'pokepiece' ), __( 'We accept returns only for unopened and unused items. Sealed trading card products must remain sealed to qualify for a return. Please read the full returns policy before ordering.', 'pokepiece' ) ),
		array( __( 'How do I contact PokePieceTCG?', 'pokepiece' ), sprintf( __( 'Email %s or use the contact form. We aim to respond within 24 to 48 hours.', 'pokepiece' ), pp_contact_email() ) ),
	) );
}

/** Owner-approved home wording (editable in Settings > PokePiece). */
function pp_seo_home_title() { return trim( (string) get_option( 'pokepiece_seo_home_title', '' ) ) ?: 'PokePieceTCG | Trading Cards & Collectibles UK'; }
function pp_seo_home_desc() { return trim( (string) get_option( 'pokepiece_seo_home_desc', '' ) ) ?: __( 'UK trading card store for Pokémon, One Piece, Dragon Ball and more. Sealed booster boxes and collector accessories, securely packed and dispatched fast.', 'pokepiece' ); }

/** True when a description is real copy rather than empty, a fixture or a placeholder. */
function pp_real_text( $t ) { $t = trim( wp_strip_all_tags( (string) $t ) ); return strlen( $t ) >= 40 && ! preg_match( '/^(fixture|lorem|placeholder|description coming|test)/i', $t ); }

/** Distinct, factual intro for a category with no owner-written description. Language and shrink wording come from his own product naming; nothing else is claimed. */
function pp_cat_intro( $term ) {
	$game = pp_game_of_slug( $term->slug ); $n = $term->name; $b = get_bloginfo( 'name' );
	if ( 'accessories' === $game ) { return sprintf( __( 'Collector accessories for trading cards from %s, a UK based trading card store. Secure packaging and fast UK dispatch.', 'pokepiece' ), $b ); }
	if ( str_ends_with( $term->slug, '-japanese' ) ) { return sprintf( __( 'Japanese-language %1$s booster boxes and sealed products from %2$s. Each listing states if it is shrink-wrapped. Packed securely, dispatched from the UK.', 'pokepiece' ), trim( preg_replace( '/\s*Japanese$/i', '', $n ) ), $b ); }
	if ( str_ends_with( $term->slug, '-english' ) ) { return sprintf( __( 'English-language %1$s booster boxes and sealed products from %2$s. Packed securely, dispatched from the UK.', 'pokepiece' ), trim( preg_replace( '/\s*English$/i', '', $n ) ), $b ); }
	return sprintf( __( 'Shop %1$s booster boxes and sealed trading card products from %2$s, a UK based trading card store. Secure packaging and fast UK dispatch.', 'pokepiece' ), $n, $b );
}

function pp_seo_cat_label( $term ) {
	$game = pp_game_of_slug( $term->slug );
	if ( 'accessories' === $game ) { return array( sprintf( /* translators: %s category */ __( '%s for Trading Cards UK', 'pokepiece' ), $term->name ), pp_cat_intro( $term ) ); }
	/* translators: %s category name */
	return array( sprintf( __( 'Buy %s Sealed Products UK', 'pokepiece' ), $term->name ), pp_cat_intro( $term ) );
}

function pp_seo_description() {
	if ( is_front_page() ) { return pp_seo_home_desc(); }
	if ( is_product_category() ) { $t = get_queried_object(); $d = wp_strip_all_tags( term_description( $t->term_id, 'product_cat' ) ); return pp_real_text( $d ) ? wp_trim_words( $d, 28, '…' ) : pp_seo_cat_label( $t )[1]; }
	if ( is_singular( 'product' ) ) {
		$p = wc_get_product( get_the_ID() ); if ( ! $p ) { return ''; }
		$d = wp_strip_all_tags( $p->get_short_description() ?: $p->get_description() );
		return pp_real_text( $d ) ? wp_trim_words( $d, 28, '…' ) : sprintf( /* translators: %s product name */ __( '%s from PokePieceTCG, a UK based trading card store. Secure packaging and fast UK dispatch.', 'pokepiece' ), $p->get_name() );
	}
	if ( is_shop() ) { return __( 'Shop sealed Pokémon, One Piece, Dragon Ball and other TCG booster boxes, products and collector accessories from PokePieceTCG. Secure packaging and fast UK dispatch.', 'pokepiece' ); }
	return '';
}

/** Title for pages where PokePiece sets it (home, shop, categories). Products and pages keep WordPress/SEO-plugin titles. */
function pp_seo_title() {
	if ( is_front_page() ) { return pp_seo_home_title(); }
	if ( is_singular( 'product' ) ) { $n = get_the_title(); $t = $n . ( preg_match( '/\bUK\b/i', $n ) ? '' : ' UK' ) . ' | ' . get_bloginfo( 'name' ); return strlen( $t ) <= 65 ? $t : ''; }
	if ( function_exists( 'is_shop' ) && is_shop() && ! is_search() ) { return __( 'Shop Trading Cards & Collectibles UK', 'pokepiece' ) . ' | ' . get_bloginfo( 'name' ); }
	if ( function_exists( 'is_product_category' ) && is_product_category() ) { return pp_seo_cat_label( get_queried_object() )[0] . ' | ' . get_bloginfo( 'name' ); }
	return '';
}

add_filter( 'document_title_separator', function () { return '|'; } );
add_filter( 'pre_get_document_title', function ( $t ) {
	if ( pp_seo_plugin_active() ) { return $t; }
	$o = pp_seo_title(); return $o ?: $t;
} );

/** URLs that are only a different view of the same listing (filter, sort, price): keep them out of the index, point them at the clean address. */
function pp_seo_is_param_view() {
	if ( ! ( is_shop() || is_product_taxonomy() ) ) { return false; }
	foreach ( array( 'pp_mode', 'pp_stock', 'pp_format', 'orderby', 'min_price', 'max_price', 'rating_filter' ) as $k ) { if ( isset( $_GET[ $k ] ) ) { return true; } } // phpcs:ignore WordPress.Security.NonceVerification
	foreach ( array_keys( $_GET ) as $k ) { if ( str_starts_with( (string) $k, 'filter_' ) || str_starts_with( (string) $k, 'query_type_' ) ) { return true; } } // phpcs:ignore
	return false;
}
function pp_seo_canonical_url() {
	if ( is_shop() ) { $u = get_permalink( wc_get_page_id( 'shop' ) ); }
	elseif ( is_product_taxonomy() ) { $u = get_term_link( get_queried_object() ); }
	else { return ''; }
	if ( is_wp_error( $u ) ) { return ''; }
	$paged = (int) get_query_var( 'paged' ); return $paged > 1 ? trailingslashit( $u ) . 'page/' . $paged . '/' : $u;
}
add_filter( 'wp_robots', function ( $r ) { if ( ! pp_seo_plugin_active() && pp_seo_is_param_view() ) { $r['noindex'] = true; $r['follow'] = true; } return $r; } );

// robots.txt: keep crawlers out of transactional pages and internal search. Filter/sort URLs are NOT blocked here: they carry noindex + a canonical (a blocked URL could never show its noindex).
add_filter( 'robots_txt', function ( $out ) {
	if ( pp_seo_plugin_active() ) { return $out; }
	$rules = "Disallow: /cart/\nDisallow: /checkout/\nDisallow: /my-account/\nDisallow: /*?s=\n";
	return preg_replace( '/^(User-agent: \*\R)/m', '$1' . $rules, $out, 1 ); // inside the group, so crawlers actually apply it; WordPress already appends the Sitemap line
} );
// Sitemap: transactional pages and unused tags do not belong in it.
add_filter( 'wp_sitemaps_posts_query_args', function ( $args, $type ) {
	if ( 'page' === $type && function_exists( 'wc_get_page_id' ) ) { $args['post__not_in'] = array_filter( array( wc_get_page_id( 'cart' ), wc_get_page_id( 'checkout' ), wc_get_page_id( 'myaccount' ) ) ); }
	return $args;
}, 10, 2 );
add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) { return 'users' === $name ? false : $provider; }, 10, 2 );
add_filter( 'wp_sitemaps_taxonomies', function ( $t ) { unset( $t['product_tag'], $t['product_shipping_class'] ); return $t; } );

// Rank Math (live site): it prints the tags; PokePiece only supplies the home / shop / category wording so the approved titles hold whichever tool is active.
add_filter( 'rank_math/frontend/title', function ( $t ) { $o = pp_seo_title(); return $o ?: $t; } );
add_filter( 'rank_math/frontend/description', function ( $d ) { return ( is_front_page() || is_shop() || is_product_category() ) && ! ( is_product_category() && pp_real_text( term_description() ) ) ? ( pp_seo_description() ?: $d ) : $d; } );
add_filter( 'rank_math/opengraph/facebook/og_title', function ( $t ) { $o = pp_seo_title(); return $o ?: $t; } );
add_filter( 'rank_math/opengraph/twitter/twitter_title', function ( $t ) { $o = pp_seo_title(); return $o ?: $t; } );
add_filter( 'rank_math/frontend/canonical', function ( $c ) { return pp_seo_is_param_view() ? ( pp_seo_canonical_url() ?: $c ) : $c; } );
add_filter( 'rank_math/frontend/robots', function ( $r ) { if ( pp_seo_is_param_view() ) { $r['index'] = 'noindex'; $r['follow'] = 'follow'; } return $r; } );

add_action( 'wp_head', function () {
	if ( pp_seo_plugin_active() || is_404() ) { return; }
	if ( is_search() || is_cart() || is_checkout() || is_account_page() ) { return; }
	$desc = pp_seo_description(); $title = wp_get_document_title();
	$canon = pp_seo_canonical_url();
	$url = is_front_page() ? home_url( '/' ) : ( $canon ?: ( is_singular() ? get_permalink() : home_url( add_query_arg( null, null ) ) ) );
	if ( $canon ) { echo '<link rel="canonical" href="' . esc_url( $canon ) . '">' . "\n"; } // singular pages get theirs from WordPress core
	$img = PP_URI . '/assets/brand/og-default.jpg'; $dims = array( 1200, 630 );
	if ( is_singular( 'product' ) ) { $i = wp_get_attachment_image_src( get_post_thumbnail_id(), 'large' ); if ( $i ) { $img = $i[0]; $dims = array( $i[1], $i[2] ); } }
	if ( $desc ) { echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n"; }
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '"><meta property="og:locale" content="' . esc_attr( str_replace( '-', '_', get_bloginfo( 'language' ) ) ) . '"><meta property="og:type" content="' . ( is_singular( 'product' ) ? 'product' : 'website' ) . '"><meta property="og:title" content="' . esc_attr( $title ) . '"><meta property="og:url" content="' . esc_url( $url ) . '"><meta property="og:image" content="' . esc_url( $img ) . '"><meta property="og:image:width" content="' . (int) $dims[0] . '"><meta property="og:image:height" content="' . (int) $dims[1] . '">' . "\n";
	echo '<meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="' . esc_attr( $title ) . '"><meta name="twitter:image" content="' . esc_url( $img ) . '">' . "\n";
	if ( $desc ) { echo '<meta property="og:description" content="' . esc_attr( $desc ) . '"><meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n"; }
}, 2 );

add_action( 'wp_head', function () {
	if ( pp_seo_plugin_active() || ! is_front_page() ) { return; }
	$org = array( '@type' => array( 'Organization', 'OnlineStore' ), '@id' => home_url( '/#org' ), 'name' => 'PokePieceTCG', 'url' => home_url( '/' ), 'logo' => PP_URI . '/assets/brand/icon-192.png', 'email' => pp_contact_email(), 'areaServed' => 'GB', 'description' => 'PokePieceTCG is a UK based trading card store for Pokémon, One Piece, Dragon Ball and other TCG booster boxes, sealed products, collector items and accessories. All products are authentic and sourced from trusted suppliers. Fast UK dispatch on all orders.', 'sameAs' => array_values( array_filter( array( pp_social( 'tiktok' ), pp_social( 'discord' ), esc_url( (string) get_option( 'pokepiece_google_profile', 'https://share.google/zSyE11bqZ6OTNXMwk' ) ) ) ) ) );
	$site = array( '@type' => 'WebSite', '@id' => home_url( '/#site' ), 'url' => home_url( '/' ), 'name' => 'PokePieceTCG', 'publisher' => array( '@id' => home_url( '/#org' ) ), 'potentialAction' => array( '@type' => 'SearchAction', 'target' => home_url( '/?s={search_term_string}&post_type=product' ), 'query-input' => 'required name=search_term_string' ) );
	$faq = array( '@type' => 'FAQPage', 'mainEntity' => array_map( fn( $f ) => array( '@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $f[1] ) ), pp_faqs() ) );
	echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => array( $org, $site, $faq ) ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}, 3 );

// Category/Shop band: if the merchant has not written a description, show a short factual intro (crawlable text, no claims beyond the owner's wording).
add_filter( 'pp_band_description', function ( $desc ) {
	if ( $desc || ! is_product_category() ) { return $desc; }
	return '<p>' . esc_html( pp_cat_intro( get_queried_object() ) ) . '</p>';
} );
