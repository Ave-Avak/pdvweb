<?php
/**
 * public/catalogue.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Catalogue des articles.
 *
 * Accessible à tous (UNM + UM + admin).
 * Supporte : filtre par catégorie, recherche par nom, tri, pagination.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

$recherche   = trim($_GET['q']        ?? '');
$idCategorie = (int)($_GET['categorie'] ?? 0);
$tri         = $_GET['tri']           ?? 'recents';
$page        = max(1, (int)($_GET['page'] ?? 1));

// Validation du tri
if (!in_array($tri, ['recents', 'prix_asc', 'prix_desc', 'populaires'], true)) {
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

// Récupération des articles
$resultat = Article::lister([
    'recherche' => $recherche,
    'categorie' => $idCategorie,
    'tri'       => $tri,
    'page'      => $page,
    'parPage'   => 12,
]);

$categorieActive = $idCategorie > 0 ? Categorie::trouverParId($idCategorie) : null;
$toutesCategories = Categorie::listerTous();

$titre = 'Catalogue';
require_once VIEWS_PATH . '/catalogue/liste.php';
