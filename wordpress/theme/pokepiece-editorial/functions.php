<?php
/**
 * PokePieceTCG theme bootstrap. Prefer hooks to template copies; see docs/redesign/04.
 */
defined( 'ABSPATH' ) || exit;

define( 'PP_VERSION', '0.2.0' );
define( 'PP_DIR', get_template_directory() );
define( 'PP_URI', get_template_directory_uri() );

foreach ( array( 'helpers', 'art', 'setup', 'admin', 'woo', 'queries', 'rest', 'cart', 'newsletter', 'social', 'account', 'seo' ) as $pp_part ) {
	require_once PP_DIR . "/inc/{$pp_part}.php";
}
