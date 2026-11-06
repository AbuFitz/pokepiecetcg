<?php
defined( 'ABSPATH' ) || exit;
get_header();
$groups  = pp_game_groups();
$stocked = pp_discovery_products( array( 'stock_status' => 'instock', 'limit' => 8 ) );
$range   = $stocked ? array() : pp_discovery_products( array( 'limit' => 8 ) );
$stage   = array();
// Stage: real, merchant-supplied product images only; in-stock first, then the rest of the range. Never services.
$pool = array_merge( $stocked, $stocked ? array() : $range, pp_discovery_products( array( 'limit' => 12 ) ) );
$seen = array();
foreach ( $pool as $p ) { if ( isset( $seen[ $p->get_id() ] ) || ! $p->get_image_id() ) { continue; } $seen[ $p->get_id() ] = 1; $stage[] = $p; if ( count( $stage ) === 2 ) { break; } }
?>
<main id="main" class="pp-main pp-home" tabindex="-1">
	<section class="pp-hero" aria-labelledby="pp-hero-h">
		<div class="pp-wrap pp-hero__grid">
			<div class="pp-hero__copy">
				<h1 id="pp-hero-h"><?php echo esc_html( apply_filters( 'pokepiece_hero_title', __( 'Built for collectors.', 'pokepiece' ) ) ); ?></h1>
				<p><?php echo esc_html( apply_filters( 'pokepiece_hero_text', __( 'Pokémon, One Piece and Dragon Ball booster boxes and packs. Every product says whether it is sealed, no shrink or opened live.', 'pokepiece' ) ) ); ?></p>
				<p class="pp-hero__actions"><a class="pp-btn pp-btn--primary pp-btn--lg" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Shop all products', 'pokepiece' ); ?><?php echo pp_icon( 'arrow' ); ?></a>
				<?php if ( isset( $groups['pokemon'] ) ) : ?><a class="pp-link" href="<?php echo esc_url( $groups['pokemon']['items'][0]['url'] ); ?>"><?php echo esc_html( $groups['pokemon']['name'] . ' ' . $groups['pokemon']['items'][0]['name'] ); ?></a><?php endif; ?></p>
			</div>
			<div class="pp-hero__stage" aria-label="<?php esc_attr_e( 'Featured products', 'pokepiece' ); ?>">
				<span class="pp-ring" aria-hidden="true"></span>
				<?php foreach ( $stage as $i => $p ) : [ $sk, $st ] = pp_stock_state( $p ); ?>
				<a class="pp-stage pp-stage--<?php echo (int) $i; ?>" href="<?php echo esc_url( $p->get_permalink() ); ?>">
					<span class="pp-stage__img"><?php echo $p->get_image( 'pp-plate', array( 'loading' => $i ? 'lazy' : 'eager', 'fetchpriority' => $i ? 'low' : 'high', 'alt' => '' ) ); // phpcs:ignore ?></span>
					<span class="pp-stage__cap"><strong><?php echo esc_html( $p->get_name() ); ?></strong><span><?php echo wp_kses_post( $p->get_price_html() ); ?> · <?php echo esc_html( $st ); ?></span></span>
				</a>
				<?php endforeach; ?>
				<?php if ( ! $stage ) : ?><img class="pp-stage__mark" src="<?php echo esc_url( PP_URI . '/assets/brand/symbol-colour.svg' ); ?>" alt="" width="180" height="180"><?php endif; ?>
			</div>
		</div>
	</section>

	<section class="pp-section pp-gateways" aria-labelledby="pp-gw-h">
		<div class="pp-wrap">
			<h2 id="pp-gw-h" class="pp-h2"><?php esc_html_e( 'Choose your game', 'pokepiece' ); ?></h2>
			<ul class="pp-gw">
				<?php foreach ( $groups as $g ) : ?>
				<li class="pp-gw__item"><h3><?php echo esc_html( $g['name'] ); ?></h3><ul>
					<?php foreach ( $g['items'] as $it ) : ?><li><a href="<?php echo esc_url( $it['url'] ); ?>"><?php echo esc_html( $it['name'] ); ?><span><?php echo esc_html( sprintf( _n( '%d product', '%d products', $it['count'], 'pokepiece' ), $it['count'] ) ); ?><?php echo pp_icon( 'arrow' ); ?></span></a></li><?php endforeach; ?>
				</ul></li>
				<?php endforeach; ?>
			</ul>
		</div>
	</section>

	<section class="pp-section" aria-labelledby="pp-col-h">
		<div class="pp-wrap">
			<div class="pp-sechead"><h2 id="pp-col-h" class="pp-h2"><?php echo $stocked ? esc_html__( 'In stock now', 'pokepiece' ) : esc_html__( 'The range', 'pokepiece' ); ?></h2><a class="pp-link" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) . ( $stocked ? '?pp_stock=1' : '' ) ); ?>"><?php esc_html_e( 'View all', 'pokepiece' ); ?></a></div>
			<?php if ( ! $stocked ) : ?>
			<div class="pp-note" role="status"><p><strong><?php esc_html_e( 'Everything we list is out of stock right now.', 'pokepiece' ); ?></strong> <?php esc_html_e( 'Browse the range below or get in touch.', 'pokepiece' ); ?>
			<?php $c = get_page_by_path( 'contact' ); if ( $c ) : ?> <a href="<?php echo esc_url( get_permalink( $c ) ); ?>"><?php esc_html_e( 'Contact us', 'pokepiece' ); ?></a><?php endif; ?></p></div>
			<?php endif; ?>
			<?php $list = $stocked ?: $range; if ( $list ) : ?>
			<ul class="products pp-products columns-4">
				<?php foreach ( $list as $p ) { $GLOBALS['post'] = get_post( $p->get_id() ); setup_postdata( $GLOBALS['post'] ); $GLOBALS['product'] = $p; wc_get_template_part( 'content', 'product' ); } wp_reset_postdata(); ?>
			</ul>
			<?php endif; ?>
		</div>
	</section>

	<section class="pp-section pp-modes" aria-labelledby="pp-modes-h">
		<div class="pp-wrap">
			<h2 id="pp-modes-h" class="pp-h2"><?php esc_html_e( 'How each product reaches you', 'pokepiece' ); ?></h2>
			<ul class="pp-modes__list">
				<li><span class="pp-mode pp-mode--sealed"><span class="pp-mode__dot" aria-hidden="true"></span><?php esc_html_e( 'Sealed', 'pokepiece' ); ?></span><p><?php esc_html_e( 'Delivered sealed. Any packaging condition is written on the product page.', 'pokepiece' ); ?></p><a class="pp-link" href="<?php echo esc_url( add_query_arg( 'pp_mode', 'sealed', wc_get_page_permalink( 'shop' ) ) ); ?>"><?php esc_html_e( 'See sealed products', 'pokepiece' ); ?></a></li>
				<li><span class="pp-mode pp-mode--no_shrink"><span class="pp-mode__dot" aria-hidden="true"></span><?php esc_html_e( 'No shrink', 'pokepiece' ); ?></span><p><?php esc_html_e( 'Sent without shrink-wrap. The condition is stated plainly beside the price.', 'pokepiece' ); ?></p><a class="pp-link" href="<?php echo esc_url( add_query_arg( 'pp_mode', 'no_shrink', wc_get_page_permalink( 'shop' ) ) ); ?>"><?php esc_html_e( 'See no-shrink products', 'pokepiece' ); ?></a></li>
				<li><span class="pp-mode pp-mode--live"><span class="pp-mode__dot" aria-hidden="true"></span><?php esc_html_e( 'Live opening', 'pokepiece' ); ?></span><p><?php esc_html_e( 'Opened live, then the cards are shipped to you. Not a sealed product.', 'pokepiece' ); ?></p><a class="pp-link" href="<?php echo esc_url( add_query_arg( 'pp_mode', 'live', wc_get_page_permalink( 'shop' ) ) ); ?>"><?php esc_html_e( 'See live-opening products', 'pokepiece' ); ?></a></li>
			</ul>
		</div>
	</section>

	<section class="pp-section pp-strip" aria-label="<?php esc_attr_e( 'Delivery', 'pokepiece' ); ?>">
		<div class="pp-wrap"><p><strong><?php esc_html_e( 'Delivery', 'pokepiece' ); ?></strong> <?php echo esc_html( pp_dispatch_text() ); ?>
		<?php $sp = get_page_by_path( 'shipping-policy' ); if ( $sp ) : ?> <a href="<?php echo esc_url( get_permalink( $sp ) ); ?>"><?php esc_html_e( 'Shipping policy', 'pokepiece' ); ?></a><?php endif; ?></p></div>
	</section>
</main>
<?php get_footer();
