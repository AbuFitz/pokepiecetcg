<?php
/**
 * PokePiece editorial: presentation-only enhancements.
 * WooCommerce retains the authoritative product, order and customer state.
 */
defined('ABSPATH') || exit;

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('pokepiece-editorial', get_stylesheet_uri(), array(), '0.1.0');
    wp_enqueue_script('pokepiece-editorial', get_stylesheet_directory_uri() . '/assets/storefront.js', array(), '0.1.0', true);
});
add_action('after_setup_theme', function () {
    add_theme_support('woocommerce');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
});
/** Only real merchandise is eligible for editorial discovery. Conservative exclusions. */
function pp_is_merchandise($product) {
    if (!$product || !($product instanceof WC_Product)) return false;
    $name = strtolower($product->get_name());
    $sku = strtolower($product->get_sku());
    $blocked = array('shipping upgrade', 'postage upgrade', 'special delivery', 'shipping service');
    foreach ($blocked as $term) if (strpos($name, $term) !== false || strpos($sku, str_replace(' ', '-', $term)) !== false) return false;
    return $product->is_visible();
}
/** Never manufacture product images, availability or prices. */
function pp_product_card($product) {
    if (!pp_is_merchandise($product)) return;
    $url = get_permalink($product->get_id());
    $available = $product->is_purchasable() && $product->is_in_stock();
    echo '<article class="pp-product">';
    echo '<a class="pp-productmedia" href="'.esc_url($url).'">';
    if (!$product->is_in_stock()) echo '<span class="pp-stocktag">Out of stock</span>';
    echo $product->get_image('woocommerce_thumbnail', array('loading'=>'lazy'));
    echo '</a><div class="pp-productmeta">';
    $categories = wc_get_product_category_list($product->get_id(), ', ');
    if ($categories) echo '<div class="pp-eyebrow">'.wp_kses_post($categories).'</div>';
    echo '<a href="'.esc_url($url).'"><h3 class="pp-producttitle">'.esc_html($product->get_name()).'</h3></a>';
    echo '<div class="pp-productprice">'.wp_kses_post($product->get_price_html()).'</div>';
    echo '<div class="pp-stock '.($available ? '' : 'is-out').'">'.($available ? 'Available to order' : 'Currently unavailable').'</div>';
    echo '</div><a class="pp-productaction" href="'.esc_url($url).'">'.($available ? 'Explore product' : 'View details').' ↗</a></article>';
}
function pp_editorial_products($limit = 8) {
    if (!function_exists('wc_get_products')) return array();
    $items = wc_get_products(array('status'=>'publish','limit'=>60,'orderby'=>'date','order'=>'DESC'));
    $eligible = array_values(array_filter($items, 'pp_is_merchandise'));
    usort($eligible, function($a,$b){ return (int)$b->is_in_stock() <=> (int)$a->is_in_stock(); });
    return array_slice($eligible, 0, $limit);
}
function pp_cat_link($search, $fallback = '') {
    $terms = get_terms(array('taxonomy'=>'product_cat','hide_empty'=>false,'search'=>$search,'number'=>1));
    if (!is_wp_error($terms) && !empty($terms)) return get_term_link($terms[0]);
    return $fallback ?: (function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/'));
}
function pp_page_link($slug) {
    $p = get_page_by_path($slug);
    return $p ? get_permalink($p) : home_url('/'.$slug.'/');
}
/** Route product searches through WooCommerce rather than WordPress posts. */
add_filter('get_search_form', function($form) {
    return $form;
});
/** Preserve Woo's templates, gateways, account endpoints, notices and existing store session. */
