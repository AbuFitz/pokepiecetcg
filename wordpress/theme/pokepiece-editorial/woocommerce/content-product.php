<?php
/** Product card. Override reason: bespoke card markup (language/mode/stock facts, image plate). Based on Woo's content-product.php structure. */
defined( 'ABSPATH' ) || exit;
global $product;
if ( empty( $product ) || ! $product->is_visible() ) { return; }
[ $sk, $st ] = pp_stock_state( $product );
$lang = pp_product_language( $product ); $fmt = pp_product_format_label( $product );
$svc = pp_is_service( $product->get_id() );
?>
<?php $game = pp_product_game( $product ); ?>
<li <?php wc_product_class( 'pp-card pp-card--' . $sk . ' pp-game-' . ( $game ?: 'all' ) . ( $svc ? ' pp-card--service' : '' ), $product ); ?> data-pid="<?php echo (int) $product->get_id(); ?>" data-mode="<?php echo esc_attr( pp_product_mode( $product ) ); ?>" data-stock="<?php echo esc_attr( $sk ); ?>" data-price="<?php echo esc_attr( (float) $product->get_price() ); ?>" data-name="<?php echo esc_attr( mb_strtolower( $product->get_name() ) ); ?>" data-date="<?php echo (int) $product->get_date_created()->getTimestamp(); ?>">
	<div class="pp-card__plate"<?php echo pp_plate_attrs( $product ); // phpcs:ignore ?>>
		<a href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php echo $product->get_image( 'pp-plate', array( 'loading' => 'lazy', 'alt' => '' ) ); // phpcs:ignore ?></a>
		<?php echo pp_render_mode_chip( $product ); // phpcs:ignore ?>
		<?php if ( ! $svc ) : ?><button type="button" class="pp-qv-open" data-id="<?php echo (int) $product->get_id(); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Quick view: %s', 'pokepiece' ), $product->get_name() ) ); ?>"><?php esc_html_e( 'Quick view', 'pokepiece' ); ?></button><?php endif; ?>
	</div>
	<div class="pp-card__body">
		<p class="pp-card__meta"><?php echo esc_html( implode( ' · ', array_filter( array( $lang, $fmt ) ) ) ); ?><?php echo $svc ? esc_html__( 'Postage service', 'pokepiece' ) : ''; ?></p>
		<h2 class="pp-card__title"><a href="<?php the_permalink(); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h2>
		<p class="pp-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></p>
		<p class="pp-stock pp-stock--<?php echo esc_attr( $sk ); ?>"><span class="pp-stock__dot" aria-hidden="true"></span><?php echo esc_html( $st ); ?></p>
		<div class="pp-card__action">
			<?php if ( $product->is_purchasable() && $product->is_in_stock() ) : woocommerce_template_loop_add_to_cart(); else : ?>
				<a class="pp-btn pp-btn--ghost" href="<?php the_permalink(); ?>"><?php esc_html_e( 'View product', 'pokepiece' ); ?><span class="screen-reader-text">, <?php echo esc_html( $product->get_name() ); ?></span></a>
			<?php endif; ?>
		</div>
	</div>
</li>
