<?php defined( 'ABSPATH' ) || exit; $groups = pp_game_groups(); ?>
<footer class="pp-footer">
	<div class="pp-wrap pp-footer__grid">
		<div class="pp-footer__brand">
			<img src="<?php echo esc_url( PP_URI . '/assets/brand/horizontal-colour.svg' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="200" height="50" loading="lazy">
			<p><?php echo esc_html( pp_dispatch_text() ); ?></p>
			<?php $td = trim( (string) get_option( 'pokepiece_trading_details', '' ) ); if ( $td ) : ?><p class="pp-muted"><?php echo nl2br( esc_html( $td ) ); ?></p><?php endif; ?>
			<?php if ( pp_newsletter_enabled() ) : ?><button class="pp-btn pp-btn--ghost" type="button" data-pp-newsletter-open><?php esc_html_e( 'Email updates', 'pokepiece' ); ?></button><?php endif; ?>
		</div>
		<nav aria-label="<?php esc_attr_e( 'Shop', 'pokepiece' ); ?>"><h2><?php esc_html_e( 'Shop', 'pokepiece' ); ?></h2><ul>
			<li><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'All products', 'pokepiece' ); ?></a></li>
			<?php foreach ( $groups as $g ) : foreach ( $g['items'] as $it ) : ?><li><a href="<?php echo esc_url( $it['url'] ); ?>"><?php echo esc_html( $g['name'] . ' ' . $it['name'] ); ?></a></li><?php endforeach; endforeach; ?>
		</ul></nav>
		<nav aria-label="<?php esc_attr_e( 'Help', 'pokepiece' ); ?>"><h2><?php esc_html_e( 'Help', 'pokepiece' ); ?></h2><ul>
			<?php foreach ( array( 'my-account' => 'My account', 'about' => 'About', 'contact' => 'Contact', 'shipping-policy' => 'Shipping policy', 'refund-return-policy' => 'Returns policy', 'terms-conditions' => 'Terms & conditions', 'privacy-policy' => 'Privacy policy' ) as $slug => $label ) : $pg = get_page_by_path( $slug ); if ( $pg ) : ?>
			<li><a href="<?php echo esc_url( get_permalink( $pg ) ); ?>"><?php echo esc_html( $label ); ?></a></li><?php endif; endforeach; ?>
			<?php do_action( 'pokepiece_footer_help_links' ); ?>
		</ul></nav>
	</div>
	<p class="pp-footer__legal pp-wrap">© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'Pokémon, One Piece and Dragon Ball are trademarks of their respective owners; this shop is not affiliated with them.', 'pokepiece' ); ?></p>
</footer>

<div class="pp-modal" id="pp-qv" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Quick view', 'pokepiece' ); ?>" hidden><div class="pp-modal__panel"><button class="pp-iconbtn pp-close" type="button" aria-label="<?php esc_attr_e( 'Close quick view', 'pokepiece' ); ?>"><?php echo pp_icon( 'close' ); ?></button><div class="pp-modal__body" aria-live="polite"></div></div></div>

<?php if ( pp_newsletter_enabled() ) : ?>
<div class="pp-modal" id="pp-nl" role="dialog" aria-modal="true" aria-labelledby="pp-nl-title" hidden><div class="pp-modal__panel pp-modal__panel--sm"><button class="pp-iconbtn pp-close" type="button" aria-label="<?php esc_attr_e( 'Close', 'pokepiece' ); ?>"><?php echo pp_icon( 'close' ); ?></button>
	<form class="pp-nl" novalidate><h2 id="pp-nl-title"><?php esc_html_e( 'Email updates', 'pokepiece' ); ?></h2>
	<p><?php esc_html_e( 'Occasional emails about new stock. Unsubscribe any time.', 'pokepiece' ); ?></p>
	<label for="pp-nl-email"><?php esc_html_e( 'Email address', 'pokepiece' ); ?></label><input id="pp-nl-email" type="email" name="email" autocomplete="email" required>
	<input type="text" name="website" tabindex="-1" autocomplete="off" class="pp-hp" aria-hidden="true">
	<label class="pp-check"><input type="checkbox" name="consent" value="1"><span><?php esc_html_e( 'I agree to receive marketing emails from PokePieceTCG.', 'pokepiece' ); ?></span></label>
	<p class="pp-form-msg" role="status" aria-live="polite"></p>
	<button class="pp-btn pp-btn--primary" type="submit"><?php esc_html_e( 'Sign up', 'pokepiece' ); ?></button></form></div></div>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
