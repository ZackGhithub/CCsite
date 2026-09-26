# Cours Chambertin — site WordPress/Gutenberg

## Contexte du projet

Ce dépôt contient la maquette du site de l'ensemble scolaire École +
Collège Chambertin (Asnières-sur-Seine), destinée à être convertie en
**thème WordPress sur-mesure, sans Divi ni constructeur de pages**, avec
édition du contenu au **bloc éditeur natif (Gutenberg)**.

La maquette actuelle est un générateur statique (`build.py` + fragments
`content/*.html`) qui reproduit volontairement la séparation
contenu / gabarit / présentation d'un thème WordPress classique, pour
rendre la conversion directe :

- `content/*.html` → `post_content` de chaque page (collé tel quel dans
  l'éditeur de blocs)
- Le bloc `TEMPLATE` de `build.py` → `header.php` + `footer.php` +
  `page.php`
- Les classes CSS (`cc-section`, `cc-bento`, `cc-dual-panel`,
  `cc-tri-panel`, `cc-timeline`, `cc-faq`, `cc-plan`, `cc-pied`, etc.) sont
  génériques et se retrouvent telles quelles dans le thème final
- Le HTML des fragments utilise déjà les classes de blocs Gutenberg natifs
  (`wp-block-group`, etc.) pour rester compatible avec l'éditeur sans
  bloc custom supplémentaire quand ce n'est pas nécessaire

## Langue

Le site et son contenu sont **entièrement en français**. Toujours répondre
en français dans ce projet, sauf demande contraire explicite. Les noms de
variables, fonctions et commits peuvent rester en anglais (convention du
code), mais le contenu éditorial, les messages de commit descriptifs et
toute communication doivent être en français.

## Structure actuelle du dépôt

```
content/            Fragments HTML de contenu (futur post_content WP)
build.py             Générateur statique : gabarit + réécriture des liens
assets/css/style.css Feuille de style unique (palette, composants)
assets/js/main.js    Menu mobile + sous-menus tactiles
assets/images/       Photos et logos (JPEG + WebP, logos optimisés)
<dossiers de pages>/  Sortie statique générée par build.py
sitemap.xml, robots.txt
```

## Palette et variables CSS (à conserver dans le thème WP)

- `--cc-ink`, `--cc-ink-soft` : texte
- `--cc-craie`, `--cc-blanc` : fonds clairs
- `--cc-accent` / `--cc-accent-dark` (bordeaux #8B1E2D) : accent Collège
- `--cc-bleu` / `--cc-bleu-dark` (#245460) : accent École
- `--cc-section-accent` : variable de section, bascule bordeaux ↔ bleu
  selon le contexte (École vs Collège) — à piloter via une classe de bloc
  parent, pas en dur
- `--cc-footer-bg` (#1E1613) : fond pied de page et hero
- Polices : Spectral (serif, titres) / Montserrat (sans, texte courant)

## Règles de conversion vers WordPress

1. **Ne jamais réintroduire Divi ou un page builder.** Le thème est
   sur-mesure, l'édition se fait avec les blocs natifs de Gutenberg
   (Group, Colonnes, Détails/FAQ natif si possible, etc.), complétés par
   des blocs custom uniquement quand un composant n'a pas d'équivalent
   natif (ex. `cc-tri-panel`, `cc-timeline`).
2. **Contenu vs présentation.** Le CSS des composants (`cc-*`) reste dans
   la feuille de style du thème, jamais en style inline dans les blocs.
3. **Chemins relatifs uniquement dans le contenu WordPress.** Les liens
   internes et les chemins d'images doivent utiliser les fonctions
   WordPress standard (`home_url()`, `wp_get_attachment_image()`, liens
   relatifs stockés en base) — ne jamais coder en dur un domaine ou un
   préfixe de type `clone0726`.
4. **Accessibilité et SEO déjà en place à préserver** : un seul `<h1>`
   par page (classe `cc-page-title`), attributs alt sur toutes les images,
   contraste WCAG AA, `:focus-visible` sur les éléments interactifs,
   métadonnées Open Graph / Twitter / JSON-LD, sitemap et robots.txt.
5. **Images** : conserver le double format JPEG + WebP via `<picture>`
   pour les photos ; les logos restent en PNG optimisé (déjà redimensionnés
   à 640px max).

## Workflow

- Toujours développer sur la branche `claude/loving-faraday-7x8p96`.
- Après toute modification de `content/`, du gabarit ou du CSS dans la
  maquette statique, relancer `python3 build.py` avant de committer.
- Utiliser le skill `gutenberg-block` pour créer ou adapter un bloc
  Gutenberg custom.
- Utiliser le skill `git-deploy` pour le workflow de commit/push sur ce
  projet.
