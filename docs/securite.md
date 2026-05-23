# 🔒 Sécurité du projet PDVWeb

Document de référence pour le rendu pédagogique.

Ce document présente comment **chaque risque du OWASP Top 10 (2021)** est traité dans le projet PDVWeb.

OWASP (Open Web Application Security Project) est l'organisation de référence en sécurité applicative. Le Top 10 est leur classement des risques les plus critiques pour les applications web.

---

## OWASP A01:2021 — Broken Access Control

**Risque** : Un utilisateur accède à des ressources/actions auxquelles il n'a pas droit.

### Mesures appliquées

- **Contrôle systématique côté serveur** via `Auth::requireLogin()` et `Auth::requireAdmin()` au début de chaque page sensible
- **Pas de "security by URL obscurity"** : toute page admin vérifie le rôle, pas seulement l'apparition dans la navbar
- **Vérification de propriété** : un membre ne peut voir que ses propres factures (`facture.php`), modifier que ses propres commentaires
- **Anti-tampering** : les ID envoyés en POST sont systématiquement re-vérifiés
  - Exemple `Adresse::appartientAuMembre()` avant validation de commande
  - Exemple `FraisPort::trouverApplicable()` avant calcul du total
- **Admin ne peut pas se modifier lui-même** : protection dans `membre_action.php`

### Fichiers clés

- `classes/util/Auth.php` — Méthodes `requireLogin()`, `requireAdmin()`, `estConnecte()`, `estAdmin()`
- Toutes les pages sous `public/admin/` commencent par `Auth::requireAdmin()`

---

## OWASP A02:2021 — Cryptographic Failures

**Risque** : Stockage ou transmission insécurisée de données sensibles.

### Mesures appliquées

- **Mots de passe hashés avec bcrypt** (`PASSWORD_BCRYPT` par défaut dans `password_hash()`)
- **Pas de mots de passe en clair** stockés nulle part (ni en BDD, ni en log, ni en session)
- **Pas de mots de passe loggés** : `tentative_connexion` ne stocke que login + IP + succès
- **Tokens hashés** avant stockage en BDD (`Securite::hasherToken()` avec SHA-256)
- **Tokens CSRF cryptographiquement aléatoires** : `random_bytes(32)` → 64 caractères hex
- **Cookies sécurisés** :
  - `HttpOnly` → non accessible en JavaScript (anti-XSS)
  - `SameSite=Lax` → bloque l'envoi sur requêtes cross-site (anti-CSRF)
  - `Secure` activé automatiquement si HTTPS détecté

### Fichiers clés

- `classes/Membre.php` — `creer()`, `tenterConnexion()`, `changerMotPasse()`
- `classes/util/Csrf.php` — Génération et vérification CSRF
- `includes/bootstrap.php` — Configuration des cookies de session

---

## OWASP A03:2021 — Injection (SQL, XSS, etc.)

**Risque** : Injection de code malveillant via les entrées utilisateur.

### Mesures appliquées

#### SQL Injection
- **100% des requêtes utilisent PDO + prepared statements** avec paramètres bindés
- **Aucune concaténation de variables utilisateur dans une requête SQL** (audité fichier par fichier)
- `Db::pdo()` configure PDO avec `ATTR_EMULATE_PREPARES = false` pour des prepared statements réels
- ⚠️ Exception assumée : les `LIMIT $parPage OFFSET $offset` dans les paginations — les valeurs sont d'abord forcées en `int` via `(int)$opts['parPage']` et `max(1, ...)`, ce qui rend toute injection impossible

#### XSS (Cross-Site Scripting)
- **Helper `h($texte)` (htmlspecialchars)** appliqué systématiquement sur tout output venant de la BDD
- **CSP (Content-Security-Policy)** : limite les sources de scripts autorisées
- **Parser Markdown maison** (`classes/util/Markdown.php`) :
  - Encode TOUT en HTML d'abord, puis applique le markdown
  - Filtre les URLs `javascript:` et `data:` dans les liens

#### Command Injection
- **Pas d'appels `exec()`, `system()`, `shell_exec()`** dans le code

