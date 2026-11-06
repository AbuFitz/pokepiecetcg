<?php get_header(); ?>
<main id="main" class="pp-main" tabindex="-1"><div class="pp-wrap">
<?php while ( have_posts() ) : the_post(); $woo = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ); ?>
	<article <?php post_class( 'pp-page' . ( $woo ? ' pp-page--woo' : '' ) ); ?>>
		<h1 class="pp-page__title"><?php the_title(); ?></h1>
		<div class="<?php echo $woo ? 'pp-woo-content' : 'pp-prose'; ?>"><?php the_content(); ?></div>
	</article>
<?php endwhile; ?>
</div></main>
<?php get_footer();
