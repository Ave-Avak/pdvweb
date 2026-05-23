<?php
/**
 * public/comparer.php
 * ---------------------------------------------------------------------
 * Comparateur d'articles côte à côte (max 4 articles).
 *
 * Accessible à tous. Les articles à comparer sont passés en query string
 * (?ids=1,2,3) ou stockés en session pour pouvoir naviguer.
 *
 * Actions :
 *   - GET ?ids=1,2,3       → affiche les articles
 *   - GET ?add=5           → ajoute l'article 5 à la liste de comparaison
 *   - GET ?remove=3        → retire l'article 3
 *   - GET ?clear=1         → vide la liste
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

// Init la liste de comparaison en session
if (!isset($_SESSION['comparateur']) || !is_array($_SESSION['comparateur'])) {
    $_SESSION['comparateur'] = [];
}

// Action : vider
if (isset($_GET['clear'])) {
    $_SESSION['comparateur'] = [];
    Flash::info('Comparateur vidé.');
    header('Location: ' . url('/catalogue.php'));
    exit;
}

// Action : ajouter
if (isset($_GET['add'])) {
    $idAjouter = (int)$_GET['add'];
    if ($idAjouter > 0) {
        // Vérifie que l'article existe et est dispo
        $article = Article::trouverParId($idAjouter);
        if ($article) {
            if (in_array($idAjouter, $_SESSION['comparateur'], true)) {
                Flash::info('Cet article est déjà dans le comparateur.');
            } elseif (count($_SESSION['comparateur']) >= 4) {
                Flash::erreur('Vous ne pouvez comparer que 4 articles maximum.');
            } else {
                $_SESSION['comparateur'][] = $idAjouter;
                Flash::succes('« ' . h($article['nom']) . ' » ajouté au comparateur.');
            }
        }
    }
    // Redirige sur la page d'origine ou comparateur
    $retour = retour_securise($_GET['retour'] ?? null, url('/comparer.php'));
    header('Location: ' . $retour);
    exit;
}

// Action : retirer
if (isset($_GET['remove'])) {
    $idRetirer = (int)$_GET['remove'];
    $_SESSION['comparateur'] = array_values(
        array_filter($_SESSION['comparateur'], fn($id) => (int)$id !== $idRetirer)
    );
    Flash::info('Article retiré du comparateur.');
    header('Location: ' . url('/comparer.php'));
    exit;
}

// Override depuis query string ?ids=1,2,3 (pour partage de lien)
if (isset($_GET['ids'])) {
    $idsRaw = explode(',', $_GET['ids']);
    $ids = array_unique(array_filter(array_map('intval', $idsRaw)));
    $_SESSION['comparateur'] = array_slice($ids, 0, 4);
}

// Récupère les articles à comparer
$idsACompar = $_SESSION['comparateur'];
$articlesACompar = Article::pourComparaison($idsACompar);

// Met à jour la session si certains articles n'existent plus
$_SESSION['comparateur'] = array_map(fn($a) => (int)$a['id_article'], $articlesACompar);

$titre = 'Comparateur d\'articles';
require_once VIEWS_PATH . '/comparer.php';
