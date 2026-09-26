<?php
/**
 * Enregistrement des blocs custom du thème (blocks/*). Écrits en JS natif
 * sans étape de build : les dépendances de script sont déclarées ici plutôt
 * que lues depuis un fichier *.asset.php généré par wp-scripts.
 *
 * Voir .claude/skills/gutenberg-block.md : un bloc custom n'est ajouté que
 * lorsqu'aucune combinaison de blocs natifs ne peut reproduire le composant.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function cc_register_blocks() {
	$blocks = array(
		'cc-timeline'       => array( 'cc/timeline' ),
		'cc-timeline-item'  => array( 'cc/timeline-item' ),
		'cc-tri-panel'      => array( 'cc/tri-panel' ),
		'cc-tri-panel-side' => array( 'cc/tri-panel-side' ),
	);

	foreach ( $blocks as $dir => $names ) {
		$handle = 'cc-block-' . $dir;
		wp_register_script(
			$handle,
			get_theme_file_uri( "blocks/{$dir}/index.js" ),
			array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-components', 'wp-i18n' ),
			CC_THEME_VERSION,
			true
		);
		register_block_type(
			get_theme_file_path( "blocks/{$dir}/block.json" ),
			array( 'editor_script' => $handle )
		);
	}
}
add_action( 'init', 'cc_register_blocks' );
