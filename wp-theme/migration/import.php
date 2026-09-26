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
 * Étapes : import des images -> création des pages (avec hiérarchie) ->
 * création des articles d'actualité -> recréation des menus -> réglage de
 * la page d'accueil statique.
 */

if ( ! defined( 'WP_CLI' ) ) {
	echo "Ce script s'exécute avec WP-CLI : wp eval-file wp-theme/migration/import.php <chemin-du-depot-CCsite>\n";
	exit( 1 );
}

$source = isset( $args[0] ) ? rtrim( $args[0], '/' ) : null;
if ( ! $source || ! is_dir( $source . '/content' ) || ! is_dir( $source . '/assets/images' ) ) {
	WP_CLI::error( "Chemin du dépôt CCsite invalide ou manquant (content/ ou assets/images/ introuvable) : " . var_export( $source, true ) );
}

require __DIR__ . '/html-to-blocks.php';
$manifest = require __DIR__ . '/pages.php';

$content_dir = $source . '/content';
$images_dir  = $source . '/assets/images';

/** Importe les JPEG/PNG de assets/images/ dans la médiathèque (idempotent). */
function cc_migrate_import_images( $images_dir ) {
	$map   = array();
	$files = array_merge( glob( $images_dir . '/*.jpg' ), glob( $images_dir . '/*.jpeg' ), glob( $images_dir . '/*.png' ) );
	sort( $files );

	foreach ( $files as $file ) {
		$filename = basename( $file );
		$title    = pathinfo( $filename, PATHINFO_FILENAME );

		$existing = new WP_Query(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'title'          => $title,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);
		if ( $existing->posts ) {
			$map[ $filename ] = wp_get_attachment_url( $existing->posts[0] );
			continue;
		}

		$upload = wp_upload_bits( $filename, null, file_get_contents( $file ) );
		if ( ! empty( $upload['error'] ) ) {
			WP_CLI::warning( "Image non importée ({$filename}) : {$upload['error']}" );
			continue;
		}

		$filetype   = wp_check_filetype( $filename, null );
		$attach_id  = wp_insert_attachment(
			array(
				'post_mime_type' => $filetype['type'],
				'post_title'     => $title,
				'post_content'   => '',
				'post_status'    => 'inherit',
			),
			$upload['file']
		);

		require_once ABSPATH . 'wp-admin/includes/image.php';
		wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $upload['file'] ) );

		$map[ $filename ] = $upload['url'];
		WP_CLI::log( "Image importée : {$filename}" );
	}

	return $map;
}

/** Remplace les chemins /assets/images/<fichier> par l'URL réelle du média importé. */
function cc_migrate_apply_image_map( $content, array $map ) {
	return preg_replace_callback(
		'#/assets/images/([A-Za-z0-9_.-]+\.(?:jpg|jpeg|png))#',
		function ( $m ) use ( $map ) {
			return isset( $map[ $m[1] ] ) ? $map[ $m[1] ] : $m[0];
		},
		$content
	);
}

/** Crée les pages (avec leur hiérarchie parent/enfant) — idempotent. */
function cc_migrate_create_pages( array $pages, $content_dir, array $image_map ) {
	$id_map = array();

	foreach ( $pages as $page ) {
		$lookup_path = '' === $page['path'] ? 'accueil' : $page['path'];
		$existing    = get_page_by_path( $lookup_path );
		if ( $existing ) {
			WP_CLI::log( "Page déjà présente, ignorée : {$page['title']}" );
			$id_map[ $page['id'] ] = $existing->ID;
			continue;
		}

		$html   = file_get_contents( $content_dir . '/' . $page['file'] );
		$blocks = cc_migrate_apply_image_map( cc_convert_content_to_blocks( $html ), $image_map );

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $page['title'],
				'post_name'    => '' === $page['path'] ? 'accueil' : basename( $page['path'] ),
				'post_excerpt' => $page['excerpt'],
				'post_content' => $blocks,
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			WP_CLI::warning( "Échec création page « {$page['title']} » : " . $post_id->get_error_message() );
			continue;
		}

		$id_map[ $page['id'] ] = $post_id;
		WP_CLI::log( "Page créée : {$page['title']} (#{$post_id})" );
	}

	// Deuxième passe : hiérarchie parent/enfant, une fois tous les ID connus.
	foreach ( $pages as $page ) {
		if ( ! $page['parent'] || ! isset( $id_map[ $page['id'] ], $id_map[ $page['parent'] ] ) ) {
			continue;
		}
		wp_update_post( array( 'ID' => $id_map[ $page['id'] ], 'post_parent' => $id_map[ $page['parent'] ] ) );
	}

	return $id_map;
}

