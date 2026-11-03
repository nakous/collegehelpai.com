# TODO — CollegeHelp AI

Contexte et structure du projet : voir `context.md`. Les TODO propres à l'extension sont dans `collegehelpai-ex/TODO.md`.

## P0 — Sécurité et bugs bloquants (`site/college-help-ai/college-help-ai.php`)

- [ ] **Authentification de l'API** : `verify_user_token()` renvoie `true` quand `user_id` ou `Authorization` est absent, donc `/fetch-answer`, `/user-data` et `/auth/logout` sont ouverts sans token. Renvoyer `false` dans ce cas (et utiliser `hash_equals` sur des chaînes non vides).
- [ ] **`get_user_data()`** : renvoie `true` quand `user_id` ou le token manque. Renvoyer une erreur 401 à la place.
- [ ] **Route `/auth/logout` cassée** : le callback `logout_user` n'existe pas, la méthode définie s'appelle `handle_logout`. Corriger le nom du callback.
- [ ] **Crédits** : dans `validate_my_session_callback`, la condition `!is_object(...) || !method_exists(...)` est inversée. Si la feature `credits` est absente, la méthode est appelée sur un non-objet (erreur fatale). Si elle est présente, les crédits valent 10 en dur.
- [ ] **`user_id == 1` abonné d'office** : retirer ce cas particulier codé en dur, ou le passer en option.
- [ ] Retirer le `error_log()` de debug dans `verify_user_token()`.

## P1 — Publication et infrastructure

- [ ] Créer le dépôt GitHub du projet (racine : thème + plugins, ou un dépôt par dossier de `site/`).
- [ ] Configurer GitHub Actions : build de l'extension (`npm run build`), archive du thème et des plugins.
- [ ] Mettre à jour l'extension (build, vérification `isProduction = true` dans `background.js` et `lib/popup.js`, publication Chrome Web Store et Edge Add-ons).
- [ ] Mettre à jour le site (thème `cobra`, plugins `college-help-ai` et `college-help-extension-popup` vers `wp-content`).
- [ ] Valider ou restreindre `<all_urls>` (`host_permissions` et `content_scripts`) pour passer la revue des stores.
- [ ] Renseigner les vrais liens Chrome et Edge dans les options du plugin popup (valeurs par défaut `LINK_CHROME` et `LINK_EDGE`).

## P2 — Analytics : Google Analytics vers Matomo

- [ ] Trouver où le suivi actuel est injecté (aucun code Google Analytics ni Matomo dans le dépôt ; Clarity n'est qu'une ligne commentée dans `functions.php`).
- [ ] Retirer Google Analytics.
- [ ] Ajouter Matomo dans le thème (`wp_head`), avec le code ci-dessous.

```html
<!-- Matomo -->
<script>
  var _paq = window._paq = window._paq || [];
  /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="https://stats.onlevelup.com/";
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', '7']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<!-- End Matomo Code -->
```

- [ ] Vérifier dans Matomo que les visites arrivent, puis supprimer l'ancien suivi.

## P3 — SEO (`site/seo/` est prévu pour les livrables)

- [ ] Analyser et optimiser le SEO de chaque page du site (title, meta description, titres H1-H3, schema.org, maillage interne, images, vitesse).
- [ ] Optimiser les pages store (Chrome Web Store et Edge Add-ons) : titre, description, captures, mots-clés.
- [ ] Vérifier `robots.txt` et le sitemap Yoast après les changements.

## P4 — Documentation et nettoyage

- [ ] Réécrire `collegehelpai-ex/readme.md` : il parle encore de « quizSlover AI » et des endpoints `/quizSlover-help-ai/v1/...` (ils sont sous `/college-help-ai/v1`).
- [ ] Mettre à jour `collegehelpai-ex/TODO.md` : il indique la version 1.2.0 alors que le manifest est en 1.3.0.
- [ ] Corriger les métadonnées du plugin `college-help-ai` (`Author: Your Name`).
- [ ] Documenter ou retirer la doc Firefox (`gitBook/fr/installation/firefox.md`) : l'extension ne supporte pas Firefox.
- [ ] Documenter les routes de l'API `college-help-ai/v1` et le plugin `cobra_ai` dont dépend le plugin core (hors dépôt).
