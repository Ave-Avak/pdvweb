# 🏗️ Architecture MVC du projet PDVWeb

Ce document détaille l'architecture **Modèle - Vue - Contrôleur (MVC)** mise en œuvre dans le projet, conformément à l'exigence du cahier des charges :

> *« L'application devra être développée selon l'architecture MVC »*

---

## Vue d'ensemble

```
┌─────────────────────────────────────────────────────────┐
│                    NAVIGATEUR (client)                   │
│                                                          │
│   HTML / CSS Tailwind / JS POO (main.js — classes ES6)  │
└──────────────────┬──────────────────────────────────────┘
                   │
                   │ Requête HTTP (GET/POST)
                   ▼
┌─────────────────────────────────────────────────────────┐
│                     /public/ — CONTRÔLEURS              │
│  Point d'entrée HTTP. Reçoit les requêtes, valide,      │
│  appelle les modèles, choisit la vue à rendre.          │
│                                                          │
│  Ex: public/inscription.php, public/admin/membres.php   │
└──────────┬───────────────────────────────┬──────────────┘
           │                               │
           │ Lit/Écrit                     │ Choisit
           ▼                               ▼
┌────────────────────────┐    ┌───────────────────────────┐
│   /classes/ — MODÈLES  │    │     /views/ — VUES        │
│                        │    │                           │
│  Logique métier +      │    │  Pages HTML/PHP qui       │
│  accès BDD (PDO).      │    │  affichent les données.   │
│                        │    │  Aucune logique métier.   │
│  Ex: Membre, Article,  │    │  Ex: views/auth/login.php │
│      Facture, Panier   │    │                           │
└───────────┬────────────┘    └───────────────────────────┘
            │
            │ Requêtes préparées
            ▼
┌─────────────────────────────────────────────────────────┐
│                  MySQL — BASE DE DONNÉES                 │
│                       35 tables                          │
└─────────────────────────────────────────────────────────┘
```

---

## Les trois couches en détail

### 1. 📦 Le Modèle (`classes/`)

Le **modèle** encapsule **toute la logique métier et l'accès aux données**.

**Caractéristiques** :
- Classes PHP avec méthodes statiques (style cohérent dans tout le projet)
- Chaque classe correspond à une entité métier ou un service technique
- Aucune connaissance de HTTP, HTML ou affichage
- Toutes les requêtes SQL passent par PDO + prepared statements
- Code commenté en PHPDoc

**Exemples de modèles métier** :
| Classe | Rôle |
|---|---|
| `Membre` | Inscription, connexion, anonymisation RGPD |
| `Article` | Catalogue produit, recherche, stock |
| `Panier` | Gestion du panier en session |
| `Facture` | Création de commandes (transactions atomiques) |
| `Billet` / `Commentaire` | Contenu éditorial |
| `Minichat` | Mini-chat communautaire |
| `MessagePrive` | Messagerie privée entre membres |
| `Notification` | Alertes utilisateurs |

**Exemples de modèles techniques** (`classes/util/`) :
| Classe | Rôle |
|---|---|
| `Db` | Connexion PDO singleton |
| `Auth` | Sessions et authentification |
| `Csrf` | Protection anti-CSRF |
| `Securite` | Headers HTTP, rate limiting, purge |
| `Upload` | Validation et stockage des images |
| `Markdown` | Parser maison anti-XSS |

### 2. 🎬 Le Contrôleur (`public/`)

Le **contrôleur** est le **chef d'orchestre** : il reçoit la requête HTTP, valide les entrées, appelle les modèles, et choisit la vue à afficher.

**Caractéristiques** :
- Un fichier `.php` par action utilisateur
- Inclut systématiquement `bootstrap.php` qui charge tout (sessions, autoload, helpers)
- Vérifie les droits d'accès (`Auth::requireLogin()`, `Auth::requireAdmin()`)
- Vérifie le token CSRF pour tous les POST
- Termine par un `require` de la vue ou un `header('Location:')`
- Aucune génération de HTML directe (juste des `$variables` passées à la vue)

**Anatomie type d'un contrôleur** :

```php
<?php
require_once __DIR__ . '/../includes/bootstrap.php';

// 1. Contrôle d'accès
Auth::requireLogin();

// 2. Récupération paramètres
$idArticle = (int)($_GET['id'] ?? 0);

// 3. Si POST : validation + appel du modèle
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifierRequete()) { /* refuse */ }
    // ... validation ...
    Article::modifier($idArticle, $donnees);
    header('Location: /catalogue.php');
    exit;
}

// 4. Préparer les données pour la vue
$article = Article::trouverParId($idArticle);

// 5. Afficher la vue
$titre = 'Article : ' . $article['nom'];
require_once VIEWS_PATH . '/catalogue/detail.php';
```

### 3. 🖼️ La Vue (`views/`)

La **vue** est responsable **uniquement de l'affichage**. Elle reçoit des variables PHP du contrôleur et produit le HTML final.

