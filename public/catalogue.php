<?php
/**
 * public/catalogue.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Catalogue des articles.
 *
 * Accessible à tous (UNM + UM + admin).
 * Supporte :
 *   - Filtre par catégorie
 *   - Recherche par nom + description
 *   - Filtres avancés : prix min/max, en stock, note minimale, tag
 *   - Tri étendu : recents, prix asc/desc, populaires, note, alpha
 *   - Pagination
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

// Filtres simples
$recherche   = trim($_GET['q']        ?? '');
$idCategorie = (int)($_GET['categorie'] ?? 0);
$tri         = $_GET['tri']           ?? 'recents';
$page        = max(1, (int)($_GET['page'] ?? 1));

// Filtres avancés (Phase 3.1)
$prixMin = isset($_GET['prix_min']) && $_GET['prix_min'] !== '' ? (float)$_GET['prix_min'] : null;
$prixMax = isset($_GET['prix_max']) && $_GET['prix_max'] !== '' ? (float)$_GET['prix_max'] : null;
$enStock = !empty($_GET['en_stock']);
$noteMin = max(0, min(5, (int)($_GET['note_min'] ?? 0)));
$idTag   = (int)($_GET['tag'] ?? 0);

// Validation du tri
if (!in_array($tri, ['recents', 'prix_asc', 'prix_desc', 'populaires', 'note_desc', 'alpha'], true)) {
    $tri = 'recents';
}

// Log de la recherche (best-effort)
if ($recherche !== '') {
    try {
        Db::pdo()->prepare(
            "INSERT INTO recherche_log (id_membre, terme, nb_resultats, contexte)
             VALUES (?, ?, 0, 'catalogue')"
        )->execute([Auth::id(), $recherche]);
    } catch (Throwable $e) {
        // ignore
    }
}

// Récupération des articles avec tous les filtres
$resultat = Article::lister([
    'recherche' => $recherche,
    'categorie' => $idCategorie,
    'tri'       => $tri,
    'page'      => $page,
    'parPage'   => 12,
    // Filtres avancés
    'prix_min'  => $prixMin,
    'prix_max'  => $prixMax,
    'en_stock'  => $enStock,
    'note_min'  => $noteMin,
    'id_tag'    => $idTag,
]);

$categorieActive  = $idCategorie > 0 ? Categorie::trouverParId($idCategorie) : null;
$toutesCategories = Categorie::listerTous();

// Pour le filtre par tag (Phase 3.2 — table article_tag créée plus loin)
$tagActif = null;
$tousTags = [];
try {
    if ($idTag > 0) {
        $tagActif = Tag::trouverParId($idTag);
    }
    // Liste des tags qui ont au moins un article (uniquement si la table existe)
    $tousTags = Db::pdo()->query(
        "SELECT t.* FROM tag t
         WHERE EXISTS (SELECT 1 FROM article_tag WHERE id_tag = t.id_tag)
         ORDER BY t.nom"
    )->fetchAll();
} catch (Throwable $e) {
    // Table article_tag pas encore créée → on ignore
    $tousTags = [];
}

// Récupère prix min/max global pour les placeholders du formulaire
try {
    $infosPrix = Db::pdo()->query(
        "SELECT MIN(prix) AS prix_min_global, MAX(prix) AS prix_max_global
         FROM article WHERE dispo = 1"
    )->fetch();
} catch (Throwable $e) {
    $infosPrix = ['prix_min_global' => 0, 'prix_max_global' => 1000];
}

$titre = 'Catalogue';
require_once VIEWS_PATH . '/catalogue/liste.php';