/** Crée les articles d'actualité — idempotent. */
function cc_migrate_create_posts( array $posts, $content_dir, array $image_map ) {
	foreach ( $posts as $post_def ) {
		$existing = get_page_by_path( $post_def['slug'], OBJECT, 'post' );
		if ( $existing ) {
			WP_CLI::log( "Article déjà présent, ignoré : {$post_def['title']}" );
			continue;
		}

		$html   = file_get_contents( $content_dir . '/' . $post_def['file'] );
		$blocks = cc_migrate_apply_image_map( cc_convert_content_to_blocks( $html ), $image_map );

		$post_id = wp_insert_post(
			array(
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_title'   => $post_def['title'],
				'post_name'    => $post_def['slug'],
				'post_excerpt' => $post_def['excerpt'],
				'post_content' => $blocks,
				'post_date'    => $post_def['date_iso'],
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			WP_CLI::warning( "Échec création article « {$post_def['title']} » : " . $post_id->get_error_message() );
			continue;
		}

		WP_CLI::log( "Article créé : {$post_def['title']} (#{$post_id})" );
	}
}

/** Recrée un menu à partir d'entrées {id,label[,children]} du manifest — idempotent. */
function cc_migrate_create_menu( $menu_name, $location, array $entries, array $id_map ) {
	$menu = wp_get_nav_menu_object( $menu_name );
	if ( ! $menu ) {
		$menu_id = wp_create_nav_menu( $menu_name );
	} else {
		$menu_id = $menu->term_id;
		if ( wp_get_nav_menu_items( $menu_id ) ) {
			WP_CLI::log( "Menu « {$menu_name} » déjà rempli, non modifié." );
			cc_migrate_assign_menu_location( $menu_id, $location );
			return;
		}
	}

	foreach ( $entries as $entry ) {
		if ( ! isset( $id_map[ $entry['id'] ] ) ) {
			continue;
		}
		$parent_menu_item_id = wp_update_nav_menu_item(
			$menu_id,
			0,
			array(
				'menu-item-title'     => $entry['label'],
				'menu-item-object-id' => $id_map[ $entry['id'] ],
				'menu-item-object'    => 'page',
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			)
		);
		foreach ( $entry['children'] ?? array() as $child_id ) {
			if ( ! isset( $id_map[ $child_id ] ) ) {
				continue;
			}
			wp_update_nav_menu_item(
				$menu_id,
				0,
				array(
					'menu-item-title'     => get_the_title( $id_map[ $child_id ] ),
					'menu-item-object-id' => $id_map[ $child_id ],
					'menu-item-object'    => 'page',
					'menu-item-type'      => 'post_type',
					'menu-item-status'    => 'publish',
					'menu-item-parent-id' => $parent_menu_item_id,
				)
			);
		}
	}

	cc_migrate_assign_menu_location( $menu_id, $location );
	WP_CLI::log( "Menu créé et assigné : {$menu_name} -> {$location}" );
}

function cc_migrate_assign_menu_location( $menu_id, $location ) {
	$locations             = get_theme_mod( 'nav_menu_locations', array() );
	$locations[ $location ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

// --------------------------------------------------------------------
// Exécution
// --------------------------------------------------------------------

WP_CLI::log( 'Import des images...' );
$image_map = cc_migrate_import_images( $images_dir );

WP_CLI::log( 'Création des pages...' );
$id_map = cc_migrate_create_pages( $manifest['pages'], $content_dir, $image_map );

WP_CLI::log( "Création des articles d'actualité..." );
cc_migrate_create_posts( $manifest['posts'], $content_dir, $image_map );

WP_CLI::log( 'Recréation des menus...' );
cc_migrate_create_menu( 'Menu principal', 'primary', $manifest['nav_menu'], $id_map );
cc_migrate_create_menu( 'Plan du site (pied de page)', 'footer', array_map(
	static fn( $e ) => $e + array( 'children' => array() ),
	$manifest['footer_menu']
), $id_map );

if ( isset( $id_map['2'] ) ) {
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $id_map['2'] );
	WP_CLI::log( 'Page d’accueil statique réglée sur "Cours Chambertin".' );
}

WP_CLI::success( 'Migration terminée. Vérifier ensuite les pages contenant une frise ou des panneaux (cc/timeline, cc/tri-panel) et les composants restés en Bloc HTML (voir wp-theme/README.md).' );
