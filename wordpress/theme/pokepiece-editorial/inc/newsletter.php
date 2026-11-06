<?php
/**
 * Newsletter sign-up. DISABLED unless a real provider handler is attached:
 *   add_filter( 'pokepiece_newsletter_subscribe', function ( $result, $email, $consent ) { ...call provider...; return true|WP_Error; }, 10, 3 );
 * Without a handler the UI is not rendered and the endpoint answers 501. No fake success, ever.
 */
defined( 'ABSPATH' ) || exit;

function pp_newsletter_enabled() { return (bool) has_filter( 'pokepiece_newsletter_subscribe' ); }

add_action( 'rest_api_init', function () {
	register_rest_route( 'pokepiece/v1', '/newsletter', array(
		'methods' => 'POST', 'permission_callback' => '__return_true',
		'callback' => function ( WP_REST_Request $r ) {
			if ( ! pp_newsletter_enabled() ) { return new WP_Error( 'pp_nl_off', __( 'Newsletter sign-up is not connected.', 'pokepiece' ), array( 'status' => 501 ) ); }
			$email = sanitize_email( (string) $r['email'] );
			if ( ! is_email( $email ) ) { return new WP_Error( 'pp_nl_email', __( 'Enter a valid email address.', 'pokepiece' ), array( 'status' => 422 ) ); }
			if ( empty( $r['consent'] ) ) { return new WP_Error( 'pp_nl_consent', __( 'Please tick the box to agree to receive emails.', 'pokepiece' ), array( 'status' => 422 ) ); }
			if ( ! empty( $r['website'] ) ) { return array( 'status' => 'ok' ); } // honeypot, silent
			$res = apply_filters( 'pokepiece_newsletter_subscribe', null, $email, true );
			if ( is_wp_error( $res ) ) { return new WP_Error( $res->get_error_code(), $res->get_error_message(), array( 'status' => 502 ) ); }
			if ( true !== $res ) { return new WP_Error( 'pp_nl_fail', __( 'Sign-up failed. Please try again later.', 'pokepiece' ), array( 'status' => 502 ) ); }
			return array( 'status' => 'ok', 'message' => __( 'Thanks — check your inbox if the provider asks you to confirm.', 'pokepiece' ) );
		},
	) );
} );
