<form role="search" method="get" class="pp-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="pp-s-<?php echo esc_attr( wp_unique_id() ); ?>"><?php esc_html_e( 'Search products', 'pokepiece' ); ?></label>
	<?php $sid = 'pp-s-' . ( wp_unique_id() - 1 ); ?>
	<span class="pp-search__icon"><?php echo pp_icon( 'search' ); ?></span>
	<input id="<?php echo esc_attr( $sid ); ?>" class="pp-search__input" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search sets, codes or products', 'pokepiece' ); ?>" autocomplete="off" enterkeyhint="search" aria-autocomplete="list" aria-controls="<?php echo esc_attr( $sid ); ?>-list">
	<input type="hidden" name="post_type" value="product">
	<button class="pp-btn pp-btn--primary pp-search__btn" type="submit"><?php esc_html_e( 'Search', 'pokepiece' ); ?></button>
	<div class="pp-suggest" id="<?php echo esc_attr( $sid ); ?>-list" hidden><p class="pp-suggest__status" role="status" aria-live="polite"></p><ul></ul></div>
</form>
