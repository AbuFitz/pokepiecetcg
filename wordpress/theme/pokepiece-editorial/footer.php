<?php defined( 'ABSPATH' ) || exit; $groups = pp_game_groups(); $tt = pp_social( 'tiktok' ); $dc = pp_social( 'discord' ); ?>
<footer class="pp-footer">
	<div class="pp-wrap pp-footer__grid">
		<div class="pp-footer__lead">
			<a class="pp-footer__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>"><img src="<?php echo esc_url( PP_URI . '/assets/brand/horizontal-colour.svg' ); ?>" alt="" width="260" height="65" loading="lazy"></a>
			<p class="pp-footer__tag"><?php esc_html_e( 'Built for collectors. Driven by adventure.', 'pokepiece' ); ?></p>
			<?php echo pp_cta( __( 'Shop all products', 'pokepiece' ), wc_get_page_permalink( 'shop' ), 'yellow' ); ?>
			<p class="pp-footer__socials"><?php if ( $tt ) : ?><a class="pp-social" href="<?php echo esc_url( $tt ); ?>" rel="noopener" aria-label="<?php esc_attr_e( 'TikTok, @pokepiecetcgg', 'pokepiece' ); ?>"><?php echo pp_art( 'tiktok' ); // phpcs:ignore ?></a><?php endif; ?><?php if ( $dc ) : ?><a class="pp-social" href="<?php echo esc_url( $dc ); ?>" rel="noopener" aria-label="<?php esc_attr_e( 'Discord', 'pokepiece' ); ?>"><?php echo pp_art( 'discord' ); // phpcs:ignore ?></a><?php endif; ?></p>
			<?php if ( pp_newsletter_enabled() ) : ?><button class="pp-linkbtn" type="button" data-pp-newsletter-open><?php esc_html_e( 'Email updates', 'pokepiece' ); ?></button><?php endif; ?>
		</div>
		<nav aria-label="<?php esc_attr_e( 'Shop', 'pokepiece' ); ?>"><h2><?php esc_html_e( 'Shop', 'pokepiece' ); ?></h2><ul>
			<li><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'All products', 'pokepiece' ); ?></a></li>
			<?php foreach ( $groups as $g ) : foreach ( $g['items'] as $it ) : ?><li><a href="<?php echo esc_url( $it['url'] ); ?>"><?php echo esc_html( $g['name'] . ' · ' . $it['name'] ); ?></a></li><?php endforeach; endforeach; ?>
		</ul></nav>
		<nav aria-label="<?php esc_attr_e( 'Help', 'pokepiece' ); ?>"><h2><?php esc_html_e( 'Help', 'pokepiece' ); ?></h2><ul>
			<?php foreach ( array( 'my-account' => 'My account', 'about' => 'About', 'contact' => 'Contact', 'shipping-policy' => 'Shipping', 'refund-return-policy' => 'Returns', 'terms-conditions' => 'Terms', 'privacy-policy' => 'Privacy' ) as $slug => $label ) : $pg = get_page_by_path( $slug ); if ( $pg ) : ?>
			<li><a href="<?php echo esc_url( get_permalink( $pg ) ); ?>"><?php echo esc_html( $label ); ?></a></li><?php endif; endforeach; ?>
			<?php do_action( 'pokepiece_footer_help_links' ); ?>
		</ul></nav>
	</div>
	<?php $pay = pp_payment_methods(); if ( $pay ) : ?><ul class="pp-pay pp-wrap" aria-label="<?php esc_attr_e( 'Payment methods', 'pokepiece' ); ?>"><?php foreach ( $pay as $slug => $m ) : ?><li><span class="screen-reader-text"><?php echo esc_html( $m[0] ); ?></span><?php echo pp_pay_icon( $slug, $m[1] ); // phpcs:ignore ?></li><?php endforeach; ?></ul><?php endif; ?>
	<p class="pp-footer__legal pp-wrap"><?php echo esc_html( pp_dispatch_text() ); ?> © <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'Pokémon, One Piece and Dragon Ball are trademarks of their respective owners; this shop is not affiliated with them.', 'pokepiece' ); ?>
	<?php $td = trim( (string) get_option( 'pokepiece_trading_details', '' ) ); if ( $td ) { echo '<br>' . nl2br( esc_html( $td ) ); } ?></p>
</footer>


<div class="pp-modal pp-modal--search" id="pp-search-pop" role="dialog" aria-modal="true" aria-labelledby="pp-sp-title" hidden>
	<div class="pp-modal__panel pp-sp">
		<h2 id="pp-sp-title" class="screen-reader-text"><?php esc_html_e( 'Search products', 'pokepiece' ); ?></h2>
		<form class="pp-sp__form" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<span class="pp-sp__icon"><?php echo pp_icon( 'search' ); ?></span>
			<label class="screen-reader-text" for="pp-sp-input"><?php esc_html_e( 'Search sets, codes or products', 'pokepiece' ); ?></label>
			<input id="pp-sp-input" name="s" type="search" placeholder="<?php esc_attr_e( 'Search sets, codes or products', 'pokepiece' ); ?>" autocomplete="off" enterkeyhint="search">
			<input type="hidden" name="post_type" value="product">
			<button class="pp-btn pp-btn--primary pp-sp__go" type="submit"><?php esc_html_e( 'Search', 'pokepiece' ); ?></button>
			<button class="pp-sp__x" type="button" aria-label="<?php esc_attr_e( 'Close search', 'pokepiece' ); ?>"><?php echo pp_icon( 'close' ); ?></button>
		</form>
		<div class="pp-sp__body">
			<p class="pp-sp__status" role="status" aria-live="polite" hidden></p>
			<div class="pp-sp__res" hidden><ul class="pp-sp__grid"></ul><a class="pp-btn pp-btn--ghost pp-sp__all" href="#" hidden><?php esc_html_e( 'See all results', 'pokepiece' ); ?></a></div>
			<div class="pp-sp__feat">
				<p class="pp-sp__label"><?php esc_html_e( 'Popular right now', 'pokepiece' ); ?></p>
				<ul class="pp-sp__grid"><?php foreach ( pp_search_featured( 8 ) as $p ) { pp_search_tile( $p ); } ?></ul>
				<p class="pp-sp__label"><?php esc_html_e( 'Browse', 'pokepiece' ); ?></p>
				<p class="pp-sp__cats"><?php foreach ( pp_game_groups() as $g ) : foreach ( $g['items'] as $it ) : ?><a class="pp-sp__cat" href="<?php echo esc_url( $it['url'] ); ?>"><?php echo esc_html( $g['name'] . ' ' . $it['name'] ); ?></a><?php endforeach; endforeach; ?></p>
			</div>
		</div>
	</div>
</div>

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
