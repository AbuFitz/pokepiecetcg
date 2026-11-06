<?php
defined( 'ABSPATH' ) || exit;
get_header();
$groups  = pp_game_groups();
$shop    = wc_get_page_permalink( 'shop' );
$stocked = pp_discovery_products( array( 'stock_status' => 'instock', 'limit' => 10 ) );
// Wheel content: in-stock first, then the rest of the range (each card states its real stock). Never fewer than the catalogue allows.
$range = $stocked;
if ( count( $range ) < 8 ) { $ids = wp_list_pluck( $range, 'id' ); foreach ( pp_discovery_products( array( 'limit' => 12 ) ) as $p ) { if ( ! in_array( $p->get_id(), $ids, true ) ) { $range[] = $p; } if ( count( $range ) >= 10 ) { break; } } }
$all_in = $stocked && count( $stocked ) === count( $range );
$pool    = array_merge( $stocked, pp_discovery_products( array( 'limit' => 12 ) ) );
$stage = array(); $seen = array();
foreach ( $pool as $p ) { if ( isset( $seen[ $p->get_id() ] ) || ! $p->get_image_id() ) { continue; } $seen[ $p->get_id() ] = 1; $stage[] = $p; if ( count( $stage ) === 2 ) { break; } }
$tt = pp_social( 'tiktok' ); $dc = pp_social( 'discord' ); $reviews = pp_testimonials();
$world = array(
	'pokemon'     => array( 'blurb' => __( 'Japanese and English Pokémon releases.', 'pokepiece' ) ),
	'one-piece'   => array( 'blurb' => __( 'Japanese and English One Piece releases.', 'pokepiece' ) ),
	'dragon-ball' => array( 'blurb' => __( 'Dragon Ball card game boxes.', 'pokepiece' ) ),
);