**Caractéristiques** :
- Pages PHP qui contiennent principalement du HTML avec quelques `<?= h($variable) ?>`
- Aucune requête SQL directe
- Aucune logique métier (juste des conditions d'affichage)
- Échappement systématique via `h()` (htmlspecialchars)
- Inclusion du header/footer commun via `require_once`

**Anatomie type d'une vue** :

```php
<?php require_once INCLUDES_PATH . '/header.php'; ?>

<h1><?= h($article['nom']) ?></h1>
<p class="text-gray-600"><?= h($article['description']) ?></p>
<p class="text-2xl"><?= format_prix($article['prix']) ?></p>

<?php if (Auth::estConnecte()): ?>
    <form method="post" action="<?= url('/panier_add.php') ?>">
        <?= Csrf::champ() ?>
        <input type="hidden" name="id_article" value="<?= (int)$article['id_article'] ?>">
        <button type="submit">Ajouter au panier</button>
    </form>
<?php endif; ?>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
```

---

## Helpers transversaux (`includes/`)

Certains éléments ne sont ni modèle, ni contrôleur, ni vue : ce sont des **utilitaires transversaux** :

| Fichier | Rôle |
|---|---|
| `bootstrap.php` | Charge config, autoload des classes, démarre la session, applique les headers de sécurité |
| `helpers.php` | Fonctions utilitaires (`h()`, `url()`, `format_date()`, `format_prix()`, etc.) |
| `header.php` | En-tête HTML commun + navbar (inclus par chaque vue) |
| `footer.php` | Pied de page commun + chargement du JS |

---

## Exemple complet : "Modifier un article"

Suivons le flux complet d'une action utilisateur.

### 1. L'utilisateur clique sur "Modifier" → URL appelée :
```
GET /admin/article_form.php?id=42
```

### 2. Le contrôleur (`public/admin/article_form.php`) traite :
```php
Auth::requireAdmin();                               // Sécurité
$idArticle = (int)($_GET['id'] ?? 0);              // Lecture param
$article = Article::trouverParId($idArticle);      // Appel modèle
require_once VIEWS_PATH . '/admin/article_form.php'; // Choix vue
```

### 3. Le modèle (`classes/Article.php`) interroge la BDD :
```php
public static function trouverParId(int $id): ?array {
    $req = Db::pdo()->prepare("SELECT * FROM article WHERE id_article = ?");
    $req->execute([$id]);
    return $req->fetch() ?: null;
}
```

### 4. La vue (`views/admin/article_form.php`) affiche le formulaire :
```html
<form method="post">
    <input type="text" name="nom" value="<?= h($article['nom']) ?>">
    ...
</form>
```

### 5. L'utilisateur soumet → POST sur le même contrôleur :
```
POST /admin/article_form.php?id=42
```

### 6. Le contrôleur valide et appelle le modèle :
```php
if (!Csrf::verifierRequete()) { /* refuse */ }
// ... validation ...
Article::modifier($idArticle, $donnees);            // Appel modèle
header('Location: /admin/articles.php');            // Redirection
```

### 7. Le modèle exécute l'UPDATE :
```php
public static function modifier(int $id, array $donnees): bool {
    $req = Db::pdo()->prepare(
        "UPDATE article SET nom = ?, prix = ?, stock = ? WHERE id_article = ?"
    );
    return $req->execute([$donnees['nom'], $donnees['prix'], $donnees['stock'], $id]);
}
```

**Chaque responsabilité est isolée** : c'est exactement ce qu'on attend du MVC.

---

## JavaScript côté client : POO également

Le fichier `public/assets/js/main.js` utilise lui aussi l'**approche orientée objet** (classes ES6) :

```javascript
class FlashMessageManager { init() { /* ... */ } }
class ConfirmationManager { /* ... */ }
class DropdownMenuManager { /* ... */ }
class CharacterCounter { /* ... */ }
class AvatarPreview { /* ... */ }
class PDVWebApp { /* orchestre les managers */ }

const app = new PDVWebApp();
app.demarrer();
```

Chaque manager a une responsabilité unique (Single Responsibility Principle).

---

## Pourquoi MVC ?

| Bénéfice | Comment c'est concrétisé ici |
|---|---|
| **Séparation des responsabilités** | Modèles SQL, contrôleurs HTTP, vues HTML — chacun son rôle |
| **Testabilité** | Les méthodes statiques des modèles peuvent être appelées indépendamment de toute requête HTTP |
| **Réutilisabilité** | `Membre::trouverParId()` est appelée depuis 20+ contrôleurs différents |
| **Maintenabilité** | Pour modifier une règle métier, on touche au modèle uniquement, pas aux vues |
| **Sécurité** | Le contrôleur centralise les contrôles (CSRF, Auth, validation) avant tout accès au modèle |

---

## Conclusion

L'architecture MVC du projet PDVWeb respecte les principes fondamentaux :

✅ Les **modèles** (26 classes) encapsulent la logique métier et l'accès BDD
✅ Les **contrôleurs** (61 fichiers dans `public/`) orchestrent les requêtes
✅ Les **vues** (41 fichiers dans `views/`) ne font que de l'affichage
✅ Le **JavaScript** côté client utilise l'approche **orientée objet** (ES6 classes)

Cette architecture rend le code **lisible**, **maintenable**, **testable** et **sécurisé**.

---

*Document à jour à l'étape finale du projet (mai 2026).*
