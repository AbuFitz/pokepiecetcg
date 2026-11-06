<?php defined('ABSPATH') || exit; ?>
<footer class="pp-foot"><div class="pp-wrap">
<div class="pp-footgrid">
<div><div class="pp-footbrand">POKEPIECE<span style="color:#f4c641">TCG.</span></div><p style="max-width:300px;color:#b6bdb8">Built for collectors. A UK trading-card shop for Pokémon, One Piece and more.</p></div>
<div><h3>Explore</h3><a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">Shop all</a><a href="<?php echo esc_url(pp_cat_link('Pokémon')); ?>">Pokémon</a><a href="<?php echo esc_url(pp_cat_link('One Piece')); ?>">One Piece</a><a href="<?php echo esc_url(pp_cat_link('Dragon Ball')); ?>">Dragon Ball</a></div>
<div><h3>Help</h3><a href="<?php echo esc_url(pp_page_link('contact')); ?>">Contact</a><a href="<?php echo esc_url(pp_page_link('shipping-policy')); ?>">Shipping policy</a><a href="<?php echo esc_url(pp_page_link('return-policy')); ?>">Returns</a><a href="<?php echo esc_url(get_permalink(get_option('woocommerce_myaccount_page_id'))); ?>">Account</a></div>
<div><h3>Information</h3><a href="<?php echo esc_url(pp_page_link('about')); ?>">About</a><a href="<?php echo esc_url(pp_page_link('privacy-policy')); ?>">Privacy</a><a href="<?php echo esc_url(pp_page_link('terms-and-conditions')); ?>">Terms</a></div>
</div><div class="pp-footbottom">© <?php echo esc_html(date_i18n('Y')); ?> PokePieceTCG. All rights reserved.</div>
</div></footer>
<?php wp_footer(); ?>
</body></html>
