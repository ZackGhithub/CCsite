#!/usr/bin/env bash
# Assemble et zippe le plugin cc-migration : copie les sources canoniques
# (wp-theme/migration/, content/, assets/images/) dans le plugin, puis
# construit cc-migration.zip, prêt pour Extensions > Ajouter > Téléverser
# une extension.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
PLUGIN_DIR="$ROOT/wp-plugin/cc-migration"

rm -rf "$PLUGIN_DIR/includes" "$PLUGIN_DIR/data"
mkdir -p "$PLUGIN_DIR/includes" "$PLUGIN_DIR/data/content" "$PLUGIN_DIR/data/images"

cp "$ROOT/wp-theme/migration/html-to-blocks.php" "$PLUGIN_DIR/includes/"
cp "$ROOT/wp-theme/migration/migrate-functions.php" "$PLUGIN_DIR/includes/"
cp "$ROOT/wp-theme/migration/pages.php" "$PLUGIN_DIR/includes/"

cp "$ROOT"/content/*.html "$PLUGIN_DIR/data/content/"
cp "$ROOT"/assets/images/*.jpg "$ROOT"/assets/images/*.png "$PLUGIN_DIR/data/images/"

cd "$ROOT/wp-plugin"
rm -f cc-migration.zip
zip -rq cc-migration.zip cc-migration \
  -x '*.DS_Store'

echo "Créé : $ROOT/wp-plugin/cc-migration.zip ($(du -h cc-migration.zip | cut -f1))"
