<?php
/**
 * Fonctions de migration partagées entre le script WP-CLI
 * (migration/import.php) et le plugin d'admin autonome
 * (wp-plugin/cc-migration/), pour qu'il n'existe qu'une seule
 * implémentation à maintenir.
 *
 * Ne dépend pas de WP_CLI : utilise cc_migrate_log()/cc_migrate_warning()
 * ci-dessous, qui s'adaptent au contexte d'exécution (ligne de commande ou
 * page d'admin WordPress).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lignes de log accumulées quand on tourne hors WP-CLI (le plugin d'admin
 * les affiche après coup ; WP-CLI les affiche au fil de l'eau).
 */
$GLOBALS['cc_migrate_log_lines'] = array();

function cc_migrate_log( $message ) {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::log( $message );
		return;
	}
	$GLOBALS['cc_migrate_log_lines'][] = array( 'level' => 'info', 'message' => $message );
}

function cc_migrate_warning( $message ) {
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		WP_CLI::warning( $message );
		return;
	}
	$GLOBALS['cc_migrate_log_lines'][] = array( 'level' => 'warning', 'message' => $message );
}

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
			cc_migrate_warning( "Image non importée ({$filename}) : {$upload['error']}" );
			continue;
		}

		$filetype  = wp_check_filetype( $filename, null );
		$attach_id = wp_insert_attachment(
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
		cc_migrate_log( "Image importée : {$filename}" );
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

/**
 * Les liens internes convertis par html-to-blocks.php sont racine-relatifs
 * (href="/ecole/admissions/"), ce qui suppose que WordPress est installé à
 * la racine du domaine. Si WordPress vit dans un sous-dossier (ex.
 * /clone0726/ en préproduction), ces liens pointent hors du site : il faut
 * les faire passer par home_url() pour qu'ils incluent le bon préfixe.
 */
function cc_migrate_localize_links( $content ) {
	// Délimiteur "~" plutôt que "#" : le motif exclut lui-même le caractère
	// "#" (fragments d'ancre) dans une classe de caractères, ce qui casse
	// la compilation PCRE si le délimiteur et ce caractère sont les mêmes
	// (preg_replace_callback renvoie alors NULL silencieusement pour toute
	// la chaîne — vu en test : un article migré avec un post_content vide).
	return preg_replace_callback(
		'~href="(/[^"#][^"]*)"~',
		function ( $m ) {
			return 'href="' . esc_url( home_url( $m[1] ) ) . '"';
		},
		$content
	);
}

/**
 * Crée les pages (avec leur hiérarchie parent/enfant) — idempotent par
 * défaut. Avec $force_update=true, une page déjà présente voit son
 * contenu/titre/extrait ré-écrasés depuis content/*.html au lieu d'être
 * ignorée — à utiliser pour propager une modification de contenu vers des
 * pages déjà migrées (écrase aussi toute modification faite depuis
 * l'éditeur de blocs sur ces pages : à réserver à la phase de mise au
 * point, pas à un usage régulier une fois le site en édition courante).
 */
function cc_migrate_create_pages( array $pages, $content_dir, array $image_map, $force_update = false ) {
	$id_map = array();

	foreach ( $pages as $page ) {
		$lookup_path = '' === $page['path'] ? 'accueil' : $page['path'];
		$existing    = get_page_by_path( $lookup_path );
		if ( $existing && ! $force_update ) {
			cc_migrate_log( "Page déjà présente, ignorée : {$page['title']}" );
			$id_map[ $page['id'] ] = $existing->ID;
			continue;
		}

		$html   = file_get_contents( $content_dir . '/' . $page['file'] );
		$blocks = cc_migrate_localize_links( cc_migrate_apply_image_map( cc_convert_content_to_blocks( $html ), $image_map ) );

		// Le parent (s'il y en a un) a nécessairement déjà été créé : dans
		// pages.php, chaque page apparaît après son parent. Régler
		// post_parent dès la création (plutôt qu'en passe séparée une fois
		// toutes les pages créées) évite qu'une page comme "admissions"
		// (id 160, racine) ne se fasse abusivement passer pour déjà
		// existante par get_page_by_path() à cause de "ecole/admissions"
		// ou "college/admissions" : celles-ci porteraient sinon, le temps
		// d'une passe, le même post_name "admissions" sans être encore
		// rattachées à leur parent, et sembleraient être cette page racine.
		$args = array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_title'   => $page['title'],
			'post_name'    => '' === $page['path'] ? 'accueil' : basename( $page['path'] ),
			'post_excerpt' => $page['excerpt'],
			'post_content' => $blocks,
			'post_parent'  => $page['parent'] ? ( $id_map[ $page['parent'] ] ?? 0 ) : 0,
		);
		if ( $existing ) {
			$args['ID'] = $existing->ID;
		}
		$post_id = wp_insert_post( $args, true );

		if ( is_wp_error( $post_id ) ) {
			cc_migrate_warning( "Échec " . ( $existing ? 'mise à jour' : 'création' ) . " page « {$page['title']} » : " . $post_id->get_error_message() );
			continue;
		}

		$id_map[ $page['id'] ] = $post_id;
		cc_migrate_log( ( $existing ? 'Page mise à jour : ' : 'Page créée : ' ) . "{$page['title']} (#{$post_id})" );
	}

	return $id_map;
}