/** Up to three real product photos per game (WooCommerce data): in-stock first, never the service product, only products that actually have an image. */
function pp_world_products( $items, $game = '' ) {
	$slugs = array_column( $items, 'slug' );
	$ps = wc_get_products( array( 'status' => 'publish', 'limit' => 24, 'category' => $slugs, 'exclude' => pp_service_ids(), 'orderby' => 'date', 'order' => 'DESC' ) );
	// Only products whose own title names the game: a mis-filed item (e.g. a Pokémon pack under One Piece) must not represent the wrong world.
	$needles = array( 'pokemon' => array( 'pokémon', 'pokemon' ), 'one-piece' => array( 'one piece' ), 'dragon-ball' => array( 'dragon ball' ) )[ $game ] ?? array();
	$ps = array_values( array_filter( $ps, function ( $p ) use ( $needles ) { if ( ! $p->get_image_id() ) { return false; } $n = mb_strtolower( $p->get_name() ); foreach ( $needles as $w ) { if ( str_contains( $n, $w ) ) { return true; } } return ! $needles; } ) );
	usort( $ps, fn( $a, $b ) => (int) $b->is_in_stock() <=> (int) $a->is_in_stock() );
	return array_slice( $ps, 0, 3 );
}
?>
<main id="main" class="pp-main pp-home" tabindex="-1">

	<section class="pp-hero" aria-labelledby="pp-hero-h">
		<div class="pp-hero__field">
			<div class="pp-wrap pp-hero__inner">
				<p class="pp-script pp-hero__script"><?php esc_html_e( 'driven by adventure', 'pokepiece' ); ?></p>
				<h1 id="pp-hero-h" class="pp-hero__title"><span><?php esc_html_e( 'Built for', 'pokepiece' ); ?></span> <span><?php esc_html_e( 'collectors.', 'pokepiece' ); ?></span></h1>
				<p class="pp-hero__lead"><?php esc_html_e( 'PokePieceTCG is an online trading card store specialising in sealed Pokémon and One Piece TCG products.', 'pokepiece' ); ?></p>
				<p class="pp-hero__ctas"><?php echo pp_cta( __( 'Shop all products', 'pokepiece' ), $shop, 'light' ); ?><a class="pp-ulink" href="#why"><?php esc_html_e( 'Why collectors choose us', 'pokepiece' ); ?></a></p>
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

	<div class="pp-ticker" aria-hidden="true"><div class="pp-ticker__track"><?php for ( $i = 0; $i < 2; $i++ ) : ?><ul><?php foreach ( array( 'Pokémon', 'One Piece', 'Booster boxes', 'Elite trainer boxes', 'Collection boxes', 'Sealed TCG' ) as $w ) : ?><li><?php echo esc_html( $w ); ?></li><?php endforeach; ?></ul><?php endfor; ?></div></div>

	<section class="pp-worlds" aria-labelledby="pp-worlds-h">
		<div class="pp-wrap">
			<div class="pp-head"><p class="pp-script"><?php esc_html_e( 'pick a world', 'pokepiece' ); ?></p><h2 id="pp-worlds-h" class="pp-display"><?php esc_html_e( 'Three worlds. One counter.', 'pokepiece' ); ?></h2></div>
		</div>
		<ul class="pp-worlds__rail">
			<?php foreach ( $groups as $k => $g ) : $w = $world[ $k ] ?? array( 'blurb' => '' ); $total = array_sum( array_column( $g['items'], 'count' ) ); ?>
			<li class="pp-world pp-game-<?php echo esc_attr( $k ); ?>">
				<?php $custom = esc_url( (string) get_option( 'pokepiece_world_img_' . $k, '' ) ); if ( $custom ) : ?><div class="pp-world__photo pp-world__photo--custom" aria-hidden="true"><img src="<?php echo $custom; ?>" alt="" loading="lazy" decoding="async"></div><?php elseif ( ( $wp = pp_world_products( $g['items'], $k )[0] ?? null ) ) : ?><div class="pp-world__photo" aria-hidden="true" data-pid="<?php echo (int) $wp->get_id(); ?>"><?php echo $wp->get_image( 'large', array( 'alt' => '', 'decoding' => 'async' ) ); // phpcs:ignore ?></div><?php endif; ?>
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
				<div class="pp-head"><p class="pp-script"><?php echo $all_in ? esc_html__( 'ready to ship', 'pokepiece' ) : esc_html__( 'the full range', 'pokepiece' ); ?></p><h2 id="pp-shelf-h" class="pp-display"><?php echo $all_in ? esc_html__( 'On the shelf', 'pokepiece' ) : esc_html__( 'The range', 'pokepiece' ); ?></h2></div>
				
			</div>
			<?php if ( ! $stocked ) : ?>
			<p class="pp-quiet" role="status"><span><b><?php esc_html_e( 'Everything we list is out of stock right now.', 'pokepiece' ); ?></b> <?php esc_html_e( 'Have a look at what we carry, each product page says exactly what it is.', 'pokepiece' ); ?></span></p>
			<?php endif; ?>
		</div>
		<?php if ( $range ) : ?>
		<ul class="products pp-rail pp-rail--swipe" data-pp-rail tabindex="0" aria-label="<?php esc_attr_e( 'Products', 'pokepiece' ); ?>">
			<?php foreach ( $range as $p ) { $GLOBALS['post'] = get_post( $p->get_id() ); setup_postdata( $GLOBALS['post'] ); $GLOBALS['product'] = $p; wc_get_template_part( 'content', 'product' ); } wp_reset_postdata(); ?>
		</ul>
		<?php endif; ?>
		<div class="pp-wrap pp-shelf__foot"><?php echo pp_cta( __( 'View everything', 'pokepiece' ), $shop . ( $stocked ? '?pp_stock=1' : '' ), 'ink' ); ?></div>
	</section>


	<section class="pp-why" id="why" aria-labelledby="pp-why-h">
		<div class="pp-wrap">
			<div class="pp-head"><p class="pp-script"><?php esc_html_e( 'built for collectors', 'pokepiece' ); ?></p><h2 id="pp-why-h" class="pp-display"><?php esc_html_e( 'Why choose us', 'pokepiece' ); ?></h2></div>
			<ul class="pp-why__list">
				<li><h3><?php esc_html_e( 'Secure packaging', 'pokepiece' ); ?></h3><p><?php esc_html_e( 'Packed to protect sealed condition.', 'pokepiece' ); ?></p></li>
				<li><h3><?php esc_html_e( 'Fast UK dispatch', 'pokepiece' ); ?></h3><p><?php esc_html_e( 'Quick from the UK, every order.', 'pokepiece' ); ?></p></li>
				<li><h3><?php esc_html_e( 'Trusted seller', 'pokepiece' ); ?></h3><p><?php esc_html_e( 'Backed by 5.0 ★ reviews.', 'pokepiece' ); ?></p></li>
				<li><h3><?php esc_html_e( 'Authentic products', 'pokepiece' ); ?></h3><p><?php esc_html_e( 'Sealed, from trusted suppliers.', 'pokepiece' ); ?></p></li>
			</ul>
			<?php $ab = get_page_by_path( 'about' ); if ( $ab ) : ?><p class="pp-why__cta"><?php echo pp_cta( __( 'Our story', 'pokepiece' ), get_permalink( $ab ), 'ink' ); // phpcs:ignore ?></p><?php endif; ?>
		</div>
	</section>

	<section class="pp-faq" aria-labelledby="pp-faq-h">
		<div class="pp-wrap pp-faq__grid">
			<div class="pp-head"><p class="pp-script"><?php esc_html_e( 'good to know', 'pokepiece' ); ?></p><h2 id="pp-faq-h" class="pp-display"><?php esc_html_e( 'Questions, answered', 'pokepiece' ); ?></h2></div>
			<div class="pp-faq__list"><?php foreach ( pp_faqs() as $q ) : ?><details class="pp-disclose"><summary><?php echo esc_html( $q[0] ); ?><?php echo pp_icon( 'chev' ); // phpcs:ignore ?></summary><div><p><?php echo esc_html( $q[1] ); ?></p></div></details><?php endforeach; ?></div>
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

	<section class="pp-community" aria-labelledby="pp-comm-h">
		<div class="pp-wrap">
			<div class="pp-head"><p class="pp-script"><?php esc_html_e( 'follow along', 'pokepiece' ); ?></p><h2 id="pp-comm-h" class="pp-display"><?php esc_html_e( 'On TikTok', 'pokepiece' ); ?></h2>
			<p class="pp-comm__bio"><?php esc_html_e( 'Live TCG Rip & Ship · Big pulls · Trusted stock · Multiple TCGs', 'pokepiece' ); ?></p></div>
		</div>
		<?php $clips = pp_tiktok_items(); if ( $clips ) : ?>
		<div class="pp-reel" style="--n:<?php echo (int) count( $clips ); ?>" role="region" aria-label="<?php esc_attr_e( 'Latest from TikTok', 'pokepiece' ); ?>">
			<div class="pp-reel__track">
				<?php for ( $loop = 0; $loop < 2; $loop++ ) : ?>
				<ul class="pp-reel__set"<?php echo $loop ? ' aria-hidden="true"' : ''; ?>>
					<?php foreach ( $clips as $c ) : ?>
					<li class="pp-clip"><a href="<?php echo esc_url( $c['url'] ); ?>" rel="noopener"<?php echo $loop ? ' tabindex="-1"' : ''; ?>><img src="<?php echo esc_url( $c['thumb'] ); ?>" alt="" width="360" height="640" decoding="async" draggable="false"><span class="pp-clip__tt"><?php echo pp_art( 'tiktok' ); // phpcs:ignore ?></span><span class="pp-clip__cap"><?php echo esc_html( wp_trim_words( $c['title'], 14 ) ); ?></span><span class="screen-reader-text"><?php esc_html_e( 'Watch on TikTok', 'pokepiece' ); ?></span></a></li>
					<?php endforeach; ?>
				</ul>
				<?php endfor; ?>
			</div>
		</div>
		<?php endif; ?>
		<div class="pp-wrap pp-comm__ctas">
			<?php echo pp_cta( __( 'Follow on TikTok', 'pokepiece' ), $tt, 'light', ' rel="noopener"', 'tiktok' ); // phpcs:ignore ?>
			<?php if ( $dc ) { echo pp_cta( __( 'Join the community', 'pokepiece' ), $dc, 'yellow', ' rel="noopener"', 'discord' ); } // phpcs:ignore ?>
		</div>
	</section>
	<section class="pp-deliver" aria-label="<?php esc_attr_e( 'Delivery and contact', 'pokepiece' ); ?>">
		<div class="pp-wrap pp-deliver__inner">
			<div><p class="pp-script"><?php esc_html_e( 'shipping', 'pokepiece' ); ?></p><p class="pp-deliver__big"><?php esc_html_e( 'We ship from the United Kingdom.', 'pokepiece' ); ?></p><p class="pp-deliver__sub"><?php echo esc_html( pp_dispatch_text() ); ?></p>
			<p class="pp-deliver__links"><?php $sp = get_page_by_path( 'shipping-policy' ); if ( $sp ) : ?><a class="pp-ulink" href="<?php echo esc_url( get_permalink( $sp ) ); ?>"><?php esc_html_e( 'Shipping policy', 'pokepiece' ); ?></a><?php endif; ?>
			<?php echo pp_upgrade_card( 'listing' ); // phpcs:ignore ?></p></div>
			<div><p class="pp-script"><?php esc_html_e( 'questions?', 'pokepiece' ); ?></p><p class="pp-deliver__big"><a href="mailto:<?php echo esc_attr( pp_contact_email() ); ?>"><?php echo esc_html( pp_contact_email() ); ?></a></p><p class="pp-deliver__sub"><?php esc_html_e( 'For any questions regarding orders, products or support. We aim to respond within 24 to 48 hours.', 'pokepiece' ); ?></p></div>
		</div>
	</section>
</main>
<?php get_footer();
