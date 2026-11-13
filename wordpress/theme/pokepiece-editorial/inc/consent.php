<?php
/**
 * Cookie prompt + preferences, and the optional promo popup.
 *
 * Consent is stored in one first-party cookie, `pp_consent` (JSON, 6 months, SameSite=Lax): {"v":1,"p":0|1,"a":0|1,"m":0|1,"t":unix}. Categories: strictly
 * necessary (always on: basket, checkout, login), preferences (remembering choices such as a closed offer), analytics, marketing.
 * Nothing optional is loaded by the theme itself. Anything added later (analytics, a pixel) must be written as
 *     <script type="text/plain" data-pp-consent="analytics" src="…"></script>
 * and the prompt activates it only after that category is accepted. PHP can ask pp_consent_allows( 'analytics' ).
 * This is a technical consent mechanism. The wording and the cookie list must be reviewed against what the live site really sets before launch.
 */
defined( 'ABSPATH' ) || exit;

function pp_cookie_prompt_on() { return 'no' !== get_option( 'pokepiece_cookie_prompt', 'yes' ); }

function pp_consent_allows( $cat ) {
	if ( 'necessary' === $cat ) { return true; }
	if ( empty( $_COOKIE['pp_consent'] ) ) { return false; }
	$c = json_decode( wp_unslash( $_COOKIE['pp_consent'] ), true );
	$k = array( 'preferences' => 'p', 'analytics' => 'a', 'marketing' => 'm' );
	return is_array( $c ) && isset( $k[ $cat ] ) && ! empty( $c[ $k[ $cat ] ] );
}

/** Promo popup config from Settings > PokePiece. Returns null unless it is switched on and has something to say. */
function pp_promo() {
	if ( 'yes' !== get_option( 'pokepiece_promo_on', 'no' ) ) { return null; }
	$p = array(
		'title' => trim( (string) get_option( 'pokepiece_promo_title', '' ) ), 'text' => trim( (string) get_option( 'pokepiece_promo_text', '' ) ),
		'code' => trim( (string) get_option( 'pokepiece_promo_code', '' ) ), 'label' => trim( (string) get_option( 'pokepiece_promo_label', '' ) ),
		'url' => trim( (string) get_option( 'pokepiece_promo_url', '' ) ), 'delay' => max( 2, min( 60, (int) get_option( 'pokepiece_promo_delay', 8 ) ) ),
		'days' => max( 1, min( 90, (int) get_option( 'pokepiece_promo_days', 7 ) ) ), 'where' => 'home' === get_option( 'pokepiece_promo_where', 'all' ) ? 'home' : 'all',
	);
	if ( '' === $p['title'] ) { return null; }
	$p['id'] = substr( md5( wp_json_encode( array( $p['title'], $p['text'], $p['code'], $p['url'] ) ) ), 0, 8 ); // new wording re-shows to people who closed the old one
	return $p;
}
function pp_promo_here() {
	$p = pp_promo(); if ( ! $p ) { return false; }
	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) { return false; }
	if ( is_404() || is_search() ) { return false; }
	return 'home' === $p['where'] ? is_front_page() : true;
}

add_action( 'pokepiece_footer_help_links', function () {
	if ( pp_cookie_prompt_on() ) { echo '<li><button class="pp-linkbtn" type="button" data-pp-cookie-open>' . esc_html__( 'Cookie preferences', 'pokepiece' ) . '</button></li>'; }
} );

add_action( 'wp_enqueue_scripts', function () {
	$promo = pp_promo_here() ? pp_promo() : null;
	if ( ! pp_cookie_prompt_on() && ! $promo ) { return; }
	wp_enqueue_script( 'pokepiece-consent', PP_URI . '/assets/js/consent.js', array( 'pokepiece' ), filemtime( PP_DIR . '/assets/js/consent.js' ), array( 'strategy' => 'defer', 'in_footer' => true ) );
	wp_localize_script( 'pokepiece-consent', 'PPC', array(
		'prompt' => pp_cookie_prompt_on(), 'secure' => is_ssl(),
		'promo' => $promo ? array( 'id' => $promo['id'], 'delay' => $promo['delay'], 'days' => $promo['days'] ) : null,
	) );
}, 30 );

