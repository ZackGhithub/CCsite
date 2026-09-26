# Thème WordPress — Cours Chambertin

Squelette du thème sur-mesure (sans Divi), à installer dans
`wp-content/themes/cours-chambertin/` d'une installation WordPress.

## Ce qui est fait

- `style.css` : en-tête de thème + feuille de style complète, reprise à
  l'identique de `assets/css/style.css` de la maquette statique.
- `theme.json` : palette de couleurs et polices (Spectral / Montserrat)
  exposées dans l'éditeur de blocs, tailles de contenu alignées sur
  `--cc-narrow` (760px) et `--cc-max` (1180px).
- `functions.php` : support du thème (logo custom, menus, embeds
  responsives...), enqueue des styles/scripts, catégorie de blocs
  « Cours Chambertin », deux zones de widgets pour les blocs d'adresse
  du pied de page.
- `header.php` / `footer.php` : gabarit commun (bandeau d'annonce,
  en-tête, navigation principale, pied de page).
- `page.php` : gabarit des pages statiques — attend un `post_content`
  qui commence par son propre `<h1 class="cc-page-title">` (voir
  CLAUDE.md du dépôt : un seul `<h1>` par page, porté par le contenu et
  non par le gabarit).
- `single.php` : gabarit des articles d'actualité. Les permaliens
  WordPress par défaut (`/%year%/%monthnum%/%day%/%postname%/`)
  correspondent déjà au format observé dans le contenu d'origine.
- `inc/seo.php` : canonical, Open Graph, Twitter Card, JSON-LD
  (School / EducationalOrganization), format de `<title>`.
- `inc/template-tags.php` : fil d'Ariane, bandeau d'annonce, logo,
  classes de menu.

## Ce qu'il reste à faire une fois WordPress installé

1. **Activer le thème**, régler Réglages > Lecture (page d'accueil
   statique = « Accueil »), créer les pages listées dans `PAGES` de
   `build.py` avec la même hiérarchie parent/enfant.
2. **Coller le contenu** : chaque `content/<id>_<slug>.html` correspond
   au `post_content` d'une page — copier tel quel en mode « Éditeur de
   code » du bloc éditeur, puis vérifier qu'aucun bloc n'apparaît en
   « non reconnu ».
3. **Recréer les menus** (Apparence > Menus) : structure dans `NAV_MENU`
   et `FOOTER_NAV` de `build.py`, à assigner aux emplacements
   « Menu principal » et « Plan du site (pied de page) ».
4. **Importer les images** dans la médiathèque (photos + logos de
   `assets/images/`) ; renseigner les logos École/Collège/master en
   theme mods (`cc_logo_ecole_url`, `cc_logo_college_url`,
   `cc_logo_master_url`) tant qu'aucun champ dédié n'existe.
5. **Créer les 3 articles d'actualité** comme Articles (pas des Pages),
   avec la date de publication correspondant à leur ancien permalien.
6. **Blocs custom manquants** : `cc-timeline` (frise) et `cc-tri-panel`
   n'ont pas d'équivalent Gutenberg natif — à construire avec le skill
   `.claude/skills/gutenberg-block.md` avant de migrer les pages qui les
   utilisent (École, admissions, notre-histoire).
7. **Bandeau d'annonce** : réglable via `cc_announce_message` et
   `cc_announce_target_path` (theme mods) — prévoir un panneau
   Personnaliser dédié si l'équipe éditoriale doit le modifier sans
   toucher au code.

## Ne pas réintroduire

Pas de Divi, Elementor ni aucun page builder — voir `CLAUDE.md` à la
racine du dépôt pour l'ensemble des règles de conversion.
