<?php
defined( 'ABSPATH' ) || exit;
get_header();
$groups  = pp_game_groups();
$shop    = wc_get_page_permalink( 'shop' );
$stocked = pp_discovery_products( array( 'stock_status' => 'instock', 'limit' => 10 ) );
$range   = $stocked ?: pp_discovery_products( array( 'limit' => 10 ) );
$pool    = array_merge( $stocked, pp_discovery_products( array( 'limit' => 12 ) ) );
$stage = array(); $seen = array();
foreach ( $pool as $p ) { if ( isset( $seen[ $p->get_id() ] ) || ! $p->get_image_id() ) { continue; } $seen[ $p->get_id() ] = 1; $stage[] = $p; if ( count( $stage ) === 2 ) { break; } }
$tt = pp_social( 'tiktok' ); $dc = pp_social( 'discord' ); $reviews = pp_testimonials();
$mode_counts = array();
foreach ( array( 'sealed', 'no_shrink', 'live' ) as $m ) { $mode_counts[ $m ] = pp_option_counts( '_pp_mode', $m ); }
$world = array(
	'pokemon'     => array( 'blurb' => __( 'Booster boxes, packs and live rip and ship.', 'pokepiece' ) ),
	'one-piece'   => array( 'blurb' => __( 'Japanese and English boxes and packs.', 'pokepiece' ) ),
	'dragon-ball' => array( 'blurb' => __( 'Fusion World boxes, live rip and ship.', 'pokepiece' ) ),
);
?>
<main id="main" class="pp-main pp-home" tabindex="-1">

	<section class="pp-hero" aria-labelledby="pp-hero-h">
		<div class="pp-hero__field">
			<div class="pp-wrap pp-hero__inner">
				<p class="pp-script pp-hero__script"><?php esc_html_e( 'sealed boxes · live rip & ship', 'pokepiece' ); ?></p>
				<h1 id="pp-hero-h" class="pp-hero__title"><span><?php esc_html_e( 'Built for', 'pokepiece' ); ?></span> <span><?php esc_html_e( 'collectors.', 'pokepiece' ); ?></span></h1>
				<p class="pp-hero__lead"><?php esc_html_e( 'Pokémon, One Piece and Dragon Ball — and every product tells you plainly whether it’s sealed, no shrink or opened live.', 'pokepiece' ); ?></p>
				<p class="pp-hero__ctas"><?php echo pp_cta( __( 'Shop all products', 'pokepiece' ), $shop, 'light' ); ?><a class="pp-ulink" href="#live"><?php esc_html_e( 'How live opening works', 'pokepiece' ); ?></a></p>
				<div class="pp-hero__stage">
					<?php foreach ( $stage as $i => $p ) : [ $sk, $st ] = pp_stock_state( $p ); ?>
					<a class="pp-tile pp-tile--<?php echo (int) $i; ?>" href="<?php echo esc_url( $p->get_permalink() ); ?>">
						<span class="pp-tile__img"><?php echo $p->get_image( 'pp-plate', array( 'loading' => $i ? 'lazy' : 'eager', 'fetchpriority' => $i ? 'low' : 'high', 'alt' => '' ) ); // phpcs:ignore ?></span>
						<span class="pp-tile__cap"><b><?php echo esc_html( $p->get_name() ); ?></b><span><?php echo wp_kses_post( $p->get_price_html() ); ?> <em class="pp-tag pp-tag--<?php echo esc_attr( $sk ); ?>"><?php echo esc_html( $st ); ?></em></span></span>
					</a>
					<?php endforeach; ?>
					<?php if ( ! $stage ) : ?><img class="pp-hero__mark" src="<?php echo esc_url( PP_URI . '/assets/brand/symbol-colour.svg' ); ?>" alt="" width="220" height="220"><?php endif; ?>
				</div>
			</div>
		</div>
	</section>

	<div class="pp-ticker" aria-hidden="true"><div class="pp-ticker__track"><?php for ( $i = 0; $i < 2; $i++ ) : ?><ul><?php foreach ( array( 'Pokémon', 'Sealed', 'One Piece', 'No shrink', 'Dragon Ball', 'Live rip & ship' ) as $w ) : ?><li><?php echo esc_html( $w ); ?></li><?php endforeach; ?></ul><?php endfor; ?></div></div>

	<section class="pp-worlds" aria-labelledby="pp-worlds-h">
		<div class="pp-wrap">
			<div class="pp-head"><p class="pp-script"><?php esc_html_e( 'pick a world', 'pokepiece' ); ?></p><h2 id="pp-worlds-h" class="pp-display"><?php esc_html_e( 'Three worlds. One counter.', 'pokepiece' ); ?></h2></div>
		</div>
		<ul class="pp-worlds__rail">
			<?php foreach ( $groups as $k => $g ) : $w = $world[ $k ] ?? array( 'blurb' => '' ); $total = array_sum( array_column( $g['items'], 'count' ) ); ?>
			<li class="pp-world pp-game-<?php echo esc_attr( $k ); ?>">
				<h3><?php echo esc_html( $g['name'] ); ?></h3>
				<p><?php echo esc_html( $w['blurb'] ); ?></p>
				<ul class="pp-world__links">
					<?php foreach ( $g['items'] as $it ) : ?><li><a href="<?php echo esc_url( $it['url'] ); ?>"><span><?php echo esc_html( $it['name'] ); ?></span><em><?php echo (int) $it['count']; ?></em><i><?php echo pp_art( 'arrow' ); // phpcs:ignore ?></i></a></li><?php endforeach; ?>
				</ul>
			</li>
			<?php endforeach; ?>
		</ul>
	</section>

	<section class="pp-shelf" aria-labelledby="pp-shelf-h">
		<div class="pp-wrap">
			<div class="pp-shelf__head">
				<div class="pp-head"><p class="pp-script"><?php echo $stocked ? esc_html__( 'ready to ship', 'pokepiece' ) : esc_html__( 'the full range', 'pokepiece' ); ?></p><h2 id="pp-shelf-h" class="pp-display"><?php echo $stocked ? esc_html__( 'On the shelf', 'pokepiece' ) : esc_html__( 'The range', 'pokepiece' ); ?></h2></div>
				<div class="pp-rail-ctl"><button type="button" class="pp-round" data-rail="-1" aria-label="<?php esc_attr_e( 'Previous products', 'pokepiece' ); ?>"><?php echo pp_icon( 'arrow' ); ?></button><button type="button" class="pp-round" data-rail="1" aria-label="<?php esc_attr_e( 'Next products', 'pokepiece' ); ?>"><?php echo pp_icon( 'arrow' ); ?></button></div>
			</div>
			<?php if ( ! $stocked ) : ?>
			<p class="pp-quiet" role="status"><span><b><?php esc_html_e( 'Everything we list is out of stock right now.', 'pokepiece' ); ?></b> <?php esc_html_e( 'Have a look at what we carry — each product page says exactly what it is.', 'pokepiece' ); ?></span></p>
			<?php endif; ?>
		</div>
		<?php if ( $range ) : ?>
		<ul class="products pp-rail" data-pp-rail tabindex="0" aria-label="<?php esc_attr_e( 'Products', 'pokepiece' ); ?>">
			<?php foreach ( $range as $p ) { $GLOBALS['post'] = get_post( $p->get_id() ); setup_postdata( $GLOBALS['post'] ); $GLOBALS['product'] = $p; wc_get_template_part( 'content', 'product' ); } wp_reset_postdata(); ?>
		</ul>
		<?php endif; ?>
		<div class="pp-wrap pp-shelf__foot"><?php echo pp_cta( __( 'View everything', 'pokepiece' ), $shop . ( $stocked ? '?pp_stock=1' : '' ), 'ink' ); ?></div>
	</section>

	<section class="pp-live" id="live" aria-labelledby="pp-live-h">
		<div class="pp-wrap pp-live__grid">
			<div class="pp-live__copy">
				<p class="pp-script"><?php esc_html_e( 'opened live. shipped to you.', 'pokepiece' ); ?></p>
				<h2 id="pp-live-h" class="pp-display"><?php esc_html_e( 'Live rip & ship', 'pokepiece' ); ?></h2>
				<p><?php esc_html_e( 'Some products are opened live and the cards are then shipped to you. They’re labelled “Live opening” everywhere you see them, so you always know which kind you’re buying.', 'pokepiece' ); ?></p>
				<p class="pp-live__ctas"><?php echo pp_cta( __( 'See live-opening products', 'pokepiece' ), add_query_arg( 'pp_mode', 'live', $shop ), 'yellow' ); ?><?php if ( $tt ) : ?><a class="pp-ulink pp-ulink--light" href="<?php echo esc_url( $tt ); ?>" rel="noopener"><?php esc_html_e( 'Watch on TikTok', 'pokepiece' ); ?></a><?php endif; ?></p>
			</div>
			<ol class="pp-steps">
				<li><b>1</b><h3><?php esc_html_e( 'Choose a live-opening product', 'pokepiece' ); ?></h3><p><?php esc_html_e( 'Look for the Live opening label. It’s on the product card, the product page and in your cart.', 'pokepiece' ); ?></p></li>
				<li><b>2</b><h3><?php esc_html_e( 'Check out as normal', 'pokepiece' ); ?></h3><p><?php esc_html_e( 'Your order goes through the same secure checkout as everything else in the shop.', 'pokepiece' ); ?></p></li>
				<li><b>3</b><h3><?php esc_html_e( 'Your cards are shipped', 'pokepiece' ); ?></h3><p><?php echo esc_html( pp_dispatch_text() ); ?></p></li>
			</ol>
		</div>
	</section>

	<section class="pp-community" aria-labelledby="pp-comm-h">
		<div class="pp-wrap">
			<div class="pp-head"><p class="pp-script"><?php esc_html_e( 'watch · join · collect', 'pokepiece' ); ?></p><h2 id="pp-comm-h" class="pp-display"><?php esc_html_e( 'Where it happens', 'pokepiece' ); ?></h2></div>
			<ul class="pp-comm__grid">
				<?php if ( $tt ) : ?><li><a class="pp-comm pp-comm--tiktok" href="<?php echo esc_url( $tt ); ?>" rel="noopener"><span class="pp-comm__k">TikTok</span><span class="pp-comm__h">@pokepiecetcgg</span><span class="pp-comm__t"><?php esc_html_e( 'Live openings and events are run on TikTok. Follow to catch the next one.', 'pokepiece' ); ?></span><span class="pp-comm__go"><?php esc_html_e( 'Open TikTok', 'pokepiece' ); ?><i><?php echo pp_art( 'arrow' ); // phpcs:ignore ?></i></span></a></li><?php endif; ?>
				<?php if ( $dc ) : ?><li><a class="pp-comm pp-comm--discord" href="<?php echo esc_url( $dc ); ?>" rel="noopener"><span class="pp-comm__k">Discord</span><span class="pp-comm__h"><?php esc_html_e( 'Join the community', 'pokepiece' ); ?></span><span class="pp-comm__t"><?php esc_html_e( 'Talk cards with other collectors in the PokePieceTCG Discord.', 'pokepiece' ); ?></span><span class="pp-comm__go"><?php esc_html_e( 'Join the Discord', 'pokepiece' ); ?><i><?php echo pp_art( 'arrow' ); // phpcs:ignore ?></i></span></a></li><?php endif; ?>
			</ul>
		</div>
	</section>

	<section class="pp-know" aria-labelledby="pp-know-h">
		<div class="pp-wrap">
			<div class="pp-head"><p class="pp-script"><?php esc_html_e( 'no surprises', 'pokepiece' ); ?></p><h2 id="pp-know-h" class="pp-display"><?php esc_html_e( 'Know what’s in the box', 'pokepiece' ); ?></h2></div>
			<ul class="pp-know__rows">
				<?php foreach ( array(
					'sealed'    => array( __( 'Sealed', 'pokepiece' ), __( 'Delivered sealed. Any packaging condition is written on the product page.', 'pokepiece' ) ),
					'no_shrink' => array( __( 'No shrink', 'pokepiece' ), __( 'Sent without shrink-wrap. The condition is stated plainly beside the price.', 'pokepiece' ) ),
					'live'      => array( __( 'Live opening', 'pokepiece' ), __( 'Opened live, then the cards are shipped. Not a sealed product.', 'pokepiece' ) ),
				) as $m => [ $label, $txt ] ) : ?>
				<li class="pp-know__row pp-know__row--<?php echo esc_attr( $m ); ?>">
					<a href="<?php echo esc_url( add_query_arg( 'pp_mode', $m, $shop ) ); ?>">
						<span class="pp-know__label"><?php echo esc_html( $label ); ?></span>
						<span class="pp-know__txt"><?php echo esc_html( $txt ); ?></span>
						<span class="pp-know__go"><?php echo $mode_counts[ $m ] ? esc_html( sprintf( _n( '%d product', '%d products', $mode_counts[ $m ], 'pokepiece' ), $mode_counts[ $m ] ) ) : esc_html__( 'Browse', 'pokepiece' ); ?><i><?php echo pp_art( 'arrow' ); // phpcs:ignore ?></i></span>
					</a>
				</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<section class="pp-story" aria-labelledby="pp-story-h">
		<div class="pp-wrap pp-story__grid">
			<div class="pp-story__copy">
				<p class="pp-script"><?php esc_html_e( 'a very old habit', 'pokepiece' ); ?></p>
				<h2 id="pp-story-h" class="pp-display"><?php esc_html_e( 'Collecting is older than booster boxes', 'pokepiece' ); ?></h2>
				<p><?php esc_html_e( 'People have been collecting and swapping little printed cards for well over a century. The cards pictured are Liebig trade cards from the 1880s to 1910s. They are not our products — just a reminder of where this hobby comes from.', 'pokepiece' ); ?></p>
			</div>
			<div class="pp-story__collage" data-pp-tilt-group>
				<?php foreach ( array( 1, 2, 3, 4 ) as $n ) : ?><img class="pp-story__img pp-story__img--<?php echo (int) $n; ?>" src="<?php echo esc_url( PP_URI . '/assets/img/story-' . $n . '.jpg' ); ?>" alt="" loading="lazy" width="<?php echo $n < 3 ? 485 : 760; ?>" height="<?php echo $n < 3 ? 760 : 510; ?>"><?php endforeach; ?>
			</div>
		</div>
	</section>

	<?php if ( $reviews ) : $first = array_shift( $reviews ); ?>
	<section class="pp-reviews" aria-labelledby="pp-rev-h">
		<div class="pp-wrap">
			<div class="pp-head"><p class="pp-script"><?php esc_html_e( 'from the people who bought', 'pokepiece' ); ?></p><h2 id="pp-rev-h" class="pp-display"><?php esc_html_e( 'Collector reviews', 'pokepiece' ); ?></h2></div>
			<div class="pp-rev__grid">
				<figure class="pp-rev pp-rev--lead"><blockquote><p><?php echo esc_html( $first['q'] ); ?></p></blockquote><figcaption><b><?php echo esc_html( $first['n'] ); ?></b> <?php esc_html_e( 'Vinted customer', 'pokepiece' ); ?></figcaption></figure>
				<?php foreach ( $reviews as $r ) : ?>
				<figure class="pp-rev"><blockquote><p><?php echo esc_html( $r['q'] ); ?></p></blockquote><figcaption><b><?php echo esc_html( $r['n'] ); ?></b> <?php esc_html_e( 'Vinted customer', 'pokepiece' ); ?></figcaption></figure>
				<?php endforeach; ?>
			</div>
			<p class="pp-muted pp-rev__note"><?php esc_html_e( 'Reviews left for the seller on Vinted, shown as originally written. They are seller reviews, not product ratings.', 'pokepiece' ); ?></p>
		</div>
	</section>
	<?php endif; ?>

	<section class="pp-deliver" aria-label="<?php esc_attr_e( 'Delivery', 'pokepiece' ); ?>">
		<div class="pp-wrap pp-deliver__inner">
			<p class="pp-deliver__big"><?php echo esc_html( pp_dispatch_text() ); ?></p>
			<p class="pp-deliver__links"><?php $sp = get_page_by_path( 'shipping-policy' ); if ( $sp ) : ?><a class="pp-ulink" href="<?php echo esc_url( get_permalink( $sp ) ); ?>"><?php esc_html_e( 'Shipping policy', 'pokepiece' ); ?></a><?php endif; ?>
			<?php $svc = pp_service_ids(); if ( $svc && get_post_status( $svc[0] ) === 'publish' ) : ?><a class="pp-ulink" href="<?php echo esc_url( get_permalink( $svc[0] ) ); ?>"><?php esc_html_e( 'Upgrade your postage', 'pokepiece' ); ?></a><?php endif; ?></p>
		</div>
	</section>
</main>
<?php get_footer();