### Fichiers clés

- `classes/util/Db.php` — Configuration PDO sécurisée
- `includes/helpers.php` — Fonction `h()` utilisée partout
- `classes/util/Markdown.php` — Parser avec échappement préalable

---

## OWASP A04:2021 — Insecure Design

**Risque** : Erreurs de conception sécuritaire dès l'architecture.

### Mesures appliquées

- **Architecture en couches** : Modèle (`classes/`) ↔ Contrôleur (`public/`) ↔ Vue (`views/`)
- **Sécurité par défaut** : toutes les pages admin protégées, sessions sécurisées dès le bootstrap
- **Logique métier côté serveur** : pas de calcul de prix ou validation critique en JavaScript
- **Anti brute-force par login** : 5 échecs → blocage 15 min (`Membre::tenterConnexion`)
- **Anti brute-force par IP** : 10 échecs / 15 min depuis la même IP (`Securite::estRateLimited`)
- **CSRF systématique** sur tous les formulaires POST (création, modification, suppression)
- **Validation à plusieurs niveaux** : HTML5 + JS optionnel + PHP serveur (le seul qui compte)
- **Principe du moindre privilège** : un membre = ne voit que ses données, un admin = ne peut pas se rétrograder lui-même
- **Soft delete** sur les contenus (billets, commentaires) → restauration possible

### Fichiers clés

- `public/admin/membre_action.php` — Sécurités multi-niveaux sur changement de rôle/blocage
- `classes/Facture.php::creerDepuisPanier()` — Transaction atomique anti-corruption

---

## OWASP A05:2021 — Security Misconfiguration

**Risque** : Mauvaises configurations par défaut, erreurs verbeuses, etc.

### Mesures appliquées

- **DEV_MODE séparé** entre développement et production (`config/config.php`)
- **Affichage des erreurs PHP** désactivé en production (`display_errors = 0`)
- **Logs PHP redirigés** vers fichier en production (`log_errors = 1`)
- **Headers HTTP de sécurité** sur chaque page (`Securite::envoyerHeadersSecurite()`) :
  - Content-Security-Policy
  - X-Frame-Options: DENY (anti-clickjacking)
  - X-Content-Type-Options: nosniff
  - Referrer-Policy: strict-origin-when-cross-origin
  - Permissions-Policy (désactive caméra, micro, etc.)
- **Header X-Powered-By retiré** (cache la version PHP)
- **`.htaccess`** pour bloquer l'accès aux dossiers sensibles (`config/`, `classes/`, etc.)
- **`.gitignore`** : exclut `config/config.php` et les uploads de la version control
- **`config.example.php`** : modèle public, le vrai `config.php` n'est jamais commité

### Fichiers clés

- `classes/util/Securite.php` — `envoyerHeadersSecurite()`
- `config/config.example.php` — Template de configuration
- `.gitignore`

---

## OWASP A06:2021 — Vulnerable and Outdated Components

**Risque** : Bibliothèques tierces avec failles connues.

### Mesures appliquées

- **Stack minimaliste** : pas de framework ni de gestionnaire de paquets
- **Pas de dépendances Composer** = pas de packages tiers à mettre à jour
- **Tailwind via CDN** : versionné et servi par un CDN qui maintient la sécurité
- **PHP 8.2+ requis** (versions supportées par php.net)
- **PDO + MySQL natifs** : extensions du cœur PHP, pas de surcouche

### Note

Cette approche minimaliste est un choix pédagogique pour le TFM. Un projet professionnel utiliserait Composer + Dependabot/Renovate pour des mises à jour automatiques.

---

## OWASP A07:2021 — Identification and Authentication Failures

**Risque** : Mécanismes d'authentification faibles.

### Mesures appliquées

- **Hash bcrypt** des mots de passe (coût par défaut = 10, soit ~100ms par hash)
- **Anti brute-force double couche** :
  - Par login : 5 échecs → 15 min de blocage (`Membre::tenterConnexion`)
  - Par IP : 10 échecs / 15 min (`Securite::estRateLimited`)
