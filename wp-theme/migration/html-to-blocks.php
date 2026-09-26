<?php
/**
 * Convertisseur HTML -> markup de blocs Gutenberg pour les fragments de
 * content/*.html.
 *
 * Portée volontairement bornée (pas un convertisseur HTML générique) :
 * seuls les motifs réellement présents dans content/*.html sont reconnus.
 * - section "cc-section" et conteneur "cc-narrow"  -> bloc Groupe natif
 * - h1/h2/h3/h4                                     -> bloc Titre natif
 * - p                                                -> bloc Paragraphe natif
 * - <ol class="cc-timeline">                         -> bloc cc/timeline
 * - <div class="cc-tri-panel...">                    -> bloc cc/tri-panel
 * - tout le reste (bento, dual-panel, media-duo, plan, faq, formulaires,
 *   <style> scopés, images) -> bloc HTML personnalisé (passthrough fidèle)
 *
 * Ce périmètre correspond exactement aux deux blocs custom construits en
 * Phase 1 : les composants qui n'ont ni équivalent natif ni bloc dédié
 * restent en Bloc HTML plutôt que d'être décomposés au petit bonheur.
 * Un humain peut toujours reconvertir un Bloc HTML en blocs natifs a
 * posteriori dans l'éditeur si besoin.
 *
 * Peut être chargé aussi bien par WordPress (wp eval-file) qu'en PHP nu
 * (aucune fonction WordPress utilisée ici), pour rester testable en dehors
 * d'une installation WordPress.
 */

/**
 * Charge un fragment HTML dans un DOMDocument sans l'envelopper dans
 * <html><body>, avec un traitement correct de l'UTF-8.
 */
function cc_dom_load( $html ) {
	$dom = new DOMDocument();
	libxml_use_internal_errors( true );
	$dom->loadHTML(
		'<?xml encoding="utf-8" ?>' . $html,
		LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED
	);
	libxml_clear_errors();
	return $dom;
}

/** HTML du nœud lui-même (balise incluse). */
function cc_outer_html( DOMNode $node ) {
	return trim( $node->ownerDocument->saveHTML( $node ) );
}

/** HTML des enfants d'un nœud (balise du nœud exclue). */
function cc_inner_html( DOMNode $node ) {
	$html = '';
	foreach ( $node->childNodes as $child ) {
		$html .= $node->ownerDocument->saveHTML( $child );
	}
	return trim( $html );
}

/** Classes CSS d'un élément, sous forme de tableau. */
function cc_classes( DOMElement $el ) {
	return array_values( array_filter( preg_split( '/\s+/', trim( $el->getAttribute( 'class' ) ) ) ) );
}

function cc_has_class( DOMElement $el, $class ) {
	return in_array( $class, cc_classes( $el ), true );
}

