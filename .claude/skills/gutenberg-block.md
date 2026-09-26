---
name: gutenberg-block
description: Créer ou adapter un bloc Gutenberg (natif ou custom) pour le thème WordPress du Cours Chambertin, à partir des composants CSS existants (cc-*).
---

# gutenberg-block — blocs Gutenberg du Cours Chambertin

## Principe

Le thème est sur-mesure et sans page builder : tout le contenu s'édite
avec l'éditeur de blocs natif de WordPress. La règle par défaut est de
**réutiliser les blocs natifs** (Group, Colonnes, Image, Détails, Bouton,
etc.) et de leur appliquer les classes CSS `cc-*` déjà définies dans
`assets/css/style.css`. Un bloc custom n'est justifié que si aucune
combinaison de blocs natifs ne peut reproduire le composant.

## Composants existants et leur équivalent Gutenberg

| Classe CSS         | Usage                                    | Équivalent Gutenberg                                  |
|--------------------|-------------------------------------------|--------------------------------------------------------|
| `cc-section`        | Section pleine largeur avec fond alterné  | Bloc Group, variante de fond (`cc-section--craie` / `--blanc`) |
| `cc-narrow`         | Conteneur de largeur lecture              | Bloc Group avec largeur de contenu réduite              |
| `cc-dual-panel`     | Deux blocs côte à côte (École / Collège)  | Bloc Colonnes (2 colonnes) + classes latérales           |
| `cc-tri-panel`      | Trois blocs côte à côte                   | Bloc Colonnes (3 colonnes)                               |
| `cc-faq`            | Liste de questions/réponses dépliables    | Bloc natif **Détails** (`core/details`) répété           |
| `cc-timeline`       | Frise chronologique                       | Bloc custom (pas d'équivalent natif direct)              |
| `cc-plan`           | Plan du site en colonnes                  | Bloc Liste / Colonnes selon la structure                 |
| `cc-pied`           | Pied de page                              | Reste dans `footer.php` (hors éditeur)                   |
| `cc-page-title`     | `<h1>` visuel de page                     | Bloc Titre (niveau 1), classe additionnelle              |

Pour toute nouvelle page, préférer cette table à la création d'un bloc.

## Créer un bloc custom (quand nécessaire)

Utiliser l'API des blocs natifs (`@wordpress/create-block`), pas un
raccourci manuel :

```bash
npx @wordpress/create-block cc-timeline --template @wordpress/create-block-tutorial-template
```

Structure attendue dans le thème :

```
blocks/
  cc-timeline/
    block.json      # name, title, category, attributes, supports
    edit.js          # rendu dans l'éditeur
    save.js          # markup statique sauvegardé (ou render.php si dynamique)
    style.css         # importe/complète les variables --cc-* existantes
    index.js
```

Règles :

1. **`block.json`** : `"category": "cours-chambertin"` (créer la
   catégorie dans `functions.php` si elle n'existe pas), namespace
   `cours-chambertin/cc-timeline`.
2. **Style** : ne pas redéfinir les couleurs en dur — consommer les
   variables CSS globales (`--cc-accent`, `--cc-section-accent`,
   `--cc-footer-bg`, etc.) déjà déclarées sur `:root` par le thème, pour
   que le bloc suive automatiquement le thème École (bleu) / Collège
   (bordeaux) selon le bloc parent.
3. **Accessibilité** : conserver la structure sémantique (listes,
   `<h2>`/`<h3>` cohérents avec la hiérarchie de la page, jamais de
   `<h1>` supplémentaire — un seul par page via le bloc Titre principal).
4. **Attributs** : exposer en `attributes` uniquement ce qu'un rédacteur
   doit pouvoir modifier depuis l'éditeur (textes, dates, liens) — pas de
   configuration technique en dur dans le bloc.
5. **Rendu dynamique** : si le bloc doit lire des données serveur
   (ex. articles d'actualités), utiliser `render.php` (`"render":
   "file:./render.php"` dans `block.json`) plutôt que dupliquer la logique
   en JS.

## Migration des fragments HTML existants

Pour convertir un fragment de `content/*.html` en contenu Gutenberg :

1. Le fragment utilise déjà des classes `wp-block-group` sur les
   conteneurs de section : coller le fragment dans l'éditeur en mode
   code (`Éditeur de code`) fonctionne directement pour la structure de
   base.
2. Vérifier après collage que chaque bloc Group est bien reconnu comme
   bloc natif dans l'éditeur visuel (pas de "bloc non reconnu") avant de
   publier.
3. Les composants sans équivalent natif (frise, tri-panel) doivent être
   remplacés par le bloc custom correspondant, pas laissés en HTML brut
   dans un bloc "HTML personnalisé" (perte de l'édition visuelle pour les
   rédacteurs non techniques).

## Ne jamais faire

- Ne pas installer Divi, Elementor ou un autre page builder : cela
  contredit l'objectif du thème sur-mesure.
- Ne pas utiliser de style inline dans les blocs pour reproduire un
  composant `cc-*` existant — toujours passer par la classe CSS.
- Ne pas dupliquer les variables de couleur dans le CSS d'un bloc custom :
  toujours référencer les variables globales du thème.
