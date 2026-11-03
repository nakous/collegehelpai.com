# 🧩 Paramètres Généraux

## Ajustez les Fondamentaux de l'Extension

Les paramètres généraux vous permettent de contrôler les aspects essentiels de l'expérience College Help AI. Ils influencent l'apparence, le comportement de l'interface et la fluidité d'utilisation.

### 🎯 Objectifs
- Adapter l'interface à votre flux d'étude
- Gérer les éléments visibles
- Contrôler le comportement des fenêtres de réponse

### 🗂️ Aperçu des Paramètres

| Paramètre | Description | Valeurs possibles | Défaut |
|-----------|-------------|-------------------|--------|
| Position de la fenêtre | Emplacement de la réponse sur la page | `droite-centre`, `gauche-centre`, `haut`, `bas` | `droite-centre` |
| Sauvegarde position | Mémorise placement manuel (drag & drop) | `activé` / `désactivé` | `activé` |
| Opacité au repos | Transparence en mode camouflage | 0%–100% | 30% |
| Langue interface | Langue dans le popup | FR, EN, ES, IT, AR, RU | Langue du navigateur |
| Langue des réponses | Langue utilisée par l'IA | FR, EN, ES, IT, AR, RU | Auto (selon contenu) |
| Historique réponses | Nombre de réponses sauvegardées | 0–50 | 10 |
| Mode compact | Réduit la taille de la fenêtre | `activé` / `désactivé` | `désactivé` |
| Notification audio | Son léger après une réponse | `activé` / `désactivé` | `désactivé` |
| Affichage mathématiques | Rendu MathJax avancé | `normal` / `avancé` | `normal` |

---

**📸 Espace réservé pour capture d'écran :** *Vue du panneau des paramètres généraux*

---

### 🖼️ Personnalisation de la Fenêtre de Réponse

#### Position Prédéfinie
- Sélectionnez une position prédéfinie via un menu déroulant
- Recommandation : `droite-centre` pour lecture fluide

#### Déplacement Libre
1. Maintenez la barre supérieure
2. Glissez la fenêtre dans la zone souhaitée
3. Relâchez pour sauvegarder automatiquement

---

**📸 Espace réservé pour capture d'écran :** *Démonstration déplacement de fenêtre*

---

### 🌐 Langues

#### Différence Interface vs Réponse
- **Interface** = menus, boutons, labels
- **Réponse** = contenu généré par l'IA selon la langue du texte ou réglage manuel

#### Modes de Langue de Réponse
| Mode | Description | Usage recommandé |
|------|-------------|------------------|
| Auto | Détection contexte | Multilingue / Général |
| Fixe (FR) | Force réponses en français | Études langue FR |
| Fixe (EN) | Force réponses en anglais | Recherche internationale |

---

**📸 Espace réservé pour capture d'écran :** *Sélecteur de langue double (interface + réponse)*

---

### 🧠 Historique des Réponses

#### Fonctionnalités
- Liste chronologique des interactions récentes
- Affichage condensé (question + début de réponse)
- Bouton "Voir plus" pour détails
- Exportation prévue (future version)

#### Nettoyage Manuel
1. Ouvrez paramètres > Historique
2. Sélectionnez éléments à supprimer
3. Cliquez sur "Effacer la sélection" ou "Tout effacer"

---

**📸 Espace réservé pour capture d'écran :** *Interface d'historique des réponses*

---

### 🔔 Notifications

#### Sons Discrets (Optionnels)
- Bips légers uniquement quand la réponse est prête
- Volume ajusté automatiquement selon contexte

#### Toast Visuel
- Petit rectangle translucide avec message : `Réponse prête !`
- Disparaît après 3–4 secondes

---

**📸 Espace réservé pour capture d'écran :** *Notification toast de réponse prête*

---

### ✨ Mode Compact

Réduit l'espace visuel de la fenêtre de réponse.

| Élément | Normal | Compact |
|---------|--------|---------|
| Padding intérieur | 16px | 6px |
| Taille police | 15px | 13px |
| Barre titre | Icône + titre | Icône seule |
| Actions | Texte + icône | Icône seule |

---

**📸 Espace réservé pour capture d'écran :** *Comparatif mode normal vs compact*

---

### 🔒 Stockage des Paramètres

#### Sécurité
Les paramètres sensibles sont :
- Chiffrés (clé locale + identifiant utilisateur)
- Validés avant lecture
- Nettoyés après logout

#### Fichier Centralisé
Stockage et logique gérés par : `storage-safety.js`

```js
// Exemple pseudo-code
saveSetting('ui.window.position', 'right-center');
getSetting('ui.window.opacity');
resetSettings(['ui.window.position', 'ui.window.opacity']);
```

### 🛠️ Réinitialisation des Paramètres

#### Réinitialiser Tout
1. Ouvrez Paramètres > Avancé
2. Cliquez sur "Réinitialiser tout"
3. Confirmez dans la popup
4. L'extension recharge avec les valeurs par défaut

#### Réinitialiser Sélectivement
Utilisez le menu contextuel dans chaque section

---

**📸 Espace réservé pour capture d'écran :** *Bouton de réinitialisation globale*

---

### 📌 Recommandations Initiales
| Paramètre | Valeur recommandée | Pourquoi |
|-----------|--------------------|----------|
| Position fenêtre | `droite-centre` | Lisibilité optimale |
| Mode compact | `désactivé` | Meilleure visibilité |
| Langue réponse | `Auto` | Flexibilité multilingue |
| Historique | 10 éléments | Équilibre mémoire / utilité |
| Opacité camouflage | 30% | Discrétion sans gêne |

### ❓ FAQ Rapide

**Q: La langue de réponse n'est pas correcte ?**
R: Activez le mode fixe dans la section Langues.

**Q: La fenêtre se repositionne mal ?**
R: Réinitialisez la position ou désactivez la sauvegarde.

**Q: L'historique est vide ?**
R: Vérifiez que la fonctionnalité est activée dans paramètres généraux.

### 🚀 Prochaine Étape
Passez à **[Langues et Localisation](langues.md)** pour affiner la personnalisation multilingue.
