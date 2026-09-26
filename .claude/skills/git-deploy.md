---
name: git-deploy
description: Workflow de commit/push pour le projet Cours Chambertin (branche unique, build avant commit, messages de commit).
---

# git-deploy — workflow Git du Cours Chambertin

## Branche

Ce dépôt n'a qu'une seule branche active : `claude/loving-faraday-7x8p96`.
C'est aussi la branche par défaut. Sauf demande explicite contraire :

- Toujours committer et pousser sur `claude/loving-faraday-7x8p96`.
- Ne jamais créer de nouvelle branche sans autorisation explicite.
- Ne jamais forcer un push (`--force`) sans autorisation explicite.

## Avant tout commit

1. Si `content/*.html`, `build.py` ou `assets/css/style.css` ont changé,
   régénérer le site statique :
   ```bash
   python3 build.py
   ```
2. Vérifier qu'aucun lien interne n'est cassé (le script de build réécrit
   les chemins relatifs ; relire la sortie si des pages ont été ajoutées
   ou déplacées dans `PAGES`).
3. `git status` pour vérifier ce qui est réellement modifié avant
   `git add` — ne jamais utiliser `git add -A` ou `git add .` à l'aveugle
   sur ce projet (fichiers générés volumineux, images).

## Commits

- Un commit = un changement cohérent (pas de mélange contenu +
  refactor + style dans le même commit sauf demande explicite).
- Message de commit en anglais, style impératif court, comme l'historique
  existant du projet (ex. `Hero: drop the wine tint, match the footer's
  neutral dark exactly`, `Add sitewide préinscription announcement bar`).
- Toujours committer via heredoc pour préserver le formatage :
  ```bash
  git commit -m "$(cat <<'EOF'
  Résumé court à l'impératif

  Détail optionnel si nécessaire.
  EOF
  )"
  ```
- Ne jamais utiliser `git commit --amend` sauf demande explicite : créer
  un nouveau commit à la place.
- Ne jamais utiliser `--no-verify` pour contourner un hook.

## Push

```bash
git push -u origin claude/loving-faraday-7x8p96
```

En cas d'échec réseau uniquement, réessayer avec un backoff exponentiel
(2s, 4s, 8s, 16s), jusqu'à 4 fois. Ne jamais réessayer un push rejeté pour
une autre raison (conflit, branche protégée) sans en informer l'utilisateur.

## Pull requests

Ne jamais créer de pull request sans demande explicite de l'utilisateur.
Quand une PR est demandée, vérifier d'abord l'absence de template
(`.github/pull_request_template.md`) avant de rédiger la description.

## Après le déploiement WordPress

Une fois la conversion en thème WordPress commencée, ce workflow reste
valable pour le dépôt Git du thème (`wp-content/themes/cours-chambertin/`) :
le contenu éditorial vivra alors dans la base WordPress, mais le code du
thème (PHP, CSS, JS, blocs custom) continue de suivre ce même workflow Git.
