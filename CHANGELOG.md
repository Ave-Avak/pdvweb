# Changelog

## [Non publié]

### À venir
- Étape 3 : authentification (inscription, connexion, déconnexion, profil)
- Étape 4 : mini-chat
- Étape 5 : blog et commentaires
- Étape 6 : panier et commandes
- Étape 7 : interface d'administration

---

## [0.2.0] — Étape 2 : Socle commun

### Ajouté
- `config/config.php` (+ `config.example.php`) : paramètres centraux
- **6 classes utilitaires** dans `classes/util/` :
  - `Db.php` : singleton PDO avec mode exception, fetch associatif, requêtes préparées vraies
  - `Auth.php` : login/logout, requireLogin, requireAdmin, gestion session
  - `Csrf.php` : jetons anti-CSRF avec `hash_equals`
  - `Flash.php` : messages flash typés (succès/erreur/info/avertissement)
  - `Upload.php` : upload sécurisé (extension + MIME + image valide + renommage)
  - `Securite.php` : anti brute-force, IP, génération de tokens
- `includes/bootstrap.php` : amorçage (config + session sécurisée + autoload PSR-4 + helpers)
- `includes/helpers.php` : 10 fonctions globales (`h()`, `url()`, `asset()`, `format_date()`, `format_prix()`, `tronquer()`, etc.)
- `includes/header.php` : navbar Tailwind dynamique (connecté/non), avatar, panier, notifications
- `includes/footer.php` : pied de page complet
- `public/index.php` : contrôleur de la page d'accueil
- `views/accueil.php` : vue de l'accueil (hero, fonctionnalités, mini-formulaire login)
- **Sécurité Apache** : 3 fichiers `.htaccess` (racine bloquée, public ouvert, uploads sans exécution PHP)
- `public/assets/` : CSS perso, JS minimaliste, icônes SVG (avatar défaut, article défaut, favicon)
- Mode session sécurisé : HTTPOnly, SameSite=Lax, use_only_cookies

### Sécurité
- Toutes les classes utilisent des requêtes préparées
- Jeton CSRF systématique dans les formulaires
- `htmlspecialchars` via fonction `h()` partout dans les vues
- `session_regenerate_id(true)` après login (anti session fixation)
- Mode dégradé : la page d'accueil s'affiche même si la BDD est indisponible
- Cookies de session HTTPOnly + SameSite=Lax
- Headers HTTP de sécurité (X-Content-Type-Options, X-Frame-Options, Referrer-Policy)

### Testé
- 15 fichiers PHP : syntaxe validée (PHP 8.3)
- 13 tests d'intégration verts (autoload + classes + helpers + CSRF)
- Page d'accueil rendue : 17,7 Ko de HTML, tous éléments présents

---

## [0.1.0] — Étape 1 : Architecture et schéma BDD

### Ajouté
- Architecture du projet : approche hybride PHP procédural + MVC light + classes utilitaires
- Schéma de base de données : **34 tables** organisées en 8 groupes fonctionnels
- Conformité au schéma initial du professeur (7 tables imposées) avec enrichissements
- Données de test (3 catégories, 18 articles, 1 admin + 3 membres, billets, commentaires, etc.)
- Script utilitaire `generer_hashes.php` pour les mots de passe bcrypt
- Documentation : `README.md`, `justification_tables.md`, `guide_git.md`
- Licence MIT
- `.gitignore` pour les fichiers sensibles
