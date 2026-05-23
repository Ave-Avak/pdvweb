# 🔍 Rapport d'audit sécurité — PDVWeb

**Date d'audit** : étape 8 du projet
**Périmètre** : ensemble du code source PHP, schéma SQL, configuration

Ce rapport documente l'audit ligne-par-ligne des fichiers critiques pour détecter les vulnérabilités potentielles et confirme les mesures de protection en place.

---

## 1. Authentification

### Fichiers audités
- `classes/Membre.php` — méthodes `creer()`, `tenterConnexion()`, `changerMotPasse()`
- `classes/util/Auth.php` — méthodes `connecter()`, `deconnecter()`, `requireLogin()`, `requireAdmin()`
- `public/login.php`, `public/logout.php`, `public/inscription.php`

### Constats

| Point vérifié | Statut | Détail |
|---|---|---|
| Hash des mots de passe | ✅ | `password_hash($mdp, PASSWORD_BCRYPT)` |
| Vérification timing-safe | ✅ | `password_verify()` (constant-time) |
| Anti brute-force par login | ✅ | 5 échecs → 15 min de blocage |
| Anti brute-force par IP | ✅ | 10 échecs / 15 min depuis la même IP |
| Régénération session ID au login | ✅ | `session_regenerate_id(true)` |
| Reset CSRF token au login | ✅ | `Csrf::regenerer()` dans `Auth::connecter` |
| Session strict mode | ✅ | `session.use_strict_mode = 1` |
| Logout complet | ✅ | `session_unset()` + `session_destroy()` + cookie supprimé |

### Risques résiduels

- ⚠️ **Pas de 2FA** : acceptable pour un TFM, à ajouter en prod si secteur sensible
- ⚠️ **Politique mot de passe basique** : 8 caractères min, pas de complexité forcée. À durcir en prod.

---

## 2. Injection SQL

### Méthode d'audit
- `grep -rn "->query(" classes/` → recherche des requêtes brutes potentiellement concaténées
- `grep -rn "\\\$_POST\\|\\\$_GET\\|\\\$_REQUEST" classes/` → recherche de variables superglobales dans les classes (devraient toujours passer par les contrôleurs)

### Constats

| Méthode | Vérification | Risque |
|---|---|---|
| `Db::pdo()->prepare(...)` puis `->execute([...])` | ✅ Partout | Nul |
| `Db::pdo()->query("SELECT ...")` | ✅ Uniquement avec chaînes statiques | Nul |
| Concaténation `LIMIT $parPage OFFSET $offset` | ✅ Variables forcées en `(int)` au préalable | Nul |
| `bindValue(1, $val, PDO::PARAM_INT)` | ✅ Pour les LIMIT dynamiques | Nul |

### Risques résiduels
**Aucun identifié.** Toutes les requêtes utilisent des prepared statements ou des valeurs forcées en type entier.

---

## 3. XSS (Cross-Site Scripting)

### Méthode d'audit
- `grep -rn "echo " views/` → recherche d'echo direct sans `h()`
- `grep -rn "<?=" views/ | grep -v " h(" | grep -v " format_" | grep -v " url(" | grep -v " asset"` → recherche de balises `<?=` sans helper d'échappement

### Constats

| Variable d'origine | Helper utilisé | Risque |
|---|---|---|
| Champ BDD texte simple | `h(...)` (htmlspecialchars) | Nul |
| Champ markdown billet | `Markdown::convertir(...)` puis échappement préalable | Nul |
| URL générée | `url(...)` puis `h(...)` | Nul |
| Prix | `format_prix(...)` (génère du texte sans HTML) | Nul |
| Date | `format_date(...)` ou `format_date_courte(...)` | Nul |
| Attributs HTML dynamiques | `h(...)` dans `href="..."`, `value="..."` | Nul |

### CSP en place

```
default-src 'self';
script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com;
style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com;
img-src 'self' data:;
font-src 'self' data:;
connect-src 'self';
frame-ancestors 'none';
base-uri 'self';
form-action 'self'
```

### Risques résiduels
- ⚠️ **`unsafe-inline`** dans script-src et style-src : nécessaire pour Tailwind CDN. En prod sans CDN, le retirer.

---

## 4. CSRF (Cross-Site Request Forgery)

### Méthode d'audit
- `grep -rn "REQUEST_METHOD.*POST" public/` → recherche de tous les traitements POST
- Vérification que chaque POST appelle `Csrf::verifierRequete()`

### Inventaire des POST