add_action( 'wp_footer', function () {
	$privacy = get_page_by_path( 'privacy-policy' ); $plink = $privacy ? get_permalink( $privacy ) : '';
	if ( pp_cookie_prompt_on() ) :
		?>
	<section class="pp-cookie" id="pp-cookie" role="region" aria-labelledby="pp-cookie-t" hidden>
		<h2 id="pp-cookie-t"><?php esc_html_e( 'Cookies', 'pokepiece' ); ?></h2>
		<p><?php esc_html_e( 'We use essential cookies to run the shop (basket, checkout, sign-in). With your OK we also remember your choices and, if enabled, measure how the site is used.', 'pokepiece' ); ?>
		<?php if ( $plink ) : ?><a href="<?php echo esc_url( $plink ); ?>"><?php esc_html_e( 'Privacy policy', 'pokepiece' ); ?></a><?php endif; ?></p>
		<div class="pp-cookie__btns">
			<button class="pp-btn pp-btn--primary" type="button" data-pp-consent-act="all"><?php esc_html_e( 'Accept all', 'pokepiece' ); ?></button>
			<button class="pp-btn pp-btn--primary" type="button" data-pp-consent-act="none"><?php esc_html_e( 'Reject non-essential', 'pokepiece' ); ?></button>
			<button class="pp-btn pp-btn--ghost" type="button" data-pp-cookie-open><?php esc_html_e( 'Choose', 'pokepiece' ); ?></button>
		</div>
	</section>
	<div class="pp-modal" id="pp-cookie-pref" role="dialog" aria-modal="true" aria-labelledby="pp-cp-t" hidden>
		<div class="pp-modal__panel pp-modal__panel--sm">
			<button class="pp-iconbtn pp-close" type="button" aria-label="<?php esc_attr_e( 'Close', 'pokepiece' ); ?>"><?php echo pp_icon( 'close' ); // phpcs:ignore ?></button>
			<form class="pp-cp" novalidate>
				<h2 id="pp-cp-t"><?php esc_html_e( 'Cookie preferences', 'pokepiece' ); ?></h2>
				<p class="pp-muted"><?php esc_html_e( 'Choose what you are happy with. You can change this at any time from the footer.', 'pokepiece' ); ?></p>
				<?php
				$rows = array(
					array( 'necessary', __( 'Strictly necessary', 'pokepiece' ), __( 'Keeps your basket, checkout and sign-in working. Always on.', 'pokepiece' ), true ),
					array( 'p', __( 'Preferences', 'pokepiece' ), __( 'Remembers choices on this device, for example that you closed an offer.', 'pokepiece' ), false ),
					array( 'a', __( 'Analytics', 'pokepiece' ), __( 'Helps us see which pages are used so we can improve the shop. Only applies if analytics is switched on for this site.', 'pokepiece' ), false ),
					array( 'm', __( 'Marketing', 'pokepiece' ), __( 'Lets advertising or social tools recognise you. Only applies if they are switched on for this site.', 'pokepiece' ), false ),
				);
				foreach ( $rows as $r ) : ?>
				<label class="pp-cp__row<?php echo $r[3] ? ' is-locked' : ''; ?>">
					<span class="pp-cp__txt"><strong><?php echo esc_html( $r[1] ); ?></strong><span><?php echo esc_html( $r[2] ); ?></span></span>
					<input type="checkbox" name="<?php echo esc_attr( $r[0] ); ?>" <?php echo $r[3] ? 'checked disabled' : ''; ?>><span class="pp-cp__sw" aria-hidden="true"></span>
				</label>
				<?php endforeach; ?>
				<div class="pp-cookie__btns">
					<button class="pp-btn pp-btn--primary" type="submit"><?php esc_html_e( 'Save choices', 'pokepiece' ); ?></button>
					<button class="pp-btn pp-btn--ghost" type="button" data-pp-consent-act="all"><?php esc_html_e( 'Accept all', 'pokepiece' ); ?></button>
				</div>
			</form>
		</div>
	</div>
		<?php
	endif;
	$p = pp_promo_here() ? pp_promo() : null;
	if ( $p ) :
		$url = $p['url'] ? esc_url( $p['url'] ) : '';
		?>
	<div class="pp-modal pp-modal--promo" id="pp-promo" role="dialog" aria-modal="true" aria-labelledby="pp-promo-t" hidden>
		<div class="pp-modal__panel pp-modal__panel--sm pp-promo">
			<button class="pp-iconbtn pp-close" type="button" aria-label="<?php esc_attr_e( 'Close', 'pokepiece' ); ?>"><?php echo pp_icon( 'close' ); // phpcs:ignore ?></button>
			<h2 id="pp-promo-t"><?php echo esc_html( $p['title'] ); ?></h2>
			<?php if ( $p['text'] ) : ?><p><?php echo esc_html( $p['text'] ); ?></p><?php endif; ?>
			<?php if ( $p['code'] ) : ?><div class="pp-promo__code"><code><?php echo esc_html( $p['code'] ); ?></code><button type="button" class="pp-linkbtn" data-pp-copy="<?php echo esc_attr( $p['code'] ); ?>"><?php esc_html_e( 'Copy code', 'pokepiece' ); ?></button></div><?php endif; ?>
			<?php if ( $url && $p['label'] ) : ?><a class="pp-btn pp-btn--primary pp-btn--block" href="<?php echo $url; // phpcs:ignore ?>"><?php echo esc_html( $p['label'] ); ?></a><?php endif; ?>
			<button class="pp-linkbtn pp-promo__no" type="button" data-pp-promo-no><?php esc_html_e( 'No thanks', 'pokepiece' ); ?></button>
		</div>
	</div>
		<?php
	endif;
}, 20 );
