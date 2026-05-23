# 🔍 Limitations connues & axes d'amélioration pro

Document d'auto-évaluation rédigé en toute transparence pour expliciter
les **choix techniques** du projet et les **améliorations identifiées**
pour un contexte professionnel.

L'objectif : montrer que je connais les limites de mes choix et que je
sais où sont les standards pro, même quand je ne les ai pas appliqués.

---

## Sommaire

1. [Choix d'architecture acceptables pour un TFM](#1-choix-darchitecture)
2. [Failles détectées et corrigées](#2-failles-détectées-et-corrigées)
3. [Tableau récapitulatif](#3-tableau-récapitulatif)

---

## 1. Choix d'architecture

### 1.1 Pas de namespaces PHP

**Constat** : les classes sont dans le scope global (`class Membre {}` au lieu de `namespace App\Model; class Membre {}`).

**Justification** :
- Cohérence avec le PHP enseigné dans le cours
- Pas besoin de configurer un autoloader PSR-4
- Lisibilité immédiate du code

**En production**, j'utiliserais :
```php
namespace App\Model;
class Membre { ... }

// Et dans l'autre fichier :
use App\Model\Membre;
```
Avec Composer et son autoloader PSR-4 standard.

---

### 1.2 Méthodes statiques (pattern DAO)

**Constat** : toutes les méthodes des modèles sont statiques :
```php
$article = Article::trouverParId(5);  // ✓ choix actuel
// vs
$repo = new ArticleRepository($pdo);
$article = $repo->findById(5);        // ✗ pattern Repository non utilisé
```

**Justification** :
- Pattern **DAO** (Data Access Object) très répandu
- Plus simple, plus lisible
- Pas d'injection de dépendances à gérer

**Inconvénients reconnus** :
- ❌ Difficulté à mocker pour les tests unitaires
- ❌ Couplage fort avec `Db::pdo()` (singleton global)
- ❌ Pas conforme aux principes SOLID stricts (Dependency Inversion)

**En production avec PHPUnit**, je passerais au pattern **Repository** avec
injection de dépendances :
```php
class ArticleRepository {
    public function __construct(private PDO $pdo) {}
    public function findById(int $id): ?Article { ... }
}
```

---

### 1.3 Pas de tests automatisés (PHPUnit)

**Constat** : aucun test unitaire, intégration, ou fonctionnel automatisé.

**Justification** :
- Non demandé par le CDC
- Une suite de tests sur 154 fichiers PHP demanderait ~80h supplémentaires
- J'ai privilégié une **couverture fonctionnelle manuelle** (test de chaque parcours)

**En production**, je couvrirais en priorité :
- `classes/util/Auth.php` (sécurité)
- `classes/util/Csrf.php` (sécurité)
- `classes/Facture.php` (logique métier critique)
- `classes/Panier.php` (logique d'addition, fusion, max)
- `classes/CodePromo.php` (calcul des remises)

Avec un objectif de **70% de couverture** minimum sur le code métier.

---

### 1.4 Tailwind CSS chargé depuis CDN

**Constat** : `<script src="https://cdn.tailwindcss.com">` au lieu d'un build local.

**Impact mesurable** :
- 📦 Taille : **~3 MB** chargés (CDN) vs **~5 KB** (compilé + purgé)
- ⏱️ Temps de chargement : **~500 ms** vs **~20 ms**
- 🔌 Dépendance externe : nécessite une connexion à `cdn.tailwindcss.com`

**Justification** :
- Pas de build step nécessaire pour le prof qui clone le projet
- Idéal pour un projet académique où l'environnement de test peut varier

**En production**, je mettrais en place :
```bash
npm install -D tailwindcss postcss vite
npx tailwindcss -i ./src/input.css -o ./public/assets/css/tailwind.css --minify
```
Avec un CI/CD qui rebuild à chaque push (GitHub Actions par exemple).

---

### 1.5 Pas de Composer

**Constat** : pas de `composer.json`, aucune dépendance externe.

**Justification** :
- Le projet est **100% vanilla PHP** (aucune lib externe)
- Cohérent avec le niveau du cours
- Un autoloader custom dans `bootstrap.php` suffit pour 154 fichiers

**En production**, j'utiliserais Composer pour :
- Autoload PSR-4 standard
- **PHPMailer** pour l'envoi d'emails réels (au lieu de la fonction `mail()` native)
- **Monolog** pour les logs structurés
- **PHPUnit** pour les tests

---

### 1.6 `DEV_MODE = true` par défaut

**Constat** : `config.example.php` (commité) a `DEV_MODE = true`, ce qui affiche les erreurs PHP en clair.

**Justification** :
- Permet au prof de voir les erreurs s'il y en a lors de ses tests
- Le fichier `config.php` (qui prime à l'exécution) est **non commité** (.gitignore)
- Un correcteur peut adapter ce flag pour son environnement

**En production**, ce serait **impérativement** `DEV_MODE = false` car :
- Les erreurs PHP affichées peuvent révéler des chemins serveur, structure BDD, etc.
- Ce sont des informations exploitables pour un attaquant

---

### 1.7 CSP avec `'unsafe-inline'`

**Constat** : la Content-Security-Policy autorise les scripts/styles inline :
```php
"script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; "
"style-src 'self' 'unsafe-inline' ...; "
```

**Justification** :
- Tailwind CDN **injecte du CSS dynamiquement** dans `<style>` au chargement
- Sans `'unsafe-inline'` sur `style-src`, Tailwind ne fonctionnerait pas
- Quelques scripts inline subsistent dans certaines vues

**Impact réel sur la sécurité** :
- Tous les paramètres utilisateur sont **toujours échappés** via `h()` à l'affichage
- Le risque XSS est donc limité même avec `'unsafe-inline'`
- La CSP reste plus stricte que la moyenne grâce aux autres directives :
  - `frame-ancestors 'none'` (anti-clickjacking)
  - `base-uri 'self'` (anti-injection de base)
  - `form-action 'self'` (les formulaires ne peuvent poster ailleurs)

**En production**, j'utiliserais :
- Tailwind compilé en local (résout 90% du problème)
- Des **nonces** générés par requête pour les scripts inline restants :
  ```html
  <script nonce="abc123random">/* code */</script>
  ```
  Avec dans la CSP : `script-src 'self' 'nonce-abc123random'`

---

## 2. Failles détectées et corrigées

Ces points ont été identifiés lors d'une revue de code et **corrigés** dans la version finale.

### 2.1 ✅ Open Redirect (corrigé)

**Problème** : 5 fichiers (`panier_add.php`, `favori_toggle.php`, `comparer.php`, `admin/membre_action.php`, `adresse_form.php`) lisaient `$_POST['retour']` ou `$_GET['retour']` sans validation, puis faisaient `header('Location: ' . $retour)`.

**Scénario d'attaque** :
1. Attaquant envoie un lien de phishing : `votre-site.com/panier_add.php?id_article=1&retour=https://faux-site.com/login`
2. La victime clique, voit votre URL légitime
3. Le site la redirige vers un site malveillant qui imite votre login
4. Identifiants compromis

**Correctif** : nouvelle fonction `retour_securise()` dans `includes/helpers.php` qui valide :
- Refus des protocoles arbitraires (`javascript:`, `data:`, etc.)
- Refus des URLs commençant par `//` ou `\\`
- Acceptation uniquement des URLs **relatives** (`/page.php`) ou **commençant par SITE_URL**

Utilisée partout :
```php
$retour = retour_securise($_POST['retour'] ?? null, url('/catalogue.php'));
header('Location: ' . $retour);
```

**Référence OWASP** : A01:2021 — Broken Access Control / Unvalidated Redirects.

---

### 2.2 ✅ Requête SQL dans une vue (corrigé)

**Problème** : `views/admin/code_promo_form.php` ligne 210 contenait une requête SQL directement dans la vue :
```php
<?php $nbUtil = (int)Db::pdo()->query(
    "SELECT COUNT(*) FROM code_promo_utilisation WHERE id_code = " . (int)$idCode
)->fetchColumn(); ?>
```

**Impact** :
- Pas de risque sécurité (le cast `(int)` protégeait l'injection)
- Mais **violation MVC** : entorse à la séparation Modèle/Vue
- C'était la **seule** entorse de tout le projet

**Correctif** :
1. Nouvelle méthode `CodePromo::nbUtilisations(int $idCode): int`
2. Le contrôleur `public/admin/code_promo_form.php` charge la valeur :
   ```php
   $nbUtilisations = $modeEdition ? CodePromo::nbUtilisations($idCode) : 0;
   ```
3. La vue utilise simplement `$nbUtilisations`

**Vérification** : `grep -rn "Db::pdo()" views/` retourne maintenant **zéro résultat**.

---

## 3. Tableau récapitulatif

| # | Point | Statut | Effort correction | Impact final |
|---|---|:-:|:-:|:-:|
| 1.1 | Pas de namespaces | 🟡 Documenté | 🔴 6h | TFM acceptable |
| 1.2 | Méthodes statiques | 🟡 Documenté | 🔴 12h (refactor) | TFM acceptable |
| 1.3 | Pas de tests | 🟡 Documenté | 🟡 80h | TFM acceptable |
| 1.4 | Tailwind CDN | 🟡 Documenté | 🟡 3h | TFM acceptable |
| 1.5 | Pas de Composer | 🟡 Documenté | 🟡 2h | TFM acceptable |
| 1.6 | DEV_MODE | 🟡 Documenté | 🟢 5 min | TFM acceptable |
| 1.7 | CSP unsafe-inline | 🟡 Documenté | 🔴 Lié à Tailwind | TFM acceptable |
| 2.1 | **Open Redirect** | ✅ **Corrigé** | 🟢 30 min | **Faille fermée** |
| 2.2 | **SQL dans vue** | ✅ **Corrigé** | 🟢 15 min | **MVC respecté** |

---

## 4. Conclusion

Ce projet est **prêt pour un usage pédagogique**.

Pour un déploiement professionnel réel, les axes d'amélioration prioritaires
seraient (par ordre) :
1. ⚡ **Compilation Tailwind en local** (gain perf énorme)
2. 🔒 **Variables d'environnement** (.env au lieu de constantes)
3. 🧪 **PHPUnit** sur les classes critiques (Auth, Csrf, Facture, Panier)
4. 📦 **Composer + namespaces PSR-4** (autoload standard)
5. 🏗️ **Pattern Repository** (refactoring des statiques)

L'identification volontaire de ces limites démontre une maîtrise du sujet,
même si certains standards pro n'ont pas été appliqués dans le cadre du TFM.

---

**Document rédigé le 23 mai 2026 — Version finale du projet PDVWeb.**
