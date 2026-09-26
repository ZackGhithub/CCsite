<?php
/**
 * Métadonnées SEO : titre, Open Graph, Twitter Card, données structurées
 * Schema.org. Équivalent de seo_head_block() / json_ld_for() dans build.py,
 * porté sur les API WordPress plutôt que sur le manifest PAGES en dur.
 *
 * Ce thème n'installe pas de plugin SEO (Yoast, RankMath...) : si l'un
 * d'eux est activé plus tard, désactiver cc_seo_head() ci-dessous pour
 * éviter les métadonnées en double.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Coordonnées réelles de l'institution (École et Collège partagent
 * la même adresse). Ce thème est sur-mesure pour un seul site : ces
 * informations sont donc portées en dur, comme dans build.py.
 */
function cc_institution_address() {
	return array(
		'@type'           => 'PostalAddress',
		'streetAddress'   => '9 avenue de la Marne',
		'postalCode'      => '92600',
		'addressLocality' => 'Asnières-sur-Seine',
		'addressCountry'  => 'FR',
	);
}

/**
 * Entité Schema.org adaptée à la page courante : School pour l'École ou
 * le Collège, EducationalOrganization pour l'ensemble Cours Chambertin
 * ailleurs. Les logos sont optionnels (theme mods réglés une fois les
 * images importées dans la médiathèque) : omis si non réglés plutôt que
 * de pointer vers un fichier inexistant.
 */
function cc_json_ld_entity( $section, $title, $description ) {
	$org_id     = home_url( '/' ) . '#organization';
	$ecole_id   = home_url( '/ecole/' ) . '#school';
	$college_id = home_url( '/college/' ) . '#school';

	if ( 'ecole' === $section ) {
		$entity = array(
			'@type'       => 'School',
			'@id'         => $ecole_id,
			'name'        => 'École Chambertin',
			'address'     => cc_institution_address(),
			'parentOrganization' => array( '@id' => $org_id ),
			'logo'        => get_theme_mod( 'cc_logo_ecole_url', '' ),
		);
	} elseif ( 'college' === $section ) {
		$entity = array(
			'@type'       => 'School',
			'@id'         => $college_id,
			'name'        => 'Collège Chambertin',
			'telephone'   => '+33-1-47-93-97-92',
			'foundingDate' => '1982',
			'address'     => cc_institution_address(),
			'parentOrganization' => array( '@id' => $org_id ),
			'logo'        => get_theme_mod( 'cc_logo_college_url', '' ),
		);
	} else {
		$entity = array(
			'@type'          => 'EducationalOrganization',
			'@id'            => $org_id,
			'name'           => 'Cours Chambertin',
			'telephone'      => '+33-1-47-93-97-92',
			'foundingDate'   => '1982',
			'address'        => cc_institution_address(),
			'subOrganization' => array( array( '@id' => $ecole_id ), array( '@id' => $college_id ) ),
			'logo'           => get_theme_mod( 'cc_logo_master_url', '' ),
		);
	}

	$entity = array_filter( $entity, static fn( $value ) => '' !== $value );

	return array_merge(
		array( '@context' => 'https://schema.org' ),
		$entity,
		array(
			'url'         => get_permalink() ?: home_url( '/' ),
			'name'        => $title,
			'description' => $description,
		)
	);
}

/**
 * Format de titre identique à build.py : "{titre de la page} | Cours
 * Chambertin" partout, sauf sur l'accueil.
 */
function cc_document_title_parts( $parts ) {
	if ( is_front_page() ) {
		$parts = array( 'title' => get_bloginfo( 'name' ) . ' | Enseignement privé École et Collège' );
	}
	return $parts;
}
add_filter( 'document_title_parts', 'cc_document_title_parts' );

/**
 * Bloc canonical / Open Graph / Twitter Card / JSON-LD, injecté dans <head>.
 */
function cc_seo_head() {
	if ( ! ( is_page() || is_singular( 'post' ) || is_front_page() ) ) {
		return;
	}

	$canonical   = get_permalink() ?: home_url( '/' );
	$title       = wp_get_document_title();
	$description = has_excerpt() ? get_the_excerpt() : wp_trim_words( wp_strip_all_tags( get_the_content( '', false ) ), 30 );
	$section     = cc_current_section();
	$og_image    = get_the_post_thumbnail_url( null, 'large' );
	if ( ! $og_image ) {
		$mod_key  = $section ? "cc_og_image_{$section}_url" : 'cc_og_image_default_url';
		$og_image = get_theme_mod( $mod_key, '' );
	}

	echo "\n" . '<link rel="canonical" href="' . esc_url( $canonical ) . '">' . "\n";
	echo '<meta property="og:type" content="website">' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
	echo '<meta property="og:locale" content="fr_FR">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $canonical ) . '">' . "\n";
	if ( $og_image ) {
		echo '<meta property="og:image" content="' . esc_url( $og_image ) . '">' . "\n";
	}
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '">' . "\n";
	if ( $og_image ) {
		echo '<meta name="twitter:image" content="' . esc_url( $og_image ) . '">' . "\n";
	}

	$json_ld = cc_json_ld_entity( $section, $title, $description );
	echo '<script type="application/ld+json">' . wp_json_encode( $json_ld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'cc_seo_head', 5 );
