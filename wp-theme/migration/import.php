<?php
/**
 * Script de migration — à exécuter une fois WordPress installé, avec le
 * thème cours-chambertin actif :
 *
 *   wp eval-file wp-theme/migration/import.php /chemin/vers/le/depot/CCsite
 *
 * L'argument est le chemin du dépôt CCsite (celui qui contient content/ et
 * assets/images/), pas celui du thème. Idempotent : peut être relancé sans
 * dupliquer les pages, articles ou images déjà importés.
 *
 * Pas d'accès SSH/WP-CLI ? Le plugin wp-plugin/cc-migration/ fait
 * exactement la même chose depuis l'admin WordPress (voir son README).
 *
 * La logique elle-même vit dans migrate-functions.php, partagée avec ce
 * plugin — ce fichier n'est qu'un point d'entrée en ligne de commande.
 */

if ( ! defined( 'WP_CLI' ) ) {
	echo "Ce script s'exécute avec WP-CLI : wp eval-file wp-theme/migration/import.php <chemin-du-depot-CCsite>\n";
	exit( 1 );
}

$source = isset( $args[0] ) ? rtrim( $args[0], '/' ) : null;
if ( ! $source || ! is_dir( $source . '/content' ) || ! is_dir( $source . '/assets/images' ) ) {
	WP_CLI::error( 'Chemin du dépôt CCsite invalide ou manquant (content/ ou assets/images/ introuvable) : ' . var_export( $source, true ) );
}

require __DIR__ . '/html-to-blocks.php';
require __DIR__ . '/migrate-functions.php';
$manifest = require __DIR__ . '/pages.php';

cc_migrate_run( $manifest, $source . '/content', $source . '/assets/images' );

WP_CLI::success( 'Migration terminée. Vérifier ensuite les pages contenant une frise ou des panneaux (cc/timeline, cc/tri-panel) et les composants restés en Bloc HTML (voir wp-theme/README.md).' );
