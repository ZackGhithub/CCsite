<?php
/**
 * Plugin Name: Cours Chambertin — Migration de contenu
 * Description: Importe en un clic les 24 pages, les 3 articles d'actualité, les images et les menus du site Cours Chambertin dans WordPress. À utiliser une fois, avec le thème cours-chambertin actif, puis à désactiver/supprimer. Alternative sans SSH au script wp-theme/migration/import.php du dépôt.
 * Version: 0.1.0
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * Text Domain: cc-migration
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CC_MIGRATION_DIR', plugin_dir_path( __FILE__ ) );

require CC_MIGRATION_DIR . 'includes/html-to-blocks.php';
require CC_MIGRATION_DIR . 'includes/migrate-functions.php';

add_action( 'admin_menu', function () {
	add_management_page(
		'Migration Cours Chambertin',
		'Migration Cours Chambertin',
		'manage_options',
		'cc-migration',
		'cc_migration_admin_page'
	);
} );

function cc_migration_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$log = null;
	if ( isset( $_POST['cc_migration_run'] ) && check_admin_referer( 'cc_migration_run' ) ) {
		$manifest     = require CC_MIGRATION_DIR . 'includes/pages.php';
		$force_update = ! empty( $_POST['cc_migration_force_update'] );
		$log          = cc_migrate_run( $manifest, CC_MIGRATION_DIR . 'data/content', CC_MIGRATION_DIR . 'data/images', $force_update );
	} elseif ( isset( $_POST['cc_migration_fix_links'] ) && check_admin_referer( 'cc_migration_fix_links' ) ) {
		$manifest = require CC_MIGRATION_DIR . 'includes/pages.php';
		cc_migrate_fix_existing_links( $manifest );
		$log = $GLOBALS['cc_migrate_log_lines'];
	}

	$theme_ok = 'cours-chambertin' === get_stylesheet();

	echo '<div class="wrap"><h1>Migration Cours Chambertin</h1>';

	if ( ! $theme_ok ) {
		echo '<div class="notice notice-warning"><p>Le thème actif n\'est pas « cours-chambertin ». '
			. 'Active-le d\'abord (Apparence > Thèmes) : les blocs <code>cc/timeline</code> et '
			. '<code>cc/tri-panel</code> utilisés par le contenu importé n\'existent que si ce thème est actif.</p></div>';
	}

	if ( null !== $log ) {
		$warnings = array_filter( $log, static fn( $l ) => 'warning' === $l['level'] );
		echo '<div class="notice ' . ( $warnings ? 'notice-warning' : 'notice-success' ) . '"><p>'
			. 'Migration exécutée. ' . count( $log ) . ' ligne(s) de journal'
			. ( $warnings ? ', dont ' . count( $warnings ) . ' avertissement(s)' : '' ) . '.</p></div>';
		echo '<details open style="background:#fff;border:1px solid #ccd0d4;padding:1em;margin-bottom:1.5em;"><summary><strong>Journal</strong></summary><ul style="margin-top:1em;">';
		foreach ( $log as $line ) {
			$style = 'warning' === $line['level'] ? 'color:#b32d2e;' : '';
			echo '<li style="' . $style . '">' . esc_html( $line['message'] ) . '</li>';
		}
		echo '</ul></details>';
	}

	echo '<p>Ce bouton importe (une seule fois, sans doublon si relancé) :</p><ul style="list-style:disc;margin-left:2em;">'
		. '<li>Les images du site dans la médiathèque</li>'
		. '<li>Les 24 pages, avec leur hiérarchie</li>'
		. '<li>Les 3 articles d\'actualité</li>'
		. '<li>Le menu principal et le menu de pied de page</li>'
		. '<li>La page d\'accueil statique</li>'
		. '</ul>';
	echo '<form method="post">';
	wp_nonce_field( 'cc_migration_run' );
	echo '<p><label><input type="checkbox" name="cc_migration_force_update" value="1"> '
		. '<strong>Écraser le contenu des pages/articles déjà présents</strong> avec la version actuelle du dépôt '
		. '(à cocher pour propager une correction de contenu ou de mise en page — écrase aussi toute modification '
		. 'faite depuis dans l\'éditeur de blocs sur ces pages précises)</label></p>';
	submit_button( 'Lancer la migration', 'primary', 'cc_migration_run' );
	echo '</form>';

	echo '<hr style="margin:2em 0;">';
	echo '<h2>Corriger les liens internes</h2>';
	echo '<p>Si le site tourne dans un sous-dossier (ex. préproduction sur <code>/clone0726/</code>), '
		. 'les liens internes des pages déjà migrées peuvent pointer hors de ce sous-dossier. '
		. 'Ce bouton les recalcule sans toucher au reste du contenu — sûr à relancer plusieurs fois.</p>';
	echo '<form method="post">';
	wp_nonce_field( 'cc_migration_fix_links' );
	submit_button( 'Corriger les liens internes', 'secondary', 'cc_migration_fix_links' );
	echo '</form>';

	echo '<p style="margin-top:2em;color:#646970;">Une fois la migration terminée et vérifiée, désactive puis supprime '
		. 'cette extension (Extensions) : elle n\'a plus d\'utilité ensuite.</p>';
	echo '</div>';
}
