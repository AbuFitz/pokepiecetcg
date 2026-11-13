<?php
defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'pokepiece', PP_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'woocommerce', array(
		'thumbnail_image_width' => 480, 'single_image_width' => 900,
		'product_grid' => array( 'default_columns' => 4, 'min_columns' => 2, 'max_columns' => 4 ),
	) );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'responsive-embeds' );
	add_image_size( 'pp-plate', 640, 640, false ); // never hard-cropped: any shape of photo is shown whole inside the square frame
	add_image_size( 'pp-thumb', 240, 240, false );
	register_nav_menus( array( 'footer' => __( 'Footer links', 'pokepiece' ), 'utility' => __( 'Header utility links', 'pokepiece' ) ) );
} );

add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'pokepiece', PP_URI . '/assets/css/main.css', array(), filemtime( PP_DIR . '/assets/css/main.css' ) );
	wp_enqueue_script( 'pokepiece', PP_URI . '/assets/js/main.js', array(), filemtime( PP_DIR . '/assets/js/main.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );
	if ( class_exists( 'WooCommerce' ) ) {
		wp_enqueue_script( 'wc-cart-fragments' );
	}
	wp_localize_script( 'pokepiece', 'PP', array(
		'rest'  => esc_url_raw( rest_url( 'pokepiece/v1/' ) ),
		'ajax'  => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'pp_cart' ), 'accNonce' => wp_create_nonce( 'pp_account' ),
		'newsletter' => pp_newsletter_enabled(),
		'i18n'  => array(
			'searching' => __( 'Searching…', 'pokepiece' ), 'noMatch' => __( 'No products match that search.', 'pokepiece' ),
			'searchFail' => __( 'Live search is unavailable. Press Enter to see full results.', 'pokepiece' ),
			'cartFail' => __( 'The cart could not be updated. Nothing was changed, please try again.', 'pokepiece' ),
			'nlFail' => __( 'Sign-up is not available right now.', 'pokepiece' ), 'loading' => __( 'Loading…', 'pokepiece' ),
		),
	) );
} );

// Preload only the faces used above the fold. Fonts are self-hosted WOFF2 (SIL OFL, licences in assets/fonts).
add_action( 'wp_head', function () {
	foreach ( array( 'archivo-latin-wdth-normal.woff2' ) as $f ) {
		printf( '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n", esc_url( PP_URI . '/assets/fonts/' . $f ) );
	}
	if ( has_site_icon() ) {
		return;
	}
	printf( '<link rel="icon" href="%s" sizes="any">' . "\n" . '<link rel="icon" href="%s" type="image/svg+xml">' . "\n" . '<link rel="apple-touch-icon" href="%s">' . "\n",
		esc_url( PP_URI . '/assets/brand/favicon.ico' ), esc_url( PP_URI . '/assets/brand/favicon.svg' ), esc_url( PP_URI . '/assets/brand/apple-touch-icon.png' ) );
}, 1 );

// Viewport: allow zoom (the current live site disables it).
add_action( 'wp_head', function () {
	echo '<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">' . "\n";
	echo '<meta name="theme-color" content="' . esc_attr( pp_top_colour() ) . '">' . "\n";
}, 0 );

// Staging must never be indexed. Define PP_STAGING in wp-config.php on staging only. (robots is not access control, protect staging too.)
add_filter( 'wp_robots', function ( $r ) {
	if ( defined( 'PP_STAGING' ) && PP_STAGING ) {
		$r['noindex'] = true; $r['nofollow'] = true; unset( $r['max-image-preview'] );
	}
	return $r;
}, 99 );

add_filter( 'body_class', function ( $c ) {
	$c[] = 'pp';
	$bar = 'ink';
	if ( is_front_page() ) { $bar = 'red'; }
	elseif ( function_exists( 'is_product_category' ) && is_product_category() ) { $g = pp_game_of_slug( get_queried_object()->slug ); $bar = array( 'pokemon' => 'yellow', 'one-piece' => 'blue', 'dragon-ball' => 'orange', 'other' => 'ink', 'accessories' => 'red' )[ $g ] ?? 'ink'; }
	$c[] = 'pp-bar-' . $bar;
	return $c;
} );

// Reduce core bloat that is not used by this theme (emoji scripts).
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );

// Woo's float-based layout CSS is replaced by this theme's grid. woocommerce-general stays for cart/checkout/account form basics; main.css overrides it.
add_filter( 'woocommerce_enqueue_styles', function ( $s ) {
	unset( $s['woocommerce-layout'], $s['woocommerce-smallscreen'] );
	return $s;
} );

/** Colour of the area behind the phone status bar / notch: matches the top of the page (hero colour). */
function pp_top_colour() {
	$map = array( 'red' => '#c1232c', 'blue' => '#2447b3', 'yellow' => '#f4c641', 'orange' => '#e8732a', 'ink' => '#14171a' );
	foreach ( get_body_class() as $c ) { if ( str_starts_with( $c, 'pp-bar-' ) ) { return $map[ substr( $c, 7 ) ] ?? $map['ink']; } }
	return $map['ink'];
}

// Product framing: WooCommerce hard-crops its thumbnail sizes to 1:1 by default, which cuts the top/bottom off tall box photos. Keep every size uncropped so
// photos of any shape reach the browser whole; CSS (object-fit: contain) then frames them inside the square plate. Existing uploads need one thumbnail
// regeneration after install (wp media regenerate --yes) because WordPress only re-cuts sizes when asked.
foreach ( array( 'thumbnail', 'gallery_thumbnail', 'single' ) as $pp_sz ) {
	add_filter( 'woocommerce_get_image_size_' . $pp_sz, function ( $size ) { $size['crop'] = 0; return $size; } );
}
