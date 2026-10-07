<?php
/**
 * Personnaliseur — bandeau d'annonce sitewide.
 *
 * Expose cc_announce_message et cc_announce_target_path (déjà lus par
 * cc_announcement_bar() dans template-tags.php) comme réglages éditables
 * dans Apparence > Personnaliser, sans passer par le code.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function cc_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'cc_announce',
		array(
			'title'       => __( 'Bandeau d’annonce', 'cours-chambertin' ),
			'description' => __( 'Message affiché en haut de toutes les pages. Le bandeau ne s’affiche que si la page cible existe ; laisser vide le chemin pour le masquer.', 'cours-chambertin' ),
			'priority'    => 30,
		)
	);

	$wp_customize->add_setting(
		'cc_announce_message',
		array(
			'default'           => '<strong>Ouverture de la préinscription</strong> — Collège Chambertin',
			'sanitize_callback' => 'wp_kses_post',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'cc_announce_message',
		array(
			'section'     => 'cc_announce',
			'label'       => __( 'Message', 'cours-chambertin' ),
			'description' => __( 'Les balises simples comme <strong> sont acceptées.', 'cours-chambertin' ),
			'type'        => 'text',
		)
	);

	$wp_customize->add_setting(
		'cc_announce_target_path',
		array(
			'default'           => 'college/admissions',
			'sanitize_callback' => 'sanitize_text_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'cc_announce_target_path',
		array(
			'section'     => 'cc_announce',
			'label'       => __( 'Page cible (chemin)', 'cours-chambertin' ),
			'description' => __( 'Chemin de la page vers laquelle pointe le bandeau, par exemple college/admissions ou ecole/admissions. Sans le domaine ni la barre oblique de début.', 'cours-chambertin' ),
			'type'        => 'text',
		)
	);
}
add_action( 'customize_register', 'cc_customize_register' );
