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
<div class="pp-bar" role="region" aria-label="<?php esc_attr_e( 'Announcements', 'pokepiece' ); ?>">
	<p class="pp-bar__line" data-pp-bar aria-live="off"><?php foreach ( pp_banner_messages() as $i => $m ) : ?><a class="pp-bar__msg<?php echo 0 === $i ? ' is-on' : ''; ?>" href="<?php echo esc_url( $m[1] ); ?>"<?php echo 0 === $i ? '' : ' tabindex="-1"'; ?>><?php echo esc_html( $m[0] ); ?></a><?php endforeach; ?></p>
</div>
<header class="pp-header" id="pp-header">
	<div class="pp-frame">
		<div class="pp-frame__top">
			<button class="pp-iconbtn pp-menu-open" type="button" aria-controls="pp-menu" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open menu', 'pokepiece' ); ?>"><?php echo pp_icon( 'menu' ); ?></button>
			<a class="pp-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>, <?php esc_attr_e( 'home', 'pokepiece' ); ?>"><img src="<?php echo esc_url( PP_URI . '/assets/brand/horizontal-colour.svg' ); ?>" alt="" width="200" height="50"></a>
			<nav class="pp-nav" aria-label="<?php esc_attr_e( 'Main', 'pokepiece' ); ?>">
				<ul>
					<li><a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"<?php echo is_shop() ? ' aria-current="page"' : ''; ?>><?php esc_html_e( 'Shop all', 'pokepiece' ); ?></a></li>
					<?php foreach ( $groups as $k => $g ) : $active = pp_group_active( $g, $cur ); if ( empty( $g['items'] ) ) : ?>
					<li><a href="<?php echo esc_url( $g['url'] ); ?>"<?php echo $active ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $g['name'] ); ?></a></li>
					<?php continue; endif; ?>
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
				<button class="pp-searchbtn" type="button" data-pp-search aria-haspopup="dialog" aria-controls="pp-search-pop"><?php echo pp_icon( 'search' ); ?><span><?php esc_html_e( 'Search', 'pokepiece' ); ?></span></button>
				<a class="pp-iconbtn pp-act-account" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" aria-label="<?php echo is_user_logged_in() ? esc_attr__( 'My account', 'pokepiece' ) : esc_attr__( 'Sign in or create account', 'pokepiece' ); ?>"<?php echo is_user_logged_in() ? '' : ' data-pp-account aria-haspopup="dialog"'; ?>><?php echo pp_icon( 'user' ); ?></a>
				<button class="pp-iconbtn pp-cart-open" type="button" aria-controls="pp-cart-drawer" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open cart', 'pokepiece' ); ?>"><?php echo pp_icon( 'cart' ); ?><?php echo pp_cart_count_html(); // phpcs:ignore ?></button>
			</div>
		</div>
	</div>
</header>

<div class="pp-menu" id="pp-menu" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Menu', 'pokepiece' ); ?>" hidden>
	<div class="pp-menu__head"><span class="pp-menu__title"><?php esc_html_e( 'Menu', 'pokepiece' ); ?></span><button class="pp-iconbtn pp-close" type="button" aria-label="<?php esc_attr_e( 'Close menu', 'pokepiece' ); ?>"><?php echo pp_icon( 'close' ); ?></button></div>
	<div class="pp-menu__body">
		<a class="pp-menu__link" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Shop all', 'pokepiece' ); ?></a>
		<?php foreach ( $groups as $k => $g ) : if ( empty( $g['items'] ) ) : ?>
		<a class="pp-menu__link" href="<?php echo esc_url( $g['url'] ); ?>"><?php echo esc_html( $g['name'] ); ?></a>
		<?php continue; endif; ?>
		<details class="pp-menu__group"<?php echo pp_group_active( $g, $cur ) ? ' open' : ''; ?>><summary><?php echo esc_html( $g['name'] ); ?><?php echo pp_icon( 'chev' ); ?></summary>
			<ul><?php foreach ( $g['items'] as $it ) : ?><li><a href="<?php echo esc_url( $it['url'] ); ?>"><?php echo esc_html( $it['name'] ); ?> <em>(<?php echo (int) $it['count']; ?>)</em></a></li><?php endforeach; ?></ul></details>
		<?php endforeach; ?>
		<hr>
		<?php foreach ( array( 'my-account' => is_user_logged_in() ? __( 'My account', 'pokepiece' ) : __( 'Sign in / Create account', 'pokepiece' ), 'about' => __( 'About', 'pokepiece' ), 'contact' => __( 'Contact', 'pokepiece' ), 'shipping-policy' => __( 'Shipping', 'pokepiece' ) ) as $slug => $label ) : $pg = get_page_by_path( $slug ); if ( $pg ) : ?>
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
