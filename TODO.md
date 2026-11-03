# TODO — CollegeHelp AI

Contexte et structure du projet : voir `context.md`. Les TODO propres à l'extension sont dans `collegehelpai-ex/TODO.md`.

Ordre de travail : la version **3.0.1** d'abord (publication, analytics, SEO, documentation). La version **3.1.0** (sécurité et bugs de l'API) ne démarre qu'une fois le workflow de déploiement en place, voir la dernière section.

## v3.0.1 — Publication et infrastructure

- [x] Passer tout le projet à la version 3.0.1 : extension (1.3.0), plugins `college-help-ai` et `college-help-extension-popup` (1.0.0) et thème `cobra` (2.1).
- [x] Créer le dépôt GitHub du projet : `github.com/nakous/collegehelpai.com` (monorepo : extension, thème, plugins). L'ancien dépôt `collegehelpai-ex` n'est plus utilisé.
- [ ] Configurer GitHub Actions : le workflow est écrit (`.github/workflows/deploy.yml`, lint PHP puis `rsync` sur SSH, déploiement automatique à chaque push sur `main` qui touche `site/`) mais pas encore testé. Il reste à configurer l'environnement `prod` (voir la tâche suivante) et à lancer un premier déploiement. Il déploie en production uniquement le thème et les plugins, rien d'autre (pas de build ni de publication de l'extension) :
  - `site/cobra/` vers `/public_html/wp-content/themes/cobra/`
  - `site/college-help-ai/` et `site/college-help-extension-popup/` vers `/public_html/wp-content/plugins/`
  - Déclenchement sur push vers `main`, limité aux changements dans `site/` (`paths: site/**`).
  - Accès au serveur par clé SSH (déploiement avec `rsync` sur SSH). Créer une clé dédiée au déploiement, avec sa clé publique dans `authorized_keys` du serveur. Mettre la clé privée, l'hôte, l'utilisateur et le port dans les secrets GitHub du dépôt (`SSH_PRIVATE_KEY`, `SSH_HOST`, `SSH_USER`, `SSH_PORT`), jamais dans le code, utulise les variable de l'env "prod".
  - Utiliser `rsync` sans `--delete` global : une suppression ne doit jamais toucher les autres plugins ni les `uploads` de WordPress. Le workflow n'utilise `--delete` qu'à l'intérieur des trois dossiers du projet.
- [ ] Configurer l'environnement GitHub `prod` (Settings > Environments), sans validation manuelle :
  - Secrets : `SSH_PRIVATE_KEY` (clé dédiée au déploiement) et `SSH_KNOWN_HOSTS` (résultat de `ssh-keyscan -p <port> <hote>`).
  - Variables : `SSH_HOST`, `SSH_USER`, et optionnellement `SSH_PORT` (22 par défaut) et `REMOTE_ROOT` (`/home/onlevelup/public_html` par défaut).
  - Vérifier que `rsync` est installé sur le serveur et que la clé publique est dans `authorized_keys`.
  - Premier test : lancer le workflow à la main (onglet Actions > Run workflow), puis vérifier le thème et les plugins sur le site.
- [ ] Mettre à jour l'extension (build, vérification `isProduction = true` dans `background.js` et `lib/popup.js`, publication Chrome Web Store et Edge Add-ons).
- [ ] Mettre à jour le site (thème `cobra`, plugins `college-help-ai` et `college-help-extension-popup` vers `wp-content`).
- [ ] Renseigner les vrais liens Chrome et Edge dans les options du plugin popup (valeurs par défaut `LINK_CHROME` et `LINK_EDGE`).

## v3.0.1 — Analytics : Google Analytics vers Matomo

- [x] Ajouter Matomo dans le thème : fonction `cobra_add_matomo_tracking()` dans `site/cobra/functions.php` (hook `wp_head`, site ID 7 sur `https://stats.onlevelup.com/`, pas de suivi dans l'aperçu du Customizer) et `dns-prefetch` vers `stats.onlevelup.com`. Il sera en ligne au prochain déploiement.

## v3.0.1 — SEO (`site/seo/` est prévu pour les livrables)

- [ ] Analyser et optimiser le SEO de chaque page du site (title, meta description, titres H1-H3, schema.org, maillage interne, images, vitesse).
- [ ] Optimiser les pages store (Chrome Web Store et Edge Add-ons) : titre, description, captures, mots-clés.
- [ ] Vérifier `robots.txt` et le sitemap Yoast après les changements.

## v3.0.1 — Documentation et nettoyage

- [ ] Réécrire `collegehelpai-ex/readme.md` : il parle encore de « quizSlover AI » et des endpoints `/quizSlover-help-ai/v1/...` (ils sont sous `/college-help-ai/v1`).
- [ ] Mettre à jour `collegehelpai-ex/TODO.md` : la version courante est à jour (3.0.1), mais il garde des jalons v1.3.0 / v1.4.0 / v1.5.0 qui ne correspondent plus au numéro de version actuel.
- [ ] Corriger les métadonnées du plugin `college-help-ai` (`Author: Your Name`).
- [ ] Documenter ou retirer la doc Firefox (`gitBook/fr/installation/firefox.md`) : l'extension ne supporte pas Firefox.
- [ ] Documenter les routes de l'API `college-help-ai/v1` et le plugin `cobra_ai` dont dépend le plugin core (hors dépôt).

## v3.1.0 — Sécurité et bugs de l'API (après la mise en place du workflow, `site/college-help-ai/college-help-ai.php`)

- [ ] **Authentification de l'API** : `verify_user_token()` renvoie `true` quand `user_id` ou `Authorization` est absent, donc `/fetch-answer`, `/user-data` et `/auth/logout` sont ouverts sans token. Renvoyer `false` dans ce cas (et utiliser `hash_equals` sur des chaînes non vides).
- [ ] **`get_user_data()`** : renvoie `true` quand `user_id` ou le token manque. Renvoyer une erreur 401 à la place.
- [ ] **Route `/auth/logout` cassée** : le callback `logout_user` n'existe pas, la méthode définie s'appelle `handle_logout`. Corriger le nom du callback.
- [ ] **Crédits** : dans `validate_my_session_callback`, la condition `!is_object(...) || !method_exists(...)` est inversée. Si la feature `credits` est absente, la méthode est appelée sur un non-objet (erreur fatale). Si elle est présente, les crédits valent 10 en dur.
- [ ] **`user_id == 1` abonné d'office** : retirer ce cas particulier codé en dur, ou le passer en option.
- [ ] Retirer le `error_log()` de debug dans `verify_user_token()`.
