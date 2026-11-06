<?php defined('ABSPATH') || exit; ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="pp-skip" href="#pp-main">Skip to content</a>
<div class="pp-topline">POKÉMON · ONE PIECE · DRAGON BALL <span aria-hidden="true">/</span> THE COLLECTOR'S EDIT</div>
<header class="pp-header" id="pp-header">
<div class="pp-nav-shell">
<button class="pp-menutoggle" type="button" aria-controls="pp-mobilemenu" aria-expanded="false" data-pp-menu-open>☰ <span class="screen-reader-text">Open menu</span></button>
<a class="pp-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="PokePieceTCG home"><img src="<?php echo esc_url(get_stylesheet_directory_uri().'/assets/pokepiece-logo.svg'); ?>" alt="PokePieceTCG" onerror="this.style.display='none';this.parentNode.append(document.createTextNode('POKEPIECETCG'))"></a>
<nav class="pp-navlinks" aria-label="Primary navigation">
<a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">Shop all</a>
<a href="<?php echo esc_url(pp_cat_link('Pokémon')); ?>">Pokémon</a>
<a href="<?php echo esc_url(pp_cat_link('One Piece')); ?>">One Piece</a>
<a href="<?php echo esc_url(pp_cat_link('Dragon Ball')); ?>">Dragon Ball</a>
</nav>
<form class="pp-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>"><label class="screen-reader-text" for="pp-search-field">Search products</label><input id="pp-search-field" name="s" type="search" placeholder="Search cards, sets, boxes…" value="<?php echo esc_attr(get_search_query()); ?>"><input name="post_type" type="hidden" value="product"><button type="submit" aria-label="Search products">⌕</button></form>
<div class="pp-tools">
<a href="<?php echo esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>">Account</a>
<a href="<?php echo esc_url(wc_get_cart_url()); ?>">Bag <span class="pp-cartcount"><?php echo WC()->cart ? (int) WC()->cart->get_cart_contents_count() : 0; ?></span></a>
</div>
</div></header>
<div class="pp-backdrop" data-pp-menu-close></div>
<aside class="pp-mobilemenu" id="pp-mobilemenu" role="dialog" aria-modal="true" aria-label="Navigation" aria-hidden="true">
<div class="pp-menuhead"><strong>Explore PokePiece</strong><button type="button" data-pp-menu-close aria-label="Close menu">✕</button></div>
<nav aria-label="Mobile navigation">
<a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">Shop all ↗</a>
<a href="<?php echo esc_url(pp_cat_link('Pokémon')); ?>">Pokémon ↗</a>
<a href="<?php echo esc_url(pp_cat_link('One Piece')); ?>">One Piece ↗</a>
<a href="<?php echo esc_url(pp_cat_link('Dragon Ball')); ?>">Dragon Ball ↗</a>
<a href="<?php echo esc_url(pp_page_link('about')); ?>">Our story ↗</a>
<a href="<?php echo esc_url(pp_page_link('contact')); ?>">Contact ↗</a>
<a href="<?php echo esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>">My account ↗</a>
</nav></aside>
