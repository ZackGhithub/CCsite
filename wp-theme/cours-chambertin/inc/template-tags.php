<?php
/**
 * Fonctions d'affichage réutilisées dans les templates.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Slug de la page racine de la page courante ('ecole', 'college', ou ''
 * pour les pages communes) — équivalent de section_of() dans build.py,
 * mais dérivé de la hiérarchie WordPress plutôt que d'une liste d'ID en dur.
 */
function cc_current_section() {
	if ( ! is_page() && ! is_singular( 'post' ) ) {
		return '';
	}
	$queried = get_queried_object();
	if ( ! $queried ) {
		return '';
	}
	$ancestors = get_post_ancestors( $queried );
	$top       = $ancestors ? get_post( end( $ancestors ) ) : $queried;
	if ( ! $top instanceof WP_Post ) {
		return '';
	}
	return in_array( $top->post_name, array( 'ecole', 'college' ), true ) ? $top->post_name : '';
}

/**
 * Fil d'Ariane à partir de la hiérarchie de pages WordPress (équivalent de
 * breadcrumb_html() dans build.py). Vide sur la page d'accueil.
 */
function cc_breadcrumb() {
	if ( is_front_page() ) {
		return;
	}

	$parts = array( '<a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Accueil', 'cours-chambertin' ) . '</a>' );

	if ( is_page() ) {
		$ancestors = array_reverse( get_post_ancestors( get_queried_object_id() ) );
		foreach ( $ancestors as $ancestor_id ) {
			$parts[] = '<a href="' . esc_url( get_permalink( $ancestor_id ) ) . '">' . esc_html( get_the_title( $ancestor_id ) ) . '</a>';
		}
		$parts[] = '<span aria-current="page">' . esc_html( get_the_title() ) . '</span>';
	} elseif ( is_singular( 'post' ) ) {
		$blog_page_id = get_option( 'page_for_posts' );
		if ( $blog_page_id ) {
			$parts[] = '<a href="' . esc_url( get_permalink( $blog_page_id ) ) . '">' . esc_html( get_the_title( $blog_page_id ) ) . '</a>';
		}
		$parts[] = '<span aria-current="page">' . esc_html( get_the_title() ) . '</span>';
	}

	echo '<nav class="cc-breadcrumb" aria-label="' . esc_attr__( 'Fil d’Ariane', 'cours-chambertin' ) . '">'
		. implode( ' <span class="sep">/</span> ', $parts )
		. '</nav>';
}

/**
 * Logo à afficher dans l'en-tête : logo custom WordPress (Personnaliser >
 * Identité du site) si réglé, sinon repli sur le nom du site en texte.
 * Pour distinguer visuellement École / Collège comme sur la maquette
 * statique (trois fichiers logo différents), régler un logo custom distinct
 * par page d'accueil de section, ou brancher un champ dédié (ACF ou
 * theme mod) filtrant sur cc_current_section().
 */
function cc_site_logo() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	printf(
		'<a class="cc-logo cc-logo--text" href="%s">%s</a>',
		esc_url( home_url( '/' ) ),
		esc_html( get_bloginfo( 'name' ) )
	);
}

/**
 * Logo du pied de page : la maquette statique utilisait un fichier dédié
 * ("cc-logo-master-blanc-plein.png", déjà importé dans la médiathèque par
 * la migration) plutôt que le logo custom de l'en-tête — celui-ci est
 * généralement sombre et deviendrait illisible sur le fond foncé du pied
 * de page. Repli sur le logo custom standard si ce fichier n'a pas été
 * trouvé (site pas encore migré, ou fichier renommé/supprimé).
 */
function cc_footer_logo() {
	$query = new WP_Query(
		array(
			'post_type'      => 'attachment',
			'post_status'    => 'inherit',
			'title'          => 'cc-logo-master-blanc-plein',
			'posts_per_page' => 1,
			'fields'         => 'ids',
		)
	);
	if ( $query->posts ) {
		echo wp_get_attachment_image( $query->posts[0], 'medium', false, array( 'alt' => get_bloginfo( 'name' ) ) );
		return;
	}
	if ( has_custom_logo() ) {
		the_custom_logo();
	}
}

/**
 * Bandeau d'annonce sitewide (ex. ouverture de la préinscription), fermable
 * et mémorisé par clé (assets/js/main.js). Le lien cible une page par son
 * chemin plutôt qu'un ID ou une URL codée en dur, pour rester valide même
 * si l'ID de la page change ; à défaut de page trouvée, le bandeau ne
 * s'affiche pas. Message et cible réglables via le Customizer
 * (theme mods `cc_announce_message` et `cc_announce_target_path`).
 */
function cc_announcement_bar() {
	$target_path = get_theme_mod( 'cc_announce_target_path', 'college/admissions' );
	$target      = get_page_by_path( $target_path );
	if ( ! $target ) {
		return;
	}
	$message = get_theme_mod( 'cc_announce_message', '<strong>Ouverture de la préinscription</strong> — Collège Chambertin' );
	$key     = 'preinscription-college-2027';
	?>
	<div id="cc-announce" class="cc-announce" data-announce-id="<?php echo esc_attr( $key ); ?>">
		<div class="cc-announce-inner">
			<a href="<?php echo esc_url( get_permalink( $target ) ); ?>"><?php echo wp_kses_post( $message ); ?></a>
			<button type="button" class="cc-announce-close" aria-label="<?php esc_attr_e( 'Fermer ce message', 'cours-chambertin' ); ?>">&times;</button>
		</div>
	</div>
	<?php
}

/**
 * Aligne les classes de wp_nav_menu() sur celles utilisées par la feuille
 * de style (has-children / current), reprises de la maquette statique,
 * plutôt que de réécrire le CSS pour les classes natives de WordPress.
 */
function cc_nav_menu_classes( $classes, $item ) {
	if ( in_array( 'menu-item-has-children', $classes, true ) ) {
		$classes[] = 'has-children';
	}
	foreach ( $classes as $class ) {
		if ( 0 === strpos( $class, 'current' ) ) {
			$classes[] = 'current';
			break;
		}
	}
	return $classes;
}
add_filter( 'nav_menu_css_class', 'cc_nav_menu_classes', 10, 2 );
