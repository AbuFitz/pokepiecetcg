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

function pp_seo_description() {
	if ( is_front_page() ) { return __( 'PokePieceTCG is a UK based trading card store specialising in sealed Pokémon and One Piece TCG products: booster boxes, elite trainer boxes and collection boxes. Secure packaging and fast UK dispatch.', 'pokepiece' ); }
	if ( is_product_category() ) { $t = get_queried_object(); return wp_trim_words( wp_strip_all_tags( term_description( $t->term_id, 'product_cat' ) ?: sprintf( __( 'Shop %s sealed TCG products from PokePieceTCG, a UK based trading card store. Secure packaging and fast UK dispatch.', 'pokepiece' ), $t->name ) ), 30, '' ); }
	if ( is_singular( 'product' ) ) { $p = wc_get_product( get_the_ID() ); if ( $p ) { $d = wp_strip_all_tags( $p->get_short_description() ?: $p->get_description() ); return $d ? wp_trim_words( $d, 28, '…' ) : sprintf( __( '%1$s from PokePieceTCG, a UK based trading card store. Secure packaging and fast UK dispatch.', 'pokepiece' ), $p->get_name() ); } }
	if ( is_shop() ) { return __( 'Shop sealed Pokémon, One Piece and Dragon Ball TCG booster boxes and products from PokePieceTCG. Secure packaging and fast UK dispatch.', 'pokepiece' ); }
	return '';
}

add_filter( 'pre_get_document_title', function ( $t ) {
	if ( pp_seo_plugin_active() ) { return $t; }
	if ( is_front_page() ) { return __( 'PokePieceTCG | Pokémon & One Piece Booster Boxes UK', 'pokepiece' ); }
	return $t;
} );

add_action( 'wp_head', function () {
	if ( pp_seo_plugin_active() || is_404() ) { return; }
	$desc = pp_seo_description(); $title = wp_get_document_title();
	$url = is_singular() ? get_permalink() : ( is_tax() ? get_term_link( get_queried_object() ) : home_url( add_query_arg( null, null ) ) );
	if ( is_search() || is_cart() || is_checkout() || is_account_page() ) { return; }
	$img = PP_URI . '/assets/brand/icon-192.png';
	if ( is_singular( 'product' ) ) { $i = wp_get_attachment_image_url( get_post_thumbnail_id(), 'large' ); if ( $i ) { $img = $i; } }
	if ( $desc ) { echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n"; }
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '"><meta property="og:type" content="' . ( is_singular( 'product' ) ? 'product' : 'website' ) . '"><meta property="og:title" content="' . esc_attr( $title ) . '"><meta property="og:url" content="' . esc_url( $url ) . '"><meta property="og:image" content="' . esc_url( $img ) . '"><meta name="twitter:card" content="summary_large_image">' . "\n";
	if ( $desc ) { echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n"; }
}, 2 );

add_action( 'wp_head', function () {
	if ( pp_seo_plugin_active() || ! is_front_page() ) { return; }
	$org = array( '@type' => 'Organization', '@id' => home_url( '/#org' ), 'name' => 'PokePieceTCG', 'url' => home_url( '/' ), 'logo' => PP_URI . '/assets/brand/icon-192.png', 'email' => pp_contact_email(), 'areaServed' => 'GB', 'description' => 'UK based trading card store specialising in sealed Pokémon and One Piece TCG products.', 'sameAs' => array_values( array_filter( array( pp_social( 'tiktok' ), pp_social( 'discord' ) ) ) ) );
	$site = array( '@type' => 'WebSite', '@id' => home_url( '/#site' ), 'url' => home_url( '/' ), 'name' => 'PokePieceTCG', 'publisher' => array( '@id' => home_url( '/#org' ) ), 'potentialAction' => array( '@type' => 'SearchAction', 'target' => home_url( '/?s={search_term_string}&post_type=product' ), 'query-input' => 'required name=search_term_string' ) );
	$faq = array( '@type' => 'FAQPage', 'mainEntity' => array_map( fn( $f ) => array( '@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $f[1] ) ), pp_faqs() ) );
	echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => array( $org, $site, $faq ) ), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}, 3 );

// Category/Shop band: if the merchant has not written a description, show a short factual intro (crawlable text, no claims beyond the owner's wording).
add_filter( 'pp_band_description', function ( $desc ) {
	if ( $desc || ! is_product_category() ) { return $desc; }
	return '<p>' . esc_html( sprintf( __( 'Shop %s sealed TCG products from PokePieceTCG, a UK based trading card store. Secure packaging and fast UK dispatch.', 'pokepiece' ), single_term_title( '', false ) ) ) . '</p>';
} );
