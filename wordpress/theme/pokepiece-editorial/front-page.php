<?php
defined('ABSPATH') || exit;
get_header();
$products = function_exists('wc_get_products') ? pp_editorial_products(8) : array();
$hero_product = count($products) ? $products[0] : null;
?>
<main id="pp-main">
<div class="pp-wrap">
<section class="pp-hero" aria-labelledby="pp-hero-heading">
<div class="pp-hero-copy">
<div class="pp-eyebrow" style="color:var(--yellow)">YOUR NEXT FIND STARTS HERE / 001</div>
<h1 id="pp-hero-heading">THE ART OF <em>THE PULL.</em></h1>
<p>Pokémon, One Piece and trading card releases for the collection — or the next opening.</p>
<div class="pp-hero-actions"><a class="pp-btn pp-btn-red" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">Explore the shop <span aria-hidden="true">↗</span></a><a class="pp-btn pp-btn-line" href="<?php echo esc_url(pp_cat_link('Pokémon')); ?>">Shop Pokémon</a></div>
</div>
<div class="pp-stage" aria-label="Featured collection">
<?php if ($hero_product): ?><a href="<?php echo esc_url(get_permalink($hero_product->get_id())); ?>" style="display:flex;align-items:center;justify-content:center;width:100%;height:100%"><?php echo $hero_product->get_image('woocommerce_single'); ?></a><?php else: ?><span style="font-family:Impact,sans-serif;font-size:clamp(40px,7vw,100px);z-index:2">POKEPIECE</span><?php endif; ?>
<span class="pp-stage-note">Actual catalogue imagery · Availability shown below</span>
</div></section>
<div class="pp-announcement"><strong>FIND YOUR NEXT SET</strong><span>Browse English and Japanese releases. Check each listing for packaging and fulfilment details.</span></div>
<section class="pp-section" aria-labelledby="pp-category-title">
<div class="pp-sectionhead"><div><span class="pp-eyebrow">01 / The collections</span><h2 id="pp-category-title">PICK YOUR UNIVERSE.</h2></div><a class="pp-textlink" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">All products ↗</a></div>
<div class="pp-categories">
<a class="pp-category" href="<?php echo esc_url(pp_cat_link('Pokémon')); ?>"><span>POKÉMON</span></a>
<a class="pp-category" href="<?php echo esc_url(pp_cat_link('One Piece')); ?>"><span>ONE PIECE</span></a>
<a class="pp-category" href="<?php echo esc_url(pp_cat_link('Dragon Ball')); ?>"><span>DRAGON BALL</span></a>
</div></section>
<section class="pp-section" aria-labelledby="pp-collection-title" style="padding-top:0">
<div class="pp-sectionhead"><div><span class="pp-eyebrow">02 / The catalogue</span><h2 id="pp-collection-title">THE LATEST EDIT.</h2></div><a class="pp-textlink" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">Browse everything ↗</a></div>
<?php if ($products): ?><div class="pp-productgrid"><?php foreach ($products as $product) pp_product_card($product); ?></div><?php else: ?><p>Our catalogue is being updated. Browse all products or get in touch about a release.</p><a class="pp-btn pp-btn-red" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">Explore all products</a><?php endif; ?>
</section>
</div>
<section class="pp-feature" aria-label="Collector experience">
<div class="pp-feature-copy"><span class="pp-eyebrow">03 / Beyond the sealed box</span><h2>YOUR CARDS.<br>YOUR CALL.</h2><p>Shopping for sealed product or a live opening? Each listing should tell you exactly what you're buying, how it will be fulfilled and whether it's currently available.</p><a class="pp-btn pp-btn-white" href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>">See available products ↗</a></div>
<div class="pp-feature-art" aria-hidden="true"><span>RIP / SHIP</span></div>
</section>
<div class="pp-wrap">
<section class="pp-section" aria-labelledby="pp-trust-title">
<div class="pp-sectionhead"><div><span class="pp-eyebrow">04 / The details matter</span><h2 id="pp-trust-title">COLLECT WITH CONFIDENCE.</h2></div></div>
<div class="pp-steps"><div class="pp-step"><span class="pp-eyebrow">01</span><h3>Know what you're getting.</h3><p>Look for product format, language and packaging details on each listing before you buy.</p></div><div class="pp-step"><span class="pp-eyebrow">02</span><h3>Real stock, real prices.</h3><p>Browse actual store listings and current availability. Unavailable products stay clearly labelled.</p></div><div class="pp-step"><span class="pp-eyebrow">03</span><h3>Questions about a release?</h3><p>Not sure about a set or fulfilment option? Reach out before placing your order.</p><a class="pp-textlink" href="<?php echo esc_url(pp_page_link('contact')); ?>">Contact the shop ↗</a></div></div>
</section></div>
</main>
<?php get_footer(); ?>