/** Crée les articles d'actualité — idempotent par défaut (voir $force_update sur cc_migrate_create_pages()). */
function cc_migrate_create_posts( array $posts, $content_dir, array $image_map, $force_update = false ) {
	foreach ( $posts as $post_def ) {
		$existing = get_page_by_path( $post_def['slug'], OBJECT, 'post' );
		if ( $existing && ! $force_update ) {
			cc_migrate_log( "Article déjà présent, ignoré : {$post_def['title']}" );
			continue;
		}

		$html   = file_get_contents( $content_dir . '/' . $post_def['file'] );
		$blocks = cc_migrate_localize_links( cc_migrate_apply_image_map( cc_convert_content_to_blocks( $html ), $image_map ) );

		$args = array(
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_title'   => $post_def['title'],
			'post_name'    => $post_def['slug'],
			'post_excerpt' => $post_def['excerpt'],
			'post_content' => $blocks,
			'post_date'    => $post_def['date_iso'],
		);
		if ( $existing ) {
			$args['ID'] = $existing->ID;
		}
		$post_id = wp_insert_post( $args, true );

		if ( is_wp_error( $post_id ) ) {
			cc_migrate_warning( "Échec " . ( $existing ? 'mise à jour' : 'création' ) . " article « {$post_def['title']} » : " . $post_id->get_error_message() );
			continue;
		}

		cc_migrate_log( ( $existing ? 'Article mis à jour : ' : 'Article créé : ' ) . "{$post_def['title']} (#{$post_id})" );
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
			cc_migrate_log( "Menu « {$menu_name} » déjà rempli, non modifié." );
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
	cc_migrate_log( "Menu créé et assigné : {$menu_name} -> {$location}" );
}

function cc_migrate_assign_menu_location( $menu_id, $location ) {
	$locations              = get_theme_mod( 'nav_menu_locations', array() );
	$locations[ $location ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Corrige les liens internes des pages/articles déjà migrés (utile après
 * une migration lancée avant l'ajout de cc_migrate_localize_links(), ou si
 * WordPress a changé de sous-dossier depuis). Sans effet sur les pages déjà
 * correctes (le remplacement ne cible que les href="/..." encore racine-
 * relatifs). Ne touche pas au reste du contenu, donc sûr à relancer même
 * après des modifications manuelles.
 */
function cc_migrate_fix_existing_links( array $manifest ) {
	$fixed = 0;

	foreach ( $manifest['pages'] as $page ) {
		$lookup = '' === $page['path'] ? 'accueil' : $page['path'];
		$post   = get_page_by_path( $lookup );
		if ( ! $post ) {
			continue;
		}
		$new_content = cc_migrate_localize_links( $post->post_content );
		if ( $new_content !== $post->post_content ) {
			wp_update_post( array( 'ID' => $post->ID, 'post_content' => $new_content ) );
			cc_migrate_log( "Liens corrigés : {$page['title']}" );
			++$fixed;
		}
	}

	foreach ( $manifest['posts'] as $post_def ) {
		$post = get_page_by_path( $post_def['slug'], OBJECT, 'post' );
		if ( ! $post ) {
			continue;
		}
		$new_content = cc_migrate_localize_links( $post->post_content );
		if ( $new_content !== $post->post_content ) {
			wp_update_post( array( 'ID' => $post->ID, 'post_content' => $new_content ) );
			cc_migrate_log( "Liens corrigés : {$post_def['title']}" );
			++$fixed;
		}
	}

	cc_migrate_log( $fixed > 0 ? "{$fixed} page(s)/article(s) corrigé(s)." : 'Aucun lien à corriger — déjà bon.' );
	return $fixed;
}

/**
 * Point d'entrée unique, appelé par le script WP-CLI et par le plugin
 * d'admin. $content_dir et $images_dir pointent respectivement vers un
 * dossier content/ et un dossier assets/images/ (peu importe où ils vivent
 * réellement sur le disque).
 */
function cc_migrate_run( array $manifest, $content_dir, $images_dir, $force_update = false ) {
	cc_migrate_log( 'Import des images...' );
	$image_map = cc_migrate_import_images( $images_dir );

	cc_migrate_log( $force_update ? 'Création/mise à jour des pages...' : 'Création des pages...' );
	$id_map = cc_migrate_create_pages( $manifest['pages'], $content_dir, $image_map, $force_update );

	cc_migrate_log( $force_update ? "Création/mise à jour des articles d'actualité..." : "Création des articles d'actualité..." );
	cc_migrate_create_posts( $manifest['posts'], $content_dir, $image_map, $force_update );

	cc_migrate_log( 'Recréation des menus...' );
	cc_migrate_create_menu( 'Menu principal', 'primary', $manifest['nav_menu'], $id_map );
	cc_migrate_create_menu(
		'Plan du site (pied de page)',
		'footer',
		array_map( static fn( $e ) => $e + array( 'children' => array() ), $manifest['footer_menu'] ),
		$id_map
	);

	if ( isset( $id_map['2'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $id_map['2'] );
		cc_migrate_log( 'Page d’accueil statique réglée sur "Cours Chambertin".' );
	}

	return $GLOBALS['cc_migrate_log_lines'];
}
