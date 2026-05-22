<?php
/**
 * public/admin/articles.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Liste des articles pour gestion.
 * Inclut les articles dispo=0 (épuisés/retirés).
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

$resultat = Article::lister([
    'parPage' => 100,  // tout sur une page pour le tableau admin
    'inclureIndisponibles' => true,
]);

$articles = $resultat['articles'];
$categories = Categorie::listerTous();

$titre = 'Administration — Articles';
require_once VIEWS_PATH . '/admin/articles.php';
