<?php
/**
 * Gabarit de repli (archives, résultats de recherche, 404...). La page
 * « Vie & actualités » elle-même reste une Page classique avec un bloc
 * Derniers articles / bento manuel — ce gabarit ne sert que de filet de
 * sécurité pour les vues que WordPress requiert par défaut.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="wp-block-group cc-section cc-section--blanc">
	<div class="wp-block-group cc-narrow">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : ?>
				<?php the_post(); ?>
				<article>
					<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
					<p class="surtitre"><?php echo esc_html( get_the_date() ); ?></p>
					<?php the_excerpt(); ?>
				</article>
			<?php endwhile; ?>
		<?php else : ?>
			<h1 class="cc-page-title"><?php esc_html_e( 'Rien à afficher', 'cours-chambertin' ); ?></h1>
		<?php endif; ?>
	</div>
</div>
<?php
get_footer();