/** Attributs JSON d'un commentaire de bloc (omis si vide). */
function cc_block_attrs_json( array $attrs ) {
	if ( ! $attrs ) {
		return '';
	}
	return ' ' . json_encode( $attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
}

function cc_block_open( $name, array $attrs = array() ) {
	return '<!-- wp:' . $name . cc_block_attrs_json( $attrs ) . ' -->';
}

function cc_block_close( $name ) {
	return '<!-- /wp:' . $name . ' -->';
}

/** Bloc HTML personnalisé (passthrough fidèle) autour d'un nœud entier. */
function cc_block_html_passthrough( DOMNode $node ) {
	$html = cc_outer_html( $node );
	if ( '' === trim( $html ) ) {
		return '';
	}
	return cc_block_open( 'html' ) . "\n" . $html . "\n" . cc_block_close( 'html' );
}

/** Bloc Titre natif (core/heading). */
function cc_block_heading( DOMElement $el ) {
	$level   = (int) substr( $el->tagName, 1 ); // "h1" -> 1
	$classes = cc_classes( $el );
	$attrs   = array();
	if ( 2 !== $level ) {
		$attrs['level'] = $level;
	}
	if ( $classes ) {
		$attrs['className'] = implode( ' ', $classes );
	}
	$outClasses = array_merge( $classes, array( 'wp-block-heading' ) );
	$tag        = 'h' . $level;
	return cc_block_open( 'heading', $attrs ) . "\n"
		. '<' . $tag . ' class="' . implode( ' ', $outClasses ) . '">' . cc_inner_html( $el ) . '</' . $tag . '>' . "\n"
		. cc_block_close( 'heading' );
}

/** Bloc Paragraphe natif (core/paragraph). */
function cc_block_paragraph( DOMElement $el ) {
	$classes = cc_classes( $el );
	$attrs   = $classes ? array( 'className' => implode( ' ', $classes ) ) : array();
	$classAttr = $classes ? ' class="' . implode( ' ', $classes ) . '"' : '';
	$style   = $el->getAttribute( 'style' );
	$styleAttr = $style ? ' style="' . htmlspecialchars( $style, ENT_QUOTES ) . '"' : '';
	return cc_block_open( 'paragraph', $attrs ) . "\n"
		. '<p' . $classAttr . $styleAttr . '>' . cc_inner_html( $el ) . '</p>' . "\n"
		. cc_block_close( 'paragraph' );
}

/**
 * <ol class="cc-timeline"> -> bloc cc/timeline avec un cc/timeline-item par
 * <li>. Le corps de chaque étape (tous les <p> après le <h3>) devient des
 * blocs Paragraphe imbriqués, comme dans le bloc édité à la main.
 */
function cc_block_timeline( DOMElement $ol ) {
	$items = '';
	foreach ( $ol->childNodes as $li ) {
		if ( ! $li instanceof DOMElement || 'li' !== $li->tagName ) {
			continue;
		}
		$future = cc_has_class( $li, 'cc-avenir' );
		$date   = '';
		$title  = '';
		$paragraphs = '';
		foreach ( $li->childNodes as $child ) {
			if ( ! $child instanceof DOMElement ) {
				continue;
			}
			if ( cc_has_class( $child, 'cc-timeline-date' ) ) {
				$date = cc_inner_html( $child );
			} elseif ( cc_has_class( $child, 'cc-timeline-body' ) ) {
				foreach ( $child->childNodes as $bodyChild ) {
					if ( ! $bodyChild instanceof DOMElement ) {
						continue;
					}
					if ( 'h3' === $bodyChild->tagName ) {
						$title = cc_inner_html( $bodyChild );
					} elseif ( 'p' === $bodyChild->tagName ) {
						$paragraphs .= cc_block_paragraph( $bodyChild ) . "\n";
					}
				}
			}
		}
		$itemAttrs = array( 'date' => $date, 'title' => $title );
		if ( $future ) {
			$itemAttrs['future'] = true;
		}
		$items .= cc_block_open( 'cc/timeline-item', $itemAttrs ) . "\n"
			. '<li' . ( $future ? ' class="cc-avenir"' : '' ) . '>'
			. '<div class="cc-timeline-date">' . $date . '</div>'
			. '<div class="cc-timeline-body"><h3>' . $title . '</h3>'
			. $paragraphs
			. '</div></li>' . "\n"
			. cc_block_close( 'cc/timeline-item' ) . "\n";
	}
	return cc_block_open( 'cc/timeline' ) . "\n"
		. '<ol class="wp-block-cc-timeline cc-timeline">' . "\n" . $items . '</ol>' . "\n"
		. cc_block_close( 'cc/timeline' );
}

/**
 * <div class="cc-tri-panel..."> -> bloc cc/tri-panel avec un
 * cc/tri-panel-side par panneau enfant.
 */
function cc_block_tri_panel( DOMElement $panel ) {
	$columns = cc_has_class( $panel, 'cc-tri-panel--quatre' ) ? 4 : 3;
	$sides   = '';
	foreach ( $panel->childNodes as $side ) {
		if ( ! $side instanceof DOMElement || ! cc_has_class( $side, 'cc-tri-panel-side' ) ) {
			continue;
		}
		$accent = cc_has_class( $side, 'cc-tri-panel-side--ecole' ) ? 'ecole'
			: ( cc_has_class( $side, 'cc-tri-panel-side--college' ) ? 'college' : '' );
		$title  = '';
		$body   = '';
		foreach ( $side->childNodes as $child ) {
			if ( ! $child instanceof DOMElement ) {
				continue;
			}
			if ( 'h3' === $child->tagName ) {
				$title = cc_inner_html( $child );
			} elseif ( 'p' === $child->tagName ) {
				$body .= cc_block_paragraph( $child ) . "\n";
			}
		}
		$sideClass = 'cc-tri-panel-side' . ( $accent ? ' cc-tri-panel-side--' . $accent : '' );
		$sideAttrs = array( 'title' => $title );
		if ( $accent ) {
			$sideAttrs['accent'] = $accent;
		}
		$sides .= cc_block_open( 'cc/tri-panel-side', $sideAttrs ) . "\n"
			. '<div class="wp-block-cc-tri-panel-side ' . $sideClass . '"><h3>' . $title . '</h3>' . $body . '</div>' . "\n"
			. cc_block_close( 'cc/tri-panel-side' ) . "\n";
	}
	$panelClass = 'cc-tri-panel' . ( 4 === $columns ? ' cc-tri-panel--quatre' : '' );
	return cc_block_open( 'cc/tri-panel', array( 'columns' => $columns ) ) . "\n"
		. '<div class="wp-block-cc-tri-panel ' . $panelClass . '">' . "\n" . $sides . '</div>' . "\n"
		. cc_block_close( 'cc/tri-panel' );
}

/** Dispatch d'un enfant de section (ou de .cc-narrow) vers le bon bloc. */
function cc_convert_child( DOMNode $node ) {
	if ( ! $node instanceof DOMElement ) {
		// Nœud texte (espaces entre balises) : ignoré, il n'a pas de valeur
		// en tant que bloc et serait invalide hors d'un bloc.
		return '';
	}
	$tag = $node->tagName;

	if ( in_array( $tag, array( 'h1', 'h2', 'h3', 'h4' ), true ) ) {
		return cc_block_heading( $node );
	}
	if ( 'p' === $tag ) {
		return cc_block_paragraph( $node );
	}
	if ( 'ol' === $tag && cc_has_class( $node, 'cc-timeline' ) ) {
		return cc_block_timeline( $node );
	}
	if ( 'div' === $tag && cc_has_class( $node, 'cc-tri-panel' ) ) {
		return cc_block_tri_panel( $node );
	}
	if ( 'div' === $tag && cc_has_class( $node, 'wp-block-group' ) && cc_has_class( $node, 'cc-narrow' ) ) {
		return cc_block_group_narrow( $node );
	}

	// Composant sans bloc dédié (bento, dual-panel, media-duo, plan, faq,
	// formulaire, <style> scopé...) : passthrough fidèle en Bloc HTML.
	return cc_block_html_passthrough( $node );
}

/** <div class="wp-block-group cc-narrow"> -> bloc Groupe natif. */
function cc_block_group_narrow( DOMElement $el ) {
	$classes = array_values( array_diff( cc_classes( $el ), array( 'wp-block-group' ) ) );
	$inner   = '';
	foreach ( $el->childNodes as $child ) {
		$converted = cc_convert_child( $child );
		if ( '' !== $converted ) {
			$inner .= $converted . "\n";
		}
	}
	$attrs = $classes ? array( 'className' => implode( ' ', $classes ) ) : array();
	return cc_block_open( 'group', $attrs ) . "\n"
		. '<div class="wp-block-group' . ( $classes ? ' ' . implode( ' ', $classes ) : '' ) . '">' . "\n"
		. $inner . '</div>' . "\n"
		. cc_block_close( 'group' );
}

/** <div class="wp-block-group cc-section cc-section--X"> -> bloc Groupe natif. */
function cc_block_section( DOMElement $el ) {
	$classes = array_values( array_diff( cc_classes( $el ), array( 'wp-block-group' ) ) );
	$inner   = '';
	foreach ( $el->childNodes as $child ) {
		$converted = cc_convert_child( $child );
		if ( '' !== $converted ) {
			$inner .= $converted . "\n";
		}
	}
	$attrs = $classes ? array( 'className' => implode( ' ', $classes ) ) : array();
	return cc_block_open( 'group', $attrs ) . "\n"
		. '<div class="wp-block-group' . ( $classes ? ' ' . implode( ' ', $classes ) : '' ) . '">' . "\n"
		. $inner . '</div>' . "\n"
		. cc_block_close( 'group' );
}

/**
 * Point d'entrée : convertit un fragment content/*.html complet en markup
 * de blocs Gutenberg.
 *
 * Deux formes de fragments existent dans content/*.html :
 * - les pages : une suite de sections top-level "cc-section" -> bloc Groupe ;
 * - les articles d'actualité (news_*.html) : de simples <p> au niveau
 *   racine, sans wrapper "cc-section" (le gabarit single.php fournit déjà
 *   ce wrapper autour de the_content()) -> délégué à cc_convert_child()
 *   comme n'importe quel enfant de section, pour obtenir de vrais blocs
 *   Paragraphe plutôt qu'un Bloc HTML par paragraphe.
 */
function cc_convert_content_to_blocks( $html ) {
	$dom    = cc_dom_load( $html );
	$blocks = array();
	foreach ( $dom->childNodes as $node ) {
		if ( ! $node instanceof DOMElement ) {
			continue;
		}
		if ( 'div' === $node->tagName
			&& cc_has_class( $node, 'wp-block-group' ) && cc_has_class( $node, 'cc-section' ) ) {
			$blocks[] = cc_block_section( $node );
		} else {
			$blocks[] = cc_convert_child( $node );
		}
	}
	return implode( "\n\n", array_filter( $blocks ) );
}
