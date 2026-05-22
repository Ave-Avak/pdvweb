# Changelog

## [Non publié]

### À venir
- Étape 5 : blog et commentaires
- Étape 6 : panier et commandes
- Étape 7 : interface d'administration

---

## [0.4.0] — Étape 4 : Mini-chat

### Ajouté
- **`classes/Minichat.php`** : modèle métier
  - `listerDerniers(int)` : récupère les N derniers messages avec jointure membre
  - `creer(int, string)` : crée un message
  - `supprimer(int)` : supprime un message
  - `trouverParId(int)` : recherche par ID (pour vérif des droits avant suppression)
  - `dernierMessageDe(int)` : pour l'anti-spam
  - `longueurMax()` / `nbAffiches()` : lecture des paramètres applicatifs
- **`public/minichat.php`** : contrôleur d'affichage + envoi
  - Réservé aux UM (`Auth::requireLogin()`)
  - Membres bloqués ne peuvent pas poster
  - Anti-spam : refuse 2 messages identiques d'affilée
  - Pattern POST-Redirect-GET après envoi
- **`public/minichat_supprimer.php`** : contrôleur de suppression
  - Méthode POST uniquement (jamais GET)
  - CSRF obligatoire
  - Droits : auteur du message OU admin
- **`views/minichat.php`** : interface complète
  - 10 derniers messages dans une zone scrollable
  - Avatar + nom + badge "Admin" + date relative
  - Compteur de caractères en direct (passe en orange à 90%)
  - Auto-scroll au dernier message
  - Bouton suppression visible au survol (icône poubelle)
  - État vide géré (illustration + message d'invitation)

### Sécurité
- CSRF sur l'envoi et la suppression
- Suppression : vérification des droits avant exécution
- Anti-spam basique (refus du doublon consécutif)
- `nl2br(h(...))` pour autoriser les retours à la ligne sans risquer le XSS
- Limite côté serveur ET côté client (HTML maxlength + check PHP)
- Les UM bloqués ne peuvent plus poster

### UX
- Affichage chronologique (ancien → récent) avec auto-scroll en bas
- Compteur de caractères en temps réel
- Indication visuelle du contenu admin (badge violet)
- Boutons de suppression visibles uniquement au survol (UI propre)

---

## [0.3.0] — Étape 3 : Authentification complète

### Ajouté
- **`classes/Membre.php`** : première classe métier du projet
  - Recherche : `trouverParId`, `trouverParLogin`, `trouverParEmail`, `listerTous`
  - Création : `loginExiste`, `emailExiste`, `creer` (avec hash bcrypt + rôle par défaut)
  - Connexion : `tenterConnexion` (anti brute-force intégré)
  - Mise à jour : `mettreAJour`, `changerMotPasse`, `mettreAJourAvatar`, `verifierMotPasse`
  - Administration : `bloquer`, `nbConnexions`
  - Audit log automatique sur les actions sensibles
- **Inscription** : `public/inscription.php` + `views/auth/inscription.php`
  - Validation côté serveur (longueur, format email, force du mdp)
  - Vérification unicité login + email
  - Upload d'avatar optionnel (aperçu JS avant validation)
  - Connexion automatique après inscription
- **Connexion** : `public/login.php` + `views/auth/login.php`
  - Anti brute-force (5 essais / 15 min)
  - Comptes de test affichés en mode dev
  - Redirection vers la page demandée avant login
- **Déconnexion** : `public/logout.php`
- **Profil** : `public/profil.php` + `views/auth/profil.php`
  - 3 formulaires distincts (infos, mot de passe, avatar)
  - Upload + suppression d'avatar
  - Carte récap des infos de compte (inscription, dernière connexion, statut)

### Sécurité
- CSRF sur tous les formulaires (5 formulaires au total : inscription, login, profil×3)
- `password_hash` / `password_verify` (bcrypt)
- `session_regenerate_id(true)` après login
- Upload sécurisé : extensions + MIME réel + image valide + renommage aléatoire
- Anti brute-force avec blocage temporaire
- Audit log automatique des inscriptions et connexions
- Validation REGEX du login (`[a-zA-Z0-9_-]{3,50}`)
- Vérification du mot de passe actuel avant tout changement
- Rollback du fichier uploadé en cas d'erreur BDD

### Testé
- 23 fichiers PHP : syntaxe validée
- Classe Membre : 5 méthodes statiques testées
- Pages inscription/login : rendu HTML complet (15k + 10k octets)
- Redirection automatique du profil pour les UNM

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
