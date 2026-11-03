# CollegeHelp AI — contexte du projet

**CollegeHelp AI** (https://collegehelpai.com/) est une extension de navigateur d'aide aux devoirs avec l'IA : l'utilisateur sélectionne du texte ou capture une zone d'écran, et l'extension affiche la réponse dans un bloc flottant sur la page.

## Technologies

- **Site** : WordPress (thème + plugins PHP, PHP 7.4+).
- **Extension** : Manifest V3, JavaScript vanilla, MathJax local (`lib/tex-mml-chtml.js`), build avec Terser (`npm run build`, sortie dans `dist/`).
- **Navigateurs** : Chrome et Edge. Firefox n'est pas supporté (le manifest n'a pas de config Gecko) ; il n'apparaît que dans la doc FR (`gitBook/fr/installation/firefox.md`).
- **Langues de l'extension** : EN, FR, ES, IT, AR, RU.
- **Dépendance externe** : le plugin `college-help-ai` appelle `cobra_ai()` (features `ai`, `credits`, `stripesubscriptions`). Ce plugin n'est **pas** dans ce dossier ; il gère l'appel OpenAI, les crédits et Stripe.

## Structure du projet

```
collegehelpai.com/
├── context.md
├── TODO.md                                  # tâches globales
├── collegehelpai-ex/                        # extension (dépôt git propre : github.com/nakous/collegehelpai-ex)
└── site/
    ├── cobra/                               # thème WordPress (v2.1)
    ├── college-help-ai/                     # plugin core : API de l'extension + config (v1.0.0)
    ├── college-help-extension-popup/        # plugin : popup marketing d'installation (v1.0.0)
    └── seo/                                 # vide pour l'instant
```

Le dossier racine n'est pas un dépôt git ; seul `collegehelpai-ex/` en est un.

### Extension (`collegehelpai-ex/`, v1.3.0)

- `manifest.json`, `background.js` (service worker : session, API, raccourcis, menu contextuel), `popup.html`, `onboarding.html`.
- `lib/` : `content.js`/`content.css` (bloc de réponse, overlay de capture), `popup.js`/`popup.css`, `i18n.js`, `onboarding.js`, `storage-safety.js`, `tex-mml-chtml.js`.
- `_locales/<lang>/messages.json`, `assets/` (icônes, logos), `gitBook/` (doc utilisateur EN/FR), `minify.js`.
- Raccourcis : `Ctrl+Shift+A` (poser la question sur le texte sélectionné), `Ctrl+Shift+S` (capture d'écran).
- Couleurs : `#69b8db`, `#4c5e87`, `#FFFFFF`.

### Thème `site/cobra`

Thème sur mesure (Bootstrap 5.1.3, Font Awesome 6.4, SEO/schema.org, accessibilité, `robots.txt` personnalisé). Le sitemap est géré par Yoast SEO. Le template `single-stripe_plan.php` affiche les plans d'abonnement.

### Plugin `site/college-help-ai`

API REST `college-help-ai/v1` utilisée par l'extension :
- `POST /fetch-answer` : envoie la question ou l'image à l'IA.
- `POST /user-data` : données de l'utilisateur.
- `POST /auth/logout` : déconnexion.

Il expose aussi l'action AJAX `validate_my_session` (`admin-ajax.php`). L'extension s'en sert pour valider la session WordPress et récupérer le token, le nonce et les crédits. La page Réglages > College Help AI configure les limites du plan gratuit et les prompts.

### Plugin `site/college-help-extension-popup`

Popup qui invite à installer l'extension, avec statistiques stockées dans la table `extension_popup_stats`. Les liens Chrome et Edge se règlent dans les options WordPress (`college_help_chrome_link`, `college_help_edge_link`) ; les valeurs par défaut sont des placeholders (`LINK_CHROME`, `LINK_EDGE`).

## Pages et liens

- Pages store :
  - Chrome : https://chromewebstore.google.com/detail/ai-college-help-for-homew/hkpnmedofadjhlllcppmpadkadhcnpbe
  - Edge : https://microsoftedge.microsoft.com/addons/detail/ai-college-help-for-hom/ldfjgaenfdeocmcjjhdhhfoeaphcbeln
- Pages du site utilisées par l'extension : `/login`, `/registration`, `/my-account`, `/plans/monthly-plan/`, `/plans/annual-plan/`, `/faq/`, `/contact-us/`, `/privacy-policy/`.
- Contact : https://collegehelpai.com/contact-us/

## Déploiement

- Plugins vers `/public_html/wp-content/plugins/` (`college-help-ai`, `college-help-extension-popup`).
- Thème vers `/public_html/wp-content/themes/` (`cobra`).
- Extension : `npm run build` dans `collegehelpai-ex/`, puis publication sur le Chrome Web Store et Edge Add-ons.
- Environnement local : `http://college.localhost/`. Le basculement se fait via `isProduction`, une constante codée en dur dans `background.js` et dans `lib/popup.js`. Elle doit être à `true` pour publier.

## Analytics

Aucun code Google Analytics ni Matomo dans ce dossier. Le README du thème cite Microsoft Clarity, mais `functions.php` ne contient qu'une ligne commentée. Le suivi est donc injecté ailleurs (réglage WordPress ou autre plugin) : à vérifier sur le site avant la migration vers Matomo (voir `TODO.md`).

## Suivi

Les tâches et les points à corriger sont dans `TODO.md`.
