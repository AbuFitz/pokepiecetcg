<?php
/**
 * Sign-in / create-account popup. Uses WordPress's own authentication (wp_signon) and WooCommerce's customer creation (wc_create_new_customer):
 * no separate account store. Generic error messages (no account enumeration), nonce-protected, IP rate-limited.
 */
defined( 'ABSPATH' ) || exit;

function pp_registration_enabled() { return 'yes' === get_option( 'woocommerce_enable_myaccount_registration' ); }

function pp_account_throttle( $bucket ) {
	$key = 'pp_acc_' . $bucket . '_' . md5( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	$n = (int) get_transient( $key );
	if ( $n >= 8 ) { wp_send_json_error( array( 'message' => __( 'Too many attempts. Please wait a few minutes and try again.', 'pokepiece' ) ), 429 ); }
	set_transient( $key, $n + 1, 15 * MINUTE_IN_SECONDS );
}

add_action( 'wp_ajax_nopriv_pokepiece_login', function () {
	check_ajax_referer( 'pp_account', 'nonce' );
	pp_account_throttle( 'login' );
	$login = sanitize_text_field( wp_unslash( $_POST['username'] ?? '' ) );
	$pass  = (string) wp_unslash( $_POST['password'] ?? '' );
	if ( '' === $login || '' === $pass ) { wp_send_json_error( array( 'message' => __( 'Enter your email or username and your password.', 'pokepiece' ) ), 422 ); }
	if ( is_email( $login ) && ( $u = get_user_by( 'email', $login ) ) ) { $login = $u->user_login; }
	$user = wp_signon( array( 'user_login' => $login, 'user_password' => $pass, 'remember' => ! empty( $_POST['remember'] ) ), is_ssl() );
	if ( is_wp_error( $user ) ) { wp_send_json_error( array( 'message' => __( 'That email/username and password combination didn’t work.', 'pokepiece' ) ), 401 ); }
	wp_send_json_success( array( 'redirect' => wc_get_page_permalink( 'myaccount' ) ) );
} );

add_action( 'wp_ajax_nopriv_pokepiece_register', function () {
	check_ajax_referer( 'pp_account', 'nonce' );
	if ( ! pp_registration_enabled() ) { wp_send_json_error( array( 'message' => __( 'Account creation is not available.', 'pokepiece' ) ), 403 ); }
	pp_account_throttle( 'register' );
	if ( ! empty( $_POST['website'] ) ) { wp_send_json_success( array( 'redirect' => home_url( '/' ) ) ); } // honeypot
	$email = sanitize_email( wp_unslash( $_POST['email'] ?? '' ) );
	$pass  = (string) wp_unslash( $_POST['password'] ?? '' );
	if ( ! is_email( $email ) ) { wp_send_json_error( array( 'message' => __( 'Enter a valid email address.', 'pokepiece' ) ), 422 ); }
	if ( strlen( $pass ) < 8 ) { wp_send_json_error( array( 'message' => __( 'Choose a password of at least 8 characters.', 'pokepiece' ) ), 422 ); }
	if ( email_exists( $email ) ) { wp_send_json_error( array( 'message' => __( 'We couldn’t create that account. If you already have one, sign in or reset your password.', 'pokepiece' ) ), 409 ); }
	$id = wc_create_new_customer( $email, '', $pass );
	if ( is_wp_error( $id ) ) { wp_send_json_error( array( 'message' => __( 'We couldn’t create that account. Please try again.', 'pokepiece' ) ), 422 ); }
	wc_set_customer_auth_cookie( $id );
	wp_send_json_success( array( 'redirect' => wc_get_page_permalink( 'myaccount' ) ) );
} );

function pp_account_modal() {
	if ( is_user_logged_in() || ( function_exists( 'is_account_page' ) && is_account_page() ) ) { return; }
	$reg = pp_registration_enabled();
	?>
<div class="pp-modal pp-modal--account" id="pp-account" role="dialog" aria-modal="true" aria-labelledby="pp-acc-title" hidden>
	<div class="pp-modal__panel pp-acc">
		<button class="pp-iconbtn pp-close" type="button" aria-label="<?php esc_attr_e( 'Close', 'pokepiece' ); ?>"><?php echo pp_icon( 'close' ); ?></button>
		<aside class="pp-acc__side" aria-hidden="true">
			<img src="<?php echo esc_url( PP_URI . '/assets/brand/horizontal-colour.svg' ); ?>" alt="" width="200" height="50">
			<p class="pp-acc__big"><?php esc_html_e( 'Built for collectors. Driven by adventure.', 'pokepiece' ); ?></p>
			<ul><li><?php esc_html_e( 'Track your orders', 'pokepiece' ); ?></li><li><?php esc_html_e( 'Save your addresses', 'pokepiece' ); ?></li><li><?php esc_html_e( 'Check out faster', 'pokepiece' ); ?></li></ul>
		</aside>
		<div class="pp-acc__main">
			<div class="pp-acc__tabs" role="tablist"><button type="button" role="tab" id="pp-tab-in" aria-selected="true" aria-controls="pp-pane-in"><?php esc_html_e( 'Sign in', 'pokepiece' ); ?></button><?php if ( $reg ) : ?><button type="button" role="tab" id="pp-tab-up" aria-selected="false" aria-controls="pp-pane-up" tabindex="-1"><?php esc_html_e( 'Create account', 'pokepiece' ); ?></button><?php endif; ?></div>
			<h2 id="pp-acc-title" class="screen-reader-text"><?php esc_html_e( 'Account', 'pokepiece' ); ?></h2>
			<form class="pp-acc__form" id="pp-pane-in" role="tabpanel" aria-labelledby="pp-tab-in" data-action="pokepiece_login" novalidate>
				<label for="pp-in-user"><?php esc_html_e( 'Email or username', 'pokepiece' ); ?></label><input id="pp-in-user" name="username" type="text" autocomplete="username" required>
				<label for="pp-in-pass"><?php esc_html_e( 'Password', 'pokepiece' ); ?></label><div class="pp-pass"><input id="pp-in-pass" name="password" type="password" autocomplete="current-password" required><button type="button" class="pp-pass__toggle" aria-pressed="false" aria-controls="pp-in-pass"><?php esc_html_e( 'Show', 'pokepiece' ); ?></button></div>
				<div class="pp-acc__row"><label class="pp-check"><input type="checkbox" name="remember" value="1"><span><?php esc_html_e( 'Remember me', 'pokepiece' ); ?></span></label><a class="pp-ulink" href="<?php echo esc_url( wc_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'pokepiece' ); ?></a></div>
				<p class="pp-form-msg" role="alert"></p>
				<button class="pp-btn pp-btn--primary pp-btn--block" type="submit"><?php esc_html_e( 'Sign in', 'pokepiece' ); ?></button>
			</form>
			<?php if ( $reg ) : ?>
			<form class="pp-acc__form" id="pp-pane-up" role="tabpanel" aria-labelledby="pp-tab-up" data-action="pokepiece_register" hidden novalidate>
				<label for="pp-up-email"><?php esc_html_e( 'Email address', 'pokepiece' ); ?></label><input id="pp-up-email" name="email" type="email" autocomplete="email" required>
				<label for="pp-up-pass"><?php esc_html_e( 'Password (8+ characters)', 'pokepiece' ); ?></label><div class="pp-pass"><input id="pp-up-pass" name="password" type="password" autocomplete="new-password" minlength="8" required><button type="button" class="pp-pass__toggle" aria-pressed="false" aria-controls="pp-up-pass"><?php esc_html_e( 'Show', 'pokepiece' ); ?></button></div>
				<input type="text" name="website" tabindex="-1" autocomplete="off" class="pp-hp" aria-hidden="true">
				<p class="pp-muted"><?php
				$pp = get_page_by_path( 'privacy-policy' );
				printf( esc_html__( 'Your personal data is used to manage your account as described in our %s.', 'pokepiece' ), $pp ? '<a href="' . esc_url( get_permalink( $pp ) ) . '">' . esc_html__( 'privacy policy', 'pokepiece' ) . '</a>' : esc_html__( 'privacy policy', 'pokepiece' ) ); // phpcs:ignore
				?></p>
				<p class="pp-form-msg" role="alert"></p>
				<button class="pp-btn pp-btn--primary pp-btn--block" type="submit"><?php esc_html_e( 'Create account', 'pokepiece' ); ?></button>
			</form>
			<?php endif; ?>
		</div>
	</div>
</div>
	<?php
}
add_action( 'wp_footer', 'pp_account_modal', 5 );

/* ---- My account page (the full page; the sign-in popup stays for everywhere else) ---- */
// Logged out: one designed stage. Brand panel on the left, sign-in with create-account below it on the right.
add_action( 'woocommerce_before_customer_login_form', function () {
	?>
	<div class="pp-auth">
		<aside class="pp-auth__aside">
			<p class="pp-script"><?php esc_html_e( 'welcome back', 'pokepiece' ); ?></p>
			<p class="pp-auth__title"><?php esc_html_e( 'Your account', 'pokepiece' ); ?></p>
			<ul class="pp-auth__perks">
				<li><?php esc_html_e( 'Track and review your orders', 'pokepiece' ); ?></li>
				<li><?php esc_html_e( 'Save addresses for faster checkout', 'pokepiece' ); ?></li>
				<li><?php esc_html_e( 'Update your details any time', 'pokepiece' ); ?></li>
			</ul>
			<a class="pp-auth__guest" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Just browsing? Back to the shop', 'pokepiece' ); ?> →</a>
		</aside>
		<div class="pp-auth__forms">
	<?php
}, 1 );
add_action( 'woocommerce_after_customer_login_form', function () { echo '</div></div>'; }, 99 );

// Logged in: dashboard tiles instead of the stock paragraph.
add_action( 'woocommerce_account_dashboard', function () {
	$u = wp_get_current_user();
	$tiles = array(
		array( 'orders', __( 'Orders', 'pokepiece' ), __( 'Track and review what you have bought', 'pokepiece' ) ),
		array( 'edit-address', __( 'Addresses', 'pokepiece' ), __( 'Delivery and billing details', 'pokepiece' ) ),
		array( 'edit-account', __( 'Account details', 'pokepiece' ), __( 'Name, email and password', 'pokepiece' ) ),
	);
	echo '<div class="pp-dash"><p class="pp-script">' . esc_html__( 'good to see you', 'pokepiece' ) . '</p><h2 class="pp-dash__hi">' . sprintf( /* translators: %s name */ esc_html__( 'Hi %s', 'pokepiece' ), esc_html( $u->display_name ) ) . '</h2><ul class="pp-dash__tiles">';
	foreach ( $tiles as $t ) { echo '<li><a href="' . esc_url( wc_get_account_endpoint_url( $t[0] ) ) . '"><strong>' . esc_html( $t[1] ) . '</strong><span>' . esc_html( $t[2] ) . '</span><i aria-hidden="true">→</i></a></li>'; }
	echo '</ul><p class="pp-dash__out"><a href="' . esc_url( wc_logout_url() ) . '">' . esc_html__( 'Log out', 'pokepiece' ) . '</a></p></div>';
}, 5 );

// A card shop sells no downloads: hide that tab unless this customer actually has some.
add_filter( 'woocommerce_account_menu_items', function ( $items ) {
	if ( isset( $items['downloads'] ) && function_exists( 'WC' ) && WC()->customer && ! WC()->customer->get_downloadable_products() ) { unset( $items['downloads'] ); }
	return $items;
}, 20 );
