#!/usr/bin/env bash
# Zippe le thème cours-chambertin, prêt pour Apparence > Thèmes > Ajouter >
# Téléverser un thème.
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

cd "$ROOT/wp-theme"
rm -f cours-chambertin.zip
zip -rq cours-chambertin.zip cours-chambertin \
  -x '*.DS_Store'

echo "Créé : $ROOT/wp-theme/cours-chambertin.zip ($(du -h cours-chambertin.zip | cut -f1))"
