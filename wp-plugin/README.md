# Plugin de migration — Cours Chambertin

Alternative sans SSH ni WP-CLI au script `wp-theme/migration/import.php` :
un plugin WordPress classique, installable depuis l'admin (`/gestion`).

## Construire le ZIP

```bash
bash wp-plugin/build-zip.sh
```

Produit `wp-plugin/cc-migration.zip`, en copiant à l'intérieur les sources
canoniques du dépôt (`wp-theme/migration/*.php`, `content/*.html`,
`assets/images/*.jpg`/`*.png`) — ces copies (`cc-migration/includes/`,
`cc-migration/data/`) et le zip lui-même sont ignorés par git (voir
`.gitignore`) : à reconstruire après toute modification du convertisseur,
du manifest ou du contenu, jamais à committer tel quel.

## Utilisation

1. **Installer d'abord le thème** : Apparence > Thèmes > Ajouter >
   Téléverser un thème → `wp-theme/cours-chambertin.zip` (généré par
   `bash wp-theme/build-zip.sh`) → Activer.
2. Extensions > Ajouter > Téléverser une extension → `cc-migration.zip` →
   Installer → Activer.
3. Un nouvel item apparaît sous **Outils > Migration Cours Chambertin**.
   Cliquer sur **Lancer la migration**.
4. Un journal détaille chaque page/article/image créé. Idempotent :
   relancer ne duplique rien (utile si une exécution s'interrompt).
5. Une fois la migration terminée et vérifiée, **désactiver puis
   supprimer** l'extension (Extensions) — elle n'a plus d'utilité et
   n'a pas vocation à rester active sur le site en production.

## Ce que fait exactement le plugin

Strictement la même chose que `wp eval-file wp-theme/migration/import.php`
(voir `wp-theme/README.md`) : la logique vit dans
`wp-theme/migration/migrate-functions.php`, partagée entre le script
WP-CLI et ce plugin — un seul endroit à maintenir. Le plugin ne fait que
fournir un déclencheur (bouton d'admin) et embarquer une copie du contenu
et des images à la place d'un chemin de dépôt passé en argument.
