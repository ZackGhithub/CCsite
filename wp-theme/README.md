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
- `blocks/` : deux blocs custom sans équivalent Gutenberg natif, écrits en
  JS natif (sans étape de build), enregistrés par `inc/blocks.php` :
  - **`cc/timeline`** + **`cc/timeline-item`** — frise chronologique
    (Notre histoire, calendrier de l'École). Chaque étape a une date, un
    titre et un corps en blocs Paragraphe natifs, avec un réglage « étape
    à venir » pour le marqueur en pointillé (`cc-avenir`).
  - **`cc/tri-panel`** + **`cc/tri-panel-side`** — rangée de 3 ou 4
    panneaux à bordure de couleur (Contact, Collège, École). Chaque
    panneau a un titre, des paragraphes natifs, et un réglage d'accent
    (aucun / École / Collège). Un bouton dans un panneau n'est pas un
    bloc à part : un paragraphe natif portant la classe CSS additionnelle
    « cc-bento-action » avec un lien sur tout son texte suffit — la
    feuille de style du thème le rend comme un bouton.

## Migration du contenu (`migration/`)

Le contenu des 27 pages/articles n'est **pas** copié-collé à la main dans
l'admin WordPress : un script WP-CLI automatise la conversion et
l'import, pour éviter les erreurs de copier-coller et obtenir dès le
départ de vraies pages en blocs Gutenberg (pas du HTML brut).

- `migration/pages.php` — manifest des pages/articles/menus, porté
  directement du tableau `PAGES` (et `NAV_MENU`/`FOOTER_NAV`) de
  `build.py`.
- `migration/html-to-blocks.php` — convertit un fragment `content/*.html`
  en markup de blocs Gutenberg (`<!-- wp:... -->`). Portée volontaire et
  bornée : sections (`cc-section`/`cc-narrow`) → bloc Groupe natif,
  titres/paragraphes → blocs natifs, `cc-timeline`/`cc-tri-panel` → les
  blocs custom de `blocks/`. Tout composant sans équivalent (bento,
  panneaux doubles, comparatif d'images, plan du site, FAQ, `<style>`
  scopés...) devient un Bloc HTML fidèle plutôt que d'être décomposé au
  hasard — convertible en blocs natifs a posteriori dans l'éditeur si
  besoin. Testable indépendamment de WordPress (aucune fonction WP
  utilisée) : `php -r 'require "migration/html-to-blocks.php";
  echo cc_convert_content_to_blocks(file_get_contents("../content/2_accueil.html"));'`
- `migration/import.php` — orchestration : importe les images de
  `assets/images/` dans la médiathèque, crée les 24 pages avec leur
  hiérarchie parent/enfant, crée les 3 articles d'actualité (avec la date
  de publication qui reproduit leur ancien permalien), recrée les deux
  menus et les assigne aux emplacements du thème, règle la page d'accueil
  statique. Idempotent (rejouable sans dupliquer).

**Exécution**, une fois WordPress installé et le thème activé :

```bash
wp eval-file wp-theme/migration/import.php /chemin/vers/le/depot/CCsite
```

L'argument est le chemin du dépôt CCsite (celui qui contient `content/`
et `assets/images/`), pas celui du thème.

**Pas d'accès SSH/WP-CLI ?** `wp-plugin/cc-migration/` fait exactement la
même chose depuis un bouton dans l'admin WordPress (Extensions > Ajouter >
Téléverser) — voir `wp-plugin/README.md`. La logique de migration
(`migrate-functions.php`) est partagée entre les deux, un seul endroit à
maintenir.

## Ce qu'il reste à faire après la migration

1. **Vérifier les pages avec frise ou panneaux** (Notre histoire, École,
   Collège, Contact, Admissions) : relire le rendu des blocs `cc/timeline`
   et `cc/tri-panel` générés, notamment les couleurs d'accent École/Collège.
2. **Renseigner les logos** École/Collège/master en theme mods
   (`cc_logo_ecole_url`, `cc_logo_college_url`, `cc_logo_master_url`)
   depuis les images importées par le script, tant qu'aucun champ dédié
   n'existe dans le Customizer.
3. **Remplir les zones de widgets** du pied de page (`footer-college`,
   `footer-ecole`) si le contenu par défaut codé dans `footer.php` doit
   être personnalisé.
4. **Relire les Blocs HTML** issus des composants sans équivalent natif
   (bento, panneaux doubles, comparatif d'images, plan du site, FAQ) —
   fonctionnels tels quels, mais à reconvertir en blocs natifs si l'équipe
   éditoriale doit les modifier souvent sans toucher au HTML.
5. **Bandeau d'annonce** : réglable sans toucher au code, via Apparence >
   Personnaliser > Bandeau d'annonce (`inc/customizer.php`) — message et
   chemin de la page cible.

## Ne pas réintroduire

Pas de Divi, Elementor ni aucun page builder — voir `CLAUDE.md` à la
racine du dépôt pour l'ensemble des règles de conversion.
