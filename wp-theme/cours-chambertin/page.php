<?php
/**
 * Gabarit des pages statiques (équivalent des fragments de content/*.html
 * assemblés par render_page() dans build.py).
 *
 * Le <h1> de la page n'est PAS généré ici : chaque contenu commence par son
 * propre "<h1 class="cc-page-title">" (voir CLAUDE.md, règle « un seul h1
 * par page »), collé tel quel dans l'éditeur de blocs depuis content/*.html.
 * Ne pas ajouter the_title() ici sous peine de dupliquer le titre.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	the_content();
endwhile;

get_footer();
