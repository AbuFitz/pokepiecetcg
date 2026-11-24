<?php
/**
 * Low-risk site hardening that a shop should always have. Everything here is invisible to normal visitors and safe with payment gateways (no
 * Content-Security-Policy and no Permissions-Policy are set, because those can break card payment frames). Turn the whole module off with:
 *   add_filter( 'ppc_hardening', '__return_false' );
 */
defined( 'ABSPATH' ) || exit;
if ( ! apply_filters( 'ppc_hardening', true ) ) { return; }

// Basic browser protections on public pages: no content-type guessing, no framing of the shop by other sites, sensible referrer, and no PHP version in headers.
add_action( 'send_headers', function () {
	if ( is_admin() ) { return; }
	header( 'X-Content-Type-Options: nosniff' ); header( 'X-Frame-Options: SAMEORIGIN' ); header( 'Referrer-Policy: strict-origin-when-cross-origin' ); header_remove( 'X-Powered-By' );
} );

// Do not announce exact WordPress / WooCommerce versions.
add_filter( 'the_generator', '__return_empty_string' );
remove_action( 'wp_head', 'wp_generator' );
remove_filter( 'get_the_generator_html', 'wc_generator_tag', 10 );

// Username enumeration. Anonymous visitors could list the administrator's login name through the REST API (/wp-json/wp/v2/users) and through ?author=1, which
// makes guessing passwords much easier. A shop has no public authors, so both are closed to anyone not signed in.
add_filter( 'rest_endpoints', function ( $e ) { if ( ! is_user_logged_in() ) { foreach ( array_keys( $e ) as $r ) { if ( preg_match( '#^/wp/v2/users#', $r ) ) { unset( $e[ $r ] ); } } } return $e; } );
add_action( 'template_redirect', function () { if ( is_author() && ! is_user_logged_in() ) { wp_safe_redirect( home_url( '/' ), 301 ); exit; } }, 1 );
add_filter( 'wp_sitemaps_add_provider', function ( $p, $name ) { return 'users' === $name ? false : $p; }, 10, 2 );

// The WordPress login page says whether the username or the password was wrong. One answer for both.
add_filter( 'login_errors', function ( $m ) { return __( '<strong>Error:</strong> The username or password is not correct.', 'pokepiece-core' ); } );