- **Régénération de l'ID de session** au login (anti session-fixation) : `session_regenerate_id(true)`
- **Token CSRF régénéré** au login
- **Session strict mode** activé (`session.use_strict_mode = 1`)
- **Logout complet** : `session_unset()` + `session_destroy()` + cookie supprimé
- **Pas de "remember me" implémenté** par défaut (réduit la surface d'attaque)

### Fichiers clés

- `classes/Membre.php::tenterConnexion()`
- `classes/util/Auth.php::connecter()`, `deconnecter()`

---

## OWASP A08:2021 — Software and Data Integrity Failures

**Risque** : Mises à jour, dépendances ou données sans contrôle d'intégrité.

### Mesures appliquées

- **Pas d'auto-update** : code servi tel quel, modifications via Git seulement
- **Transactions atomiques** pour les opérations critiques :
  - Création de facture : `BEGIN ... INSERT facture / lignes / paiement / décrément stock / promo ... COMMIT/ROLLBACK`
  - Anonymisation RGPD : toutes les modifications sous transaction
- **Décrément de stock atomique** : `UPDATE article SET stock = stock - ? WHERE id = ? AND stock >= ?` (protection anti race-condition)
- **Snapshot des prix** dans `ligne_facture` (le prix au moment de l'achat est figé)

### Fichiers clés

- `classes/Facture.php::creerDepuisPanier()`
- `classes/Membre.php::anonymiser()`

---

## OWASP A09:2021 — Security Logging and Monitoring Failures

**Risque** : Pas d'audit → impossible de détecter ou enquêter sur un incident.

### Mesures appliquées

- **Table `audit_log`** : trace les actions sensibles avec JSON contextuel
  - Actions tracées : connexions, blocages, suppressions, anonymisations, changements de rôle, purges
  - Stocke : `id_membre`, `action`, `entite`, `id_entite`, `details` (JSON), `ip`, `user_agent`, `date_action`
- **Table `log_connexion`** : trace toutes les connexions réussies
- **Table `tentative_connexion`** : trace toutes les tentatives échouées
- **Page admin Audit log** : visualisation avec filtres (action, membre, période, entité)
- **Page admin Sécurité** : compteurs en temps réel des échecs récents

### Fichiers clés

- `classes/AuditLog.php` — Lecture et écriture
- `public/admin/audit.php` + `views/admin/audit.php` — Visualiseur

---

## OWASP A10:2021 — Server-Side Request Forgery (SSRF)

**Risque** : Le serveur fait des requêtes sortantes vers des URLs contrôlées par l'attaquant.

### Mesures appliquées

- **Pas de `file_get_contents($url)` sur des URLs externes** dans le code
- **Pas de `curl_exec()`** dans le code
- **Pas de webhook ni d'intégration externe** dans cette version
- **Uploads d'images vérifiés** :
  - Type MIME réel vérifié (pas juste l'extension)
  - `getimagesize()` confirme que c'est bien une image
  - Fichier renommé avec un nom aléatoire (`bin2hex(random_bytes())`)
  - Stocké dans un dossier dédié (`uploads/avatars/`, `uploads/articles/`)
  - `chmod 0644` (lecture seule pour les autres)

### Fichiers clés

- `classes/util/Upload.php` — Validation complète des uploads

---

## Synthèse

| OWASP | Risque | Statut |
|---|---|---|
| A01 | Broken Access Control | ✅ Protégé |
| A02 | Cryptographic Failures | ✅ Protégé |
| A03 | Injection | ✅ Protégé |
| A04 | Insecure Design | ✅ Protégé |
| A05 | Security Misconfiguration | ✅ Protégé |
| A06 | Vulnerable Components | ✅ Pas de dépendances vulnérables |
| A07 | Auth Failures | ✅ Protégé |
| A08 | Data Integrity | ✅ Protégé |
| A09 | Logging Failures | ✅ Audit complet |
| A10 | SSRF | ✅ Pas de vecteur exposé |

## Pour aller plus loin

- Audit détaillé : `docs/audit_securite.md`
- Documentation OWASP : https://owasp.org/Top10/

---

*Document mis à jour à l'étape 8 du projet (mai 2026).*
