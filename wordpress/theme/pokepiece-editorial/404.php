<?php get_header(); status_header( 404 ); ?>
<main id="main" class="pp-main" tabindex="-1"><div class="pp-wrap pp-404 pp-pagewrap">
	<p class="pp-kicker">404</p>
	<h1><?php esc_html_e( 'That page isn’t here.', 'pokepiece' ); ?></h1>
	<p><?php esc_html_e( 'The link may be old or mistyped. Search the range or pick a category.', 'pokepiece' ); ?></p>
	<?php get_search_form(); ?>
	<ul class="pp-404__links"><li><a class="pp-btn pp-btn--primary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Shop all products', 'pokepiece' ); ?></a></li>
	<?php foreach ( pp_category_links() as $l ) : ?><li><a class="pp-btn pp-btn--ghost" href="<?php echo esc_url( $l[1] ); ?>"><?php echo esc_html( $l[0] ); ?></a></li><?php endforeach; ?></ul>
	<?php $c = get_page_by_path( 'contact' ); if ( $c ) : ?><p><?php esc_html_e( 'Still stuck?', 'pokepiece' ); ?> <a href="<?php echo esc_url( get_permalink( $c ) ); ?>"><?php esc_html_e( 'Contact us', 'pokepiece' ); ?></a>.</p><?php endif; ?>
</div></main>
<?php get_footer();
