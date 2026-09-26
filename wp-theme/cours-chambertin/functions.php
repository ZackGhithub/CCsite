<?php
/**
 * Cours Chambertin — configuration du thème.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CC_THEME_VERSION', '0.1.0' );

require get_theme_file_path( 'inc/template-tags.php' );
require get_theme_file_path( 'inc/seo.php' );

/**
 * Réglages généraux du thème.
 */
function cc_theme_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 100,
			'width'       => 98,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'Menu principal', 'cours-chambertin' ),
			'footer'  => __( 'Plan du site (pied de page)', 'cours-chambertin' ),
		)
	);
}
add_action( 'after_setup_theme', 'cc_theme_setup' );

/**
 * Feuilles de style et scripts.
 */
function cc_enqueue_assets() {
	wp_enqueue_style(
		'cc-google-fonts',
		'https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Spectral:ital,wght@0,300;0,400;0,600;1,400&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'cours-chambertin-style', get_stylesheet_uri(), array(), CC_THEME_VERSION );
	wp_enqueue_script(
		'cours-chambertin-main',
		get_theme_file_uri( 'assets/js/main.js' ),
		array(),
		CC_THEME_VERSION,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'cc_enqueue_assets' );

/**
 * Catégorie de blocs dédiée aux futurs blocs custom (frise, tri-panel, etc.),
 * pour ne pas les mélanger aux catégories natives dans l'inserteur.
 * Voir le skill .claude/skills/gutenberg-block.md pour la marche à suivre.
 */
function cc_block_categories( $categories ) {
	array_unshift(
		$categories,
		array(
			'slug'  => 'cours-chambertin',
			'title' => __( 'Cours Chambertin', 'cours-chambertin' ),
		)
	);
	return $categories;
}
add_filter( 'block_categories_all', 'cc_block_categories' );

/**
 * Deux zones de widgets pour les blocs d'adresse du pied de page (École et
 * Collège) : éditables au bloc éditeur (Apparence > Widgets) plutôt que
 * codées en dur, avec un repli statique dans footer.php si elles sont vides.
 */
function cc_widgets_init() {
	register_sidebar(
		array(
			'name'          => __( 'Pied de page — Collège', 'cours-chambertin' ),
			'id'            => 'footer-college',
			'before_widget' => '<div class="cc-pied-widget">',
			'after_widget'  => '</div>',
		)
	);
	register_sidebar(
		array(
			'name'          => __( 'Pied de page — École', 'cours-chambertin' ),
			'id'            => 'footer-ecole',
			'before_widget' => '<div class="cc-pied-widget">',
			'after_widget'  => '</div>',
		)
	);
}
add_action( 'widgets_init', 'cc_widgets_init' );
