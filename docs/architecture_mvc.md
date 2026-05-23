# 🏗️ Architecture MVC — PDVWeb

Documentation technique de l'architecture du projet.

---

## Sommaire

1. [Vue d'ensemble](#1-vue-densemble)
2. [Pattern MVC hybride](#2-pattern-mvc-hybride)
3. [Structure des dossiers](#3-structure-des-dossiers)
4. [Flux d'une requête HTTP](#4-flux-dune-requête-http)
5. [Conventions de nommage](#5-conventions-de-nommage)
6. [Autoloader et bootstrap](#6-autoloader-et-bootstrap)
7. [Couches métier (classes)](#7-couches-métier-classes)
8. [Vues (templates)](#8-vues-templates)
9. [JavaScript ES6](#9-javascript-es6)
10. [Inventaire complet](#10-inventaire-complet)

---

## 1. Vue d'ensemble

PDVWeb suit un **pattern MVC light hybride** :

- **M (Modèle)** : 26 classes PHP orientées objet dans `classes/`
- **V (Vue)** : ~48 fichiers PHP/HTML dans `views/`
- **C (Contrôleur)** : ~70 fichiers PHP dans `public/`

**Statistiques globales :**
- 154 fichiers PHP
- ~15 000 lignes de code
- 0 erreur de syntaxe

### Choix d'architecture

Le projet n'utilise pas de framework (Symfony, Laravel) car le **cahier des charges** demande de comprendre les mécaniques bas niveau (sessions, sécurité, routing).

L'approche **hybride procédural + POO** :
- **Contrôleurs procéduraux** (publics, simples, prévisibles)
- **Modèles POO** (méthodes statiques pour simplicité)
- **Vues HTML** (séparation présentation/logique)

Cette approche est proche de **Laravel** (sans le framework) ou de **CodeIgniter ancienne version**.

---

## 2. Pattern MVC hybride

### Schéma de communication

```
┌──────────────┐         ┌──────────────────┐         ┌──────────────┐
│              │  GET    │                  │  call   │              │
│  Navigateur  ├────────►│  Contrôleur      ├────────►│  Modèle      │
│              │         │  (public/*.php)  │         │  (classes/)  │
│              │  HTML   │                  │  data   │              │
│              │◄────────┤                  │◄────────┤              │
└──────────────┘         └────────┬─────────┘         └──────┬───────┘
                                  │ require                  │
                                  ▼                          │ PDO
                         ┌──────────────────┐                │
                         │  Vue             │                ▼
                         │  (views/*.php)   │         ┌──────────────┐
                         │                  │         │  MySQL       │
                         └──────────────────┘         │              │
                                                      └──────────────┘
```

### Exemple : "afficher la fiche d'un article"

#### 1. Le contrôleur `public/article.php`

```php
<?php
require_once __DIR__ . '/../includes/bootstrap.php';

// 1. Récupérer l'ID depuis la query string
$idArticle = (int)($_GET['id'] ?? 0);
if ($idArticle <= 0) {
    header('Location: ' . url('/catalogue.php'));
    exit;
}

// 2. Appeler le modèle pour récupérer les données
$article = Article::trouverParId($idArticle);
if (!$article) {
    Flash::erreur('Article introuvable.');
    header('Location: ' . url('/catalogue.php'));
    exit;
}

// 3. Récupérer données complémentaires
$statsNotes  = NoteArticle::statistiques($idArticle);
$tagsArticle = Article::tagsDe($idArticle);

// 4. Passer les variables à la vue
$titre = $article['nom'];
require_once VIEWS_PATH . '/catalogue/detail.php';
```

#### 2. Le modèle `classes/Article.php`

```php
class Article {
    public static function trouverParId(int $id): ?array {
        $req = Db::pdo()->prepare(
            "SELECT a.*, c.nom AS categorie_nom
             FROM article a
             INNER JOIN categorie c ON c.id_categorie = a.id_categorie
             WHERE a.id_article = ? AND a.dispo = 1"
        );
        $req->execute([$id]);
        return $req->fetch() ?: null;
    }
}
```

#### 3. La vue `views/catalogue/detail.php`

```php
<?php require_once INCLUDES_PATH . '/header.php'; ?>

<h1><?= h($article['nom']) ?></h1>
<p><?= h($article['categorie_nom']) ?></p>
<p class="text-xl font-bold"><?= format_prix($article['prix']) ?></p>

<?php if (!empty($tagsArticle)): ?>
    <div>
        <?php foreach ($tagsArticle as $tag): ?>
            <span>#<?= h($tag['nom']) ?></span>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
```

---

## 3. Structure des dossiers

```
pdvweb/
├── classes/                       # MODÈLES (26 classes)
│   ├── util/                      # Classes utilitaires
│   │   ├── Auth.php               # Authentification + droits
│   │   ├── Csrf.php               # Protection CSRF
│   │   ├── Db.php                 # Singleton PDO
│   │   ├── Flash.php              # Messages flash
│   │   ├── Helpers.php            # Fonctions h(), url(), etc.
│   │   └── Upload.php             # Upload sécurisé
│   ├── AuditLog.php               # Journal d'audit
│   ├── Article.php                # Articles + tags + comparateur
│   ├── Billet.php                 # Billets de blog
│   ├── Categorie.php              # Catégories activables
│   ├── CodePromo.php              # Codes promo
│   ├── Commentaire.php
│   ├── Facture.php                # Commandes + filtres
│   ├── Favori.php
│   ├── FraisPort.php              # Grilles tarifaires
│   ├── Membre.php                 # Membres + actions admin
│   ├── MessagePrive.php
│   ├── Minichat.php
│   ├── Newsletter.php
│   ├── NoteArticle.php
│   ├── Notification.php
│   ├── Panier.php                 # Stocké en session
│   ├── Parametre.php              # Paramètres applicatifs
│   ├── Permission.php
│   ├── RechercheLog.php
│   ├── Role.php
│   ├── Tag.php                    # CRUD complet
│   └── Token.php                  # Reset mdp / vérif email
│
├── config/                        # Configuration (hors public)
│   ├── config.example.php         # Template à dupliquer
│   └── config.php                 # ⚠️ Hors git (mots de passe BDD)
│
├── docs/                          # Documentation
│   ├── architecture_mvc.md        # Ce fichier
│   ├── audit_securite.md          # Conformité OWASP
│   ├── guide_git.md               # Procédure git
│   ├── justification_tables.md    # Pourquoi ces tables
│   ├── manuel_utilisateur.md      # Guide pratique
│   ├── schema_reference.md        # Référence colonnes
│   ├── securite.md                # Mesures techniques
│   └── captures/                  # Captures d'écran
│
├── includes/                      # Fragments réutilisables
│   ├── bootstrap.php              # Autoloader + init + session
│   ├── header.php                 # Nav + meta + skip link
│   ├── footer.php                 # Footer + badges sécurité
│   └── admin_header.php           # Bandeau admin
│
├── public/                        # CONTRÔLEURS (point d'entrée web)
│   ├── index.php                  # Accueil
│   ├── catalogue.php              # Liste articles avec filtres
│   ├── article.php                # Fiche article
│   ├── comparer.php               # Comparateur (Phase 3.3)
│   ├── panier.php                 # Voir panier
│   ├── panier_ajouter.php         # Action POST
│   ├── panier_update.php          # Action POST
│   ├── commande_valider.php       # Validation finale
│   ├── login.php / logout.php     # Auth
│   ├── inscription.php
│   ├── profil.php
│   ├── adresses.php / adresse_form.php
│   ├── favoris.php / favori_toggle.php
│   ├── messages.php / messages_nouveau.php / messages_thread.php
│   ├── notifications.php
│   ├── blog.php / billet.php
│   ├── minichat.php
│   ├── mentions_legales.php       # RGPD
│   ├── 404.php
│   ├── assets/                    # CSS, JS, images statiques
│   │   ├── css/style.css
│   │   ├── js/
│   │   └── img/
│   ├── uploads/                   # Fichiers uploadés
│   │   ├── articles/
│   │   ├── avatars/
│   │   └── billets/
│   └── admin/                     # ESPACE ADMIN (32 contrôleurs)
│       ├── index.php              # Dashboard
│       ├── membres.php / membre_detail.php
│       ├── membre_action.php      # Bloquer, promouvoir, anonymiser
│       ├── membre_form.php        # Modifier données
│       ├── articles.php / article_form.php / article_supprimer.php
│       ├── billets.php / billet_form.php / billet_supprimer.php
│       ├── categories.php / categorie_form.php / categorie_supprimer.php
│       ├── codes_promo.php / code_promo_form.php / code_promo_supprimer.php
│       ├── frais_port.php / frais_port_form.php / frais_port_supprimer.php
│       ├── tags.php / tag_form.php / tag_supprimer.php
│       ├── commandes.php / commande_statut.php / commandes_export.php
│       ├── stats_top.php / stats_connexion.php / stats_recherches.php
│       ├── audit.php
│       ├── securite.php / corbeille.php
│       └── parametres.php
│
├── sql/                           # Migrations BDD
│   ├── 00_install_complet.sql     # Script tout-en-un
│   ├── 01_schema.sql              # Schéma initial
│   ├── 02_seed.sql                # Données de démo
│   ├── 03_migration_soft_delete.sql
│   ├── 04_fix_anonymisation_order.sql
│   ├── 05_migration_mode_livraison.sql
│   ├── 06_migration_mp.sql
│   ├── 07_migration_pseudo_minichat.sql
│   ├── 08_migration_billet_resume_image.sql
│   ├── 09_migration_categorie_actif.sql
│   └── 10_migration_article_tag.sql
│
├── views/                         # VUES (~48 fichiers)
│   ├── accueil.php
│   ├── mentions_legales.php
│   ├── comparer.php               # Tableau comparatif
│   ├── auth/
│   │   ├── login.php
│   │   ├── inscription.php
│   │   ├── profil.php
│   │   ├── adresses.php / adresse_form.php
│   │   ├── favoris.php
│   │   ├── messages.php / messages_thread.php
│   │   ├── notifications.php
│   │   └── ...
│   ├── catalogue/
│   │   ├── liste.php              # Avec filtres avancés
│   │   └── detail.php
│   ├── blog/
│   │   ├── liste.php
│   │   └── detail.php
│   ├── panier/
│   │   └── voir.php
│   └── admin/                     # ~30 vues admin
│       ├── index.php              # Dashboard
│       ├── membres.php / membre_detail.php / membre_form.php
│       ├── articles.php / article_form.php
│       ├── billets.php / billet_form.php
│       ├── categories.php / categorie_form.php
│       ├── codes_promo.php / code_promo_form.php
│       ├── frais_port.php / frais_port_form.php
│       ├── tags.php / tag_form.php
│       ├── commandes.php
│       └── ...
│
├── .gitignore
├── README.md
└── install.bat                    # Installation Windows automatique
```

---

## 4. Flux d'une requête HTTP

### Exemple : "GET /pdvweb/public/catalogue.php?categorie=2"

```
1. APACHE
   - Reçoit la requête
   - Cherche le fichier catalogue.php dans public/
   - Délègue à PHP

2. PHP / bootstrap.php
   - Charge config/config.php
   - Connexion BDD (singleton Db::pdo())
   - Démarre la session (cookies HTTPOnly)
   - Définit l'autoloader (charge automatiquement les classes)
   - Charge les helpers (h(), url(), format_prix(), etc.)

3. catalogue.php (CONTRÔLEUR)
   - Récupère $_GET['categorie']
   - Valide (cast int, vérifie > 0)
   - Appelle Article::lister([...])

4. classes/Article.php (MODÈLE)
   - Prépare la requête SQL avec PDO
   - Exécute avec les paramètres
   - Retourne le résultat

5. catalogue.php (suite)
   - Reçoit les articles
   - Définit $titre = 'Catalogue'
   - require views/catalogue/liste.php

6. views/catalogue/liste.php (VUE)
   - require includes/header.php (nav + meta)
   - Boucle sur $articles et affiche le HTML
   - require includes/footer.php

7. PHP envoie la réponse HTML au navigateur
```

---

## 5. Conventions de nommage

### Fichiers

- **Contrôleurs** : `snake_case.php` (ex: `article_supprimer.php`)
- **Classes** : `PascalCase.php` (ex: `Article.php`, `CodePromo.php`)
- **Vues** : `snake_case.php` (ex: `messages_thread.php`)

### Classes PHP

- **Noms de classe** : `PascalCase` (ex: `class Article`)
- **Méthodes** : `camelCase` (ex: `trouverParId`, `lister`, `creer`)
- **Méthodes statiques** uniquement (pas d'instanciation pour les modèles)

### Variables

- **Noms** : `camelCase` (ex: `$idMembre`, `$articlesACompar`)
- **Tableaux associatifs** : reflètent les colonnes BDD (ex: `$article['nom']`)

### Routes / URLs

- **Pages publiques** : `/page.php`
- **Actions POST** : `/page_action.php` (ex: `/favori_toggle.php`)
- **Admin** : `/admin/page.php`
- **Form admin** : `/admin/entite_form.php?id=X`
- **Suppression admin** : `/admin/entite_supprimer.php`

### Tables MySQL

- **Noms** : `snake_case`, singulier (ex: `membre`, `code_promo`)
- **Tables de liaison** : `parent_enfant` (ex: `billet_tag`, `article_tag`)
- **Primary keys** : `id_<table>` (ex: `id_membre`, `id_article`)
- **Foreign keys** : portent le nom de la PK référencée

---

## 6. Autoloader et bootstrap

### `includes/bootstrap.php`

```php
<?php
// 1. Configuration
require_once __DIR__ . '/../config/config.php';

// 2. Constantes utiles
define('CLASSES_PATH', __DIR__ . '/../classes');
define('VIEWS_PATH',   __DIR__ . '/../views');
define('INCLUDES_PATH', __DIR__);
define('UPLOADS_PATH', __DIR__ . '/../public/uploads');

// 3. Autoloader (charge automatiquement les classes)
spl_autoload_register(function ($classe) {
    $candidates = [
        CLASSES_PATH . '/' . $classe . '.php',
        CLASSES_PATH . '/util/' . $classe . '.php',
    ];
    foreach ($candidates as $f) {
        if (file_exists($f)) {
            require_once $f;
            return;
        }
    }
});

// 4. Helpers (fonctions globales)
require_once CLASSES_PATH . '/util/Helpers.php';

// 5. Session sécurisée
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure'   => !empty($_SERVER['HTTPS']),
]);
```

### Pourquoi un autoloader ?

Sans autoloader, il faudrait `require_once` chaque classe au début de chaque fichier. L'autoloader détecte automatiquement quelle classe charger quand on l'utilise pour la première fois.

```php
// Sans autoloader
require_once 'classes/Article.php';
require_once 'classes/Categorie.php';
$article = Article::trouverParId(1);

// Avec autoloader
$article = Article::trouverParId(1);  // Article.php est chargé automatiquement
```

---

## 7. Couches métier (classes)

### Pattern utilisé

Toutes les classes du modèle utilisent **uniquement des méthodes statiques** :

```php
// Pas d'instanciation
$article = Article::trouverParId(5);  // ✓
$article->trouverParId(5);            // ✗
```

**Pourquoi ?**
- Plus simple (pas de constructeur, pas de propriétés)
- Pas d'état entre les appels
- Reflète bien les opérations CRUD sur BDD

### Liste des classes métier

| Classe | Rôle | Méthodes principales |
|---|---|---|
| `Article` | Articles + tags + comparateur | `lister`, `trouverParId`, `creer`, `modifier`, `supprimer`, `tagsDe`, `associerTags`, `pourComparaison` |
| `Billet` | Billets blog | `lister`, `trouverParId`, `creer`, `modifier`, `supprimer`, `tagsDe`, `restaurer` |
| `Categorie` | Catégories | `listerTous`, `listerActives`, `basculerActif` |
| `CodePromo` | Codes promo | CRUD + `appliquer`, `verifier` |
| `Commentaire` | Commentaires | CRUD + `restaurer` |
| `Facture` | Commandes | `creerDepuisPanier`, `listerToutes` (avec filtres + pagination), `changerStatut`, `chiffreAffaires` |
| `Favori` | Favoris | `ajouter`, `retirer`, `aFavori` |
| `FraisPort` | Grilles tarifaires | CRUD + `calculer`, `listerDisponibles` |
| `Membre` | Membres | CRUD + `tenterConnexion`, `anonymiser`, `mettreAJourAdmin`, `bloquer`, `changerRole` |
| `MessagePrive` | Messages privés | `envoyer`, `conversations`, `bloquer` |
| `Minichat` | Mini-chat | `derniers`, `poster`, `pseudoActif` |
| `Newsletter` | Newsletter | `inscrire`, `desinscrire` |
| `NoteArticle` | Notes/avis | `noter`, `statistiques`, `aAcheteArticle` |
| `Notification` | Notifications | `creer`, `marquerLue`, `nbNonLues` |
| `Panier` | Panier (session) | `ajouter`, `retirer`, `vider`, `detail` |
| `Parametre` | Paramètres app | `get`, `set`, `getAll` |
| `Tag` | Tags | CRUD + `codeExiste` |
| `Token` | Tokens auth | `creer`, `verifier`, `consommer` |
| `AuditLog` | Journal audit | `enregistrer`, `lister` |

### Helpers (fonctions globales)

```php
h($texte)              // htmlspecialchars (XSS protection)
url($path)             // Génère URL absolue
asset_article($img)    // URL d'une image article
asset_avatar($img)     // URL d'un avatar
format_prix($val)      // "10,99 €"
format_date_courte()   // "13/06/2026"
nom_membre($m)         // Gère le cas anonymisé
parametre($cle, $def)  // Récupère un paramètre
actif($url)            // CSS class si page courante
```

---

## 8. Vues (templates)

### Pattern

Les vues sont des fichiers PHP qui :
- N'ont pas d'accès direct à la BDD
- Reçoivent leurs variables du contrôleur
- Affichent du HTML avec `<?= h($var) ?>` pour échapper

### Anatomie d'une vue

```php
<?php
/**
 * views/catalogue/detail.php
 * Variables : $article, $tagsArticle, $statsNotes
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-4xl mx-auto">
    <h1><?= h($article['nom']) ?></h1>
    <!-- Reste du HTML -->
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
```

### Composants CSS réutilisables

`public/assets/css/style.css` définit :
- `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-danger`, `.btn-success`
- `.badge`, `.badge-success`, `.badge-warning`, etc.
- `.card`, `.card-hover`
- `.input-base`
- `.spinner`, `.skeleton`
- `.line-clamp-1/2/3`

---

## 9. JavaScript ES6

### Fichiers JS

```
public/assets/js/
├── confirm.js         # data-confirm="..."
├── csrf.js            # Auto-rafraîchissement des tokens
├── search.js          # Recherche temps réel
├── upload-preview.js  # Aperçu image avant upload
├── minichat.js        # Refresh mini-chat
└── form-validate.js   # Validation HTML5 améliorée
```

### Exemple : `confirm.js`

```javascript
// Confirmation automatique sur tout bouton avec data-confirm="..."
document.addEventListener('submit', (e) => {
    const btn = e.submitter;
    if (!btn?.dataset.confirm) return;
    if (!confirm(btn.dataset.confirm)) {
        e.preventDefault();
    }
});
```

Utilisation dans le HTML :

```html
<button type="submit" data-confirm="Vraiment supprimer ?">
    Supprimer
</button>
```

---

## 10. Inventaire complet

### Stats par dossier

| Dossier | Fichiers PHP | Description |
|---|---|---|
| `classes/` | 26 | Modèles + utilitaires |
| `public/` | 35 | Contrôleurs publics |
| `public/admin/` | 32 | Contrôleurs admin |
| `views/` | 5 | Vues racines (accueil, mentions, comparer, etc.) |
| `views/auth/` | ~15 | Vues membre |
| `views/catalogue/` | 2 | Liste + détail |
| `views/blog/` | 2 | Liste + détail |
| `views/panier/` | 1 | Voir panier |
| `views/admin/` | ~30 | Vues admin |
| `includes/` | 4 | bootstrap, header, footer, admin_header |
| **TOTAL** | **~154** | **PHP** |

### Tables BDD

| Catégorie | Tables |
|---|---|
| Membres & sécurité | `membre`, `adresse`, `role`, `permission`, `role_permission`, `membre_role`, `token`, `tentative_connexion`, `log_connexion`, `audit_log`, `consentement_rgpd` |
| Catalogue | `categorie`, `article`, `article_tag`, `vue_article`, `note_article` |
| Vente | `statut_commande`, `achat_facture`, `ligne_facture`, `paiement`, `frais_port`, `code_promo`, `code_promo_utilisation` |
| Engagement | `favori`, `newsletter_abonne`, `like_contenu`, `recherche_log` |
| Contenu | `billet`, `commentaire`, `tag`, `billet_tag` |
| Communication | `minichat`, `message_prive`, `notification` |
| Système | `parametre` |

### Migrations SQL

10 migrations incrémentales (voir README).

---

## Points forts de cette architecture

1. **Pas de framework** → comprendre les mécaniques (sessions, sécurité, routing)
2. **MVC respecté** → séparation claire des responsabilités
3. **PDO 100%** → aucune injection SQL possible
4. **Audit log** → traçabilité totale (conformité RGPD)
5. **Autoloader** → pas de require_once partout
6. **Helpers globaux** → code plus lisible
7. **Soft-delete** → corbeille pour billets/commentaires
8. **Migrations versionnées** → évolution du schéma reproductible
9. **Pas de couplage fort** → chaque classe est testable individuellement
10. **Accessibilité** → skip link, ARIA, prefers-reduced-motion

---

**Architecture documentée le 23 mai 2026 — Mise à jour avec Phase 3.**