| Fichier | CSRF vérifié | Détail |
|---|---|---|
| `public/login.php` | ✅ |
| `public/inscription.php` | ✅ |
| `public/profil.php` | ✅ |
| `public/compte_supprimer.php` | ✅ |
| `public/minichat.php` | ✅ |
| `public/minichat_supprimer.php` | ✅ |
| `public/commentaire_post.php` | ✅ |
| `public/commentaire_edit.php` | ✅ |
| `public/commentaire_delete.php` | ✅ |
| `public/like_toggle.php` | ✅ |
| `public/panier_add.php` | ✅ |
| `public/panier_update.php` | ✅ |
| `public/panier_remove.php` | ✅ |
| `public/paiement.php` | ✅ |
| `public/favori_toggle.php` | ✅ |
| `public/note_add.php` | ✅ |
| `public/admin/billet_form.php` | ✅ |
| `public/admin/billet_supprimer.php` | ✅ |
| `public/admin/article_form.php` | ✅ |
| `public/admin/article_supprimer.php` | ✅ |
| `public/admin/commande_statut.php` | ✅ |
| `public/admin/codes_promo.php` | ✅ |
| `public/admin/frais_port.php` | ✅ |
| `public/admin/corbeille.php` | ✅ |
| `public/admin/membre_action.php` | ✅ |
| `public/admin/securite.php` | ✅ |

### Risques résiduels
**Aucun.** Tous les traitements POST sont protégés.

---

## 5. Uploads de fichiers

### Fichier audité
- `classes/util/Upload.php`

### Constats

| Vérification | Statut | Détail |
|---|---|---|
| Taille max | ✅ | 5 MB par défaut |
| Extensions whitelist | ✅ | `.gif`, `.jpg`, `.jpeg` |
| MIME type réel (pas juste l'extension) | ✅ | `mime_content_type()` |
| Image valide (décodage réel) | ✅ | `getimagesize()` |
| Renommage aléatoire | ✅ | `bin2hex(random_bytes(8))` |
| Pas de path traversal | ✅ | `basename()` + dossier cible imposé |
| Permissions fichier | ✅ | `chmod 0644` |
| Dossier d'upload exécutable ? | ✅ | `.htaccess` bloque PHP, phtml, perl, python, sh, cgi (depuis étape 2) |

### À améliorer
Aucune amélioration nécessaire — le dossier `uploads/` est protégé par un `.htaccess` qui bloque tout script exécutable.

---

## 6. Contrôle d'accès

### Audit du pattern `Auth::requireAdmin()`

```
grep -l "Auth::requireAdmin" public/admin/*.php
```

Toutes les pages sous `public/admin/` commencent par cette vérification — confirmé fichier par fichier.

### Contrôles de propriété (un membre ne voit que ses données)

| Page | Vérification |
|---|---|
| `public/facture.php` | `id_membre` correspond OU `Auth::estAdmin()` |
| `public/commentaire_edit.php` | `id_membre` correspond (admin exclu pour intégrité du discours) |
| `public/commentaire_delete.php` | `id_membre` correspond OU `Auth::estAdmin()` |
| `public/paiement.php` | `Adresse::appartientAuMembre()` |
| `public/profil.php` (changement mdp) | mot de passe actuel vérifié |
| `public/admin/membre_action.php` | impossible d'agir sur son propre compte |

---

## 7. Gestion des sessions

### Configuration

```php
session.cookie_httponly  = 1
session.cookie_samesite  = Lax
session.use_only_cookies = 1
session.use_strict_mode  = 1
session.cookie_secure    = 1 (si HTTPS détecté)
```

### Régénération
- À la connexion : `session_regenerate_id(true)`
- À la déconnexion : destruction complète

---

## 8. RGPD

### Mesures techniques

| Exigence | Mise en œuvre |
|---|---|
| Droit à l'oubli (Art. 17) | `Membre::anonymiser()` — anonymisation au lieu de suppression |
| Données conservées | Factures, commentaires (avec affichage "Utilisateur supprimé") |
| Données supprimées | Login → random, email → random, nom/prénom → "supprimé/Utilisateur", mdp → random, avatar (fichier) |
| Trace d'anonymisation | `date_anonymisation` (NOT NULL après anonymisation) |
| Consentement explicite | Demande de mot de passe + saisie "SUPPRIMER" en clair |
| Minimisation | Purge automatique recommandée (page admin/sécurité) |

### Documentation
Une mention RGPD est affichée sur la page d'inscription et de suppression de compte.

---

## 9. Configuration

### `config/config.php`
- ✅ Non versionné (dans `.gitignore`)
- ✅ Template fourni : `config/config.example.php`
- ✅ Constantes par défaut sécurisées

### `.htaccess`
- ✅ Tous les dossiers sensibles ont un `.htaccess` qui bloque l'accès direct
- ✅ Routage propre dans `public/`

---

## Conclusion de l'audit

Le projet PDVWeb met en œuvre **les bonnes pratiques OWASP Top 10** sur tous les axes critiques. Les risques résiduels identifiés sont :

1. **`unsafe-inline` dans CSP** : nécessaire pour Tailwind CDN, à supprimer si on bascule sur Tailwind compilé en production.
2. **Tailwind via CDN** : la console émet un avertissement indiquant que ce mode est destiné au prototypage. Pour une mise en production, il faudrait compiler Tailwind localement via PostCSS ou le CLI. Sans impact fonctionnel ni de sécurité — purement informatif.
3. **Pas de 2FA** : amélioration possible pour un contexte plus sensible.

Ces points sont des **améliorations**, pas des failles. Le projet est **prêt pour une mise en production raisonnable** dans son contexte pédagogique.

---

*Rapport généré à l'étape 8 du projet (mai 2026).*
