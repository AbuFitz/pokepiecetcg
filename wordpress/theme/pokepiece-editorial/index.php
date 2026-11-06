<?php get_header(); ?>
<main id="main" class="pp-main" tabindex="-1"><div class="pp-wrap pp-prose pp-pagewrap">
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>
	<article <?php post_class(); ?>><h1><?php the_title(); ?></h1><?php the_content(); ?></article>
<?php endwhile; else : ?>
	<h1><?php esc_html_e( 'Nothing here', 'pokepiece' ); ?></h1><p><?php esc_html_e( 'Try the shop instead.', 'pokepiece' ); ?></p>
<?php endif; ?>
</div></main>
<?php get_footer();
