<?php defined( 'ABSPATH' ) || exit; $groups = pp_game_groups(); $cur = is_tax( 'product_cat' ) ? get_queried_object()->slug : ''; ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="pp-skip" href="#main"><?php esc_html_e( 'Skip to content', 'pokepiece' ); ?></a>
<p class="pp-service"><?php echo esc_html( pp_dispatch_text() ); ?></p>
<header class="pp-header" id="pp-header">
	<div class="pp-frame">
		<div class="pp-frame__top">
			<button class="pp-iconbtn pp-menu-open" type="button" aria-controls="pp-menu" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open menu', 'pokepiece' ); ?>"><?php echo pp_icon( 'menu' ); ?></button>
			<a class="pp-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?> — <?php esc_attr_e( 'home', 'pokepiece' ); ?>"><img src="<?php echo esc_url( PP_URI . '/assets/brand/horizontal-colour.svg' ); ?>" alt="" width="200" height="50"></a>
			<nav class="pp-nav" aria-label="<?php esc_attr_e( 'Main', 'pokepiece' ); ?>">
				<ul>
					<li><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"<?php echo is_shop() ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'Shop all', 'pokepiece' ); ?></a></li>
					<?php foreach ( $groups as $k => $g ) : $active = $cur && str_starts_with( $cur, $g['prefix'] ); ?>
					<li class="pp-nav__group">
						<button type="button" class="pp-nav__btn" aria-expanded="false" aria-controls="pp-sub-<?php echo esc_attr( $k ); ?>"<?php echo $active ? ' data-active="1"' : ''; ?>><?php echo esc_html( $g['name'] ); ?><?php echo pp_icon( 'chev' ); ?></button>
						<ul class="pp-nav__sub" id="pp-sub-<?php echo esc_attr( $k ); ?>" hidden>
							<?php foreach ( $g['items'] as $it ) : ?><li><a href="<?php echo esc_url( $it['url'] ); ?>"<?php echo $cur === $it['slug'] ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $it['name'] ); ?> <em>(<?php echo (int) $it['count']; ?>)</em></a></li><?php endforeach; ?>
						</ul>
					</li>
					<?php endforeach; ?>
				</ul>
			</nav>
			<div class="pp-actions">
				<a class="pp-iconbtn pp-act-account" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" aria-label="<?php esc_attr_e( 'My account', 'pokepiece' ); ?>"><?php echo pp_icon( 'user' ); ?></a>
				<button class="pp-iconbtn pp-cart-open" type="button" aria-controls="pp-cart-drawer" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open cart', 'pokepiece' ); ?>"><?php echo pp_icon( 'cart' ); ?><?php echo pp_cart_count_html(); // phpcs:ignore ?></button>
			</div>
		</div>
		<div class="pp-frame__search">
			<?php get_search_form(); ?>
		</div>
	</div>
</header>

<div class="pp-menu" id="pp-menu" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Menu', 'pokepiece' ); ?>" hidden>
	<div class="pp-menu__head"><span class="pp-menu__title"><?php esc_html_e( 'Menu', 'pokepiece' ); ?></span><button class="pp-iconbtn pp-close" type="button" aria-label="<?php esc_attr_e( 'Close menu', 'pokepiece' ); ?>"><?php echo pp_icon( 'close' ); ?></button></div>
	<div class="pp-menu__body">
		<a class="pp-menu__link" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Shop all', 'pokepiece' ); ?></a>
		<?php foreach ( $groups as $k => $g ) : ?>
		<details class="pp-menu__group"<?php echo ( $cur && str_starts_with( $cur, $g['prefix'] ) ) ? ' open' : ''; ?>><summary><?php echo esc_html( $g['name'] ); ?><?php echo pp_icon( 'chev' ); ?></summary>
			<ul><?php foreach ( $g['items'] as $it ) : ?><li><a href="<?php echo esc_url( $it['url'] ); ?>"><?php echo esc_html( $it['name'] ); ?> <em>(<?php echo (int) $it['count']; ?>)</em></a></li><?php endforeach; ?></ul></details>
		<?php endforeach; ?>
		<hr>
		<?php foreach ( array( 'my-account' => __( 'My account', 'pokepiece' ), 'about' => __( 'About', 'pokepiece' ), 'contact' => __( 'Contact', 'pokepiece' ), 'shipping-policy' => __( 'Shipping', 'pokepiece' ) ) as $slug => $label ) : $pg = get_page_by_path( $slug ); if ( $pg ) : ?>
		<a class="pp-menu__link pp-menu__link--minor" href="<?php echo esc_url( get_permalink( $pg ) ); ?>"><?php echo esc_html( $label ); ?></a>
		<?php endif; endforeach; ?>
	</div>
</div>

<div class="pp-backdrop" hidden></div>

<aside class="pp-drawer" id="pp-cart-drawer" role="dialog" aria-modal="true" aria-labelledby="pp-cart-title" hidden>
	<div class="pp-drawer__head"><h2 id="pp-cart-title"><?php esc_html_e( 'Your cart', 'pokepiece' ); ?></h2><button class="pp-iconbtn pp-close" type="button" aria-label="<?php esc_attr_e( 'Close cart', 'pokepiece' ); ?>"><?php echo pp_icon( 'close' ); ?></button></div>
	<p class="pp-drawer__status" role="status" aria-live="polite"></p>
	<?php echo pp_cart_drawer_body(); // phpcs:ignore ?>
</aside>
