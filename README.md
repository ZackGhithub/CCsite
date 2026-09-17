# Site du Cours Chambertin (maquette statique)

Reproduction fidèle, en HTML/CSS/JS statique, du site de référence
`https://www.courschambertin.fr/clone0726/` (ensemble scolaire École +
Collège Chambertin, Asnières-sur-Seine).

Ce site sert de maquette de test avant un futur déploiement sur WordPress
(thème sur-mesure, sans Divi). La structure a donc été pensée pour une
conversion facile : contenu, gabarit et présentation sont séparés.

## Structure du dépôt

```
content/            Fragments HTML de contenu, un fichier par page
                     (équivalent du post_content de chaque page WordPress)
build.py             Générateur statique : assemble content/ + le gabarit
                     commun (en-tête, navigation, pied de page) et réécrit
                     les liens/images en chemins relatifs
assets/
  css/style.css       Feuille de style unique (palette, typographies,
                       composants : sections, cartes, frise, FAQ, etc.)
  js/main.js          Menu mobile + sous-menus tactiles
  images/             Photos et logos réels récupérés du site de référence
<dossiers de pages>/  Pages HTML statiques générées (index.html par dossier,
                       une arborescence par section : ecole/, college/, etc.)
```

## Régénérer le site

Le contenu des pages vit dans `content/*.html` (fragments HTML propres,
sans dépendance à WordPress). Pour reconstruire toutes les pages statiques
après une modification de `content/`, du gabarit dans `build.py` ou de la
feuille de style :

```bash
python3 build.py
```

Le script régénère chaque `index.html` à sa place (ex. `ecole/cp/index.html`)
en respectant l'arborescence définie dans le tableau `PAGES` de `build.py`.

## Conversion future vers WordPress

- Chaque fichier de `content/` correspond au contenu d'une page WordPress
  (`post_content`) : il peut être recollé tel quel dans l'éditeur.
- Le bloc `TEMPLATE` de `build.py` correspond à `header.php` + `footer.php`
  + `page.php` d'un thème WordPress classique.
- Les classes CSS (`cc-section`, `cc-bento`, `cc-dual-panel`,
  `cc-timeline`, `cc-faq`, `cc-plan`, `cc-pied`, etc.) sont génériques et
  réutilisables telles quelles dans le thème final.

## Origine du contenu

Le contenu réel (textes, images, logos, palette de couleurs, structure de
navigation) provient d'un export de base de données WordPress du site de
préproduction (`clone0726`), le dossier `/clone0726/` du site en ligne
étant protégé contre les accès automatisés. Voir le récapitulatif fourni
avec la livraison pour le détail des pages reprises et des éléments non
récupérés.
