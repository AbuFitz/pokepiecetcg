<?php
$p = $args['product']; [ $sk, $st ] = pp_stock_state( $p );
$simple = $p->is_type( 'simple' ) && $p->is_purchasable() && $p->is_in_stock();
?>
<div class="pp-qvc">
	<div class="pp-qvc__img"><?php echo $p->get_image( 'pp-plate' ); // phpcs:ignore ?></div>
	<div class="pp-qvc__info">
		<h2><?php echo esc_html( $p->get_name() ); ?></h2>
		<?php echo pp_render_facts( $p ); // phpcs:ignore ?>
		<p class="pp-qvc__price"><?php echo wp_kses_post( $p->get_price_html() ); ?></p>
		<p class="pp-stock pp-stock--<?php echo esc_attr( $sk ); ?>"><span class="pp-stock__dot" aria-hidden="true"></span><?php echo esc_html( $st ); ?></p>
		<?php pp_mode_callout( $p ); ?>
		<div class="pp-qvc__actions">
			<?php if ( $simple && ! pp_is_service( $p->get_id() ) ) : ?>
				<a class="pp-btn pp-btn--primary ajax_add_to_cart add_to_cart_button" href="<?php echo esc_url( $p->add_to_cart_url() ); ?>" data-product_id="<?php echo (int) $p->get_id(); ?>" data-quantity="1" rel="nofollow"><?php esc_html_e( 'Add to cart', 'pokepiece' ); ?></a>
			<?php endif; ?>
			<a class="pp-btn pp-btn--ghost" href="<?php echo esc_url( $p->get_permalink() ); ?>"><?php esc_html_e( 'View full details', 'pokepiece' ); ?></a>
		</div>
	</div>
</div>
