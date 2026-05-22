<?php
/**
 * public/blog.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Liste des billets du blog.
 *
 * Accessible à tous (UNM + UM + Admin).
 * Support : recherche par titre, filtre par tag, tri, pagination.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

// Récupération des paramètres GET
$recherche = trim($_GET['q']   ?? '');
$idTag     = (int)($_GET['tag'] ?? 0);
$tri       = $_GET['tri']      ?? 'recents';
$page      = max(1, (int)($_GET['page'] ?? 1));

// Validation du tri (whitelist - sécurité)
if (!in_array($tri, ['recents', 'populaires', 'commentes'], true)) {
    $tri = 'recents';
}

// Log de la recherche (pour stats admin)
if ($recherche !== '') {
    try {
        $req = Db::pdo()->prepare(
            "INSERT INTO recherche_log (id_membre, terme, nb_resultats, contexte)
             VALUES (?, ?, 0, 'blog')"
        );
        $req->execute([Auth::id(), $recherche]);
    } catch (Throwable $e) {
        // Best-effort, on ne bloque pas l'utilisateur si le log échoue
    }
}

// Récupération des billets
$resultat = Billet::lister([
    'recherche' => $recherche,
    'tag'       => $idTag,
    'tri'       => $tri,
    'page'      => $page,
    'parPage'   => 10,
]);

// Tag actif (pour affichage)
$tagActif = $idTag > 0 ? Tag::trouverParId($idTag) : null;

// Tous les tags (pour le filtre)
$tousLesTags = Tag::listerTous();

// Variables pour la vue
$titre = 'Blog & News';
require_once VIEWS_PATH . '/blog/liste.php';
