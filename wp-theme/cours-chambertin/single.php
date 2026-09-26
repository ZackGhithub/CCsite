<?php
/**
 * Gabarit d'un article d'actualité (équivalent de la branche is_news de
 * render_page() dans build.py). Les permaliens WordPress par défaut
 * (/%year%/%monthnum%/%day%/%postname%/) correspondent déjà au format
 * observé dans le contenu d'origine (/2026/08/01/<slug>/).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<div class="wp-block-group cc-section cc-section--craie">
		<div class="wp-block-group cc-narrow">
			<p class="surtitre"><?php echo esc_html( get_the_date() ); ?></p>
			<h1><?php the_title(); ?></h1>
		</div>
	</div>
	<div class="wp-block-group cc-section cc-section--blanc">
		<div class="wp-block-group cc-narrow cc-article">
			<?php the_content(); ?>
		</div>
	</div>
	<?php
endwhile;

get_footer();
