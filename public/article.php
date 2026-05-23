<?php
/**
 * public/article.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Détail d'un article.
 *
 * Affiche : description, prix, stock, photo, notes/avis, articles similaires.
 * Enregistre la vue dans la table vue_article (stats admin).
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

$idArticle = (int)($_GET['id'] ?? 0);
if ($idArticle <= 0) {
    Flash::erreur('Article introuvable.');
    header('Location: ' . url('/catalogue.php'));
    exit;
}

$article = Article::trouverParId($idArticle);
if (!$article) {
    Flash::erreur('Cet article n\'existe pas ou n\'est plus disponible.');
    header('Location: ' . url('/catalogue.php'));
    exit;
}

// Enregistrement de la vue (anonyme si UNM)
Article::enregistrerVue($idArticle, Auth::id());

// Statistiques de notation
$statsNotes = NoteArticle::statistiques($idArticle);
$notes      = NoteArticle::listerParArticle($idArticle);

// État du favori pour le membre connecté
$estFavori = Auth::estConnecte() && Favori::aFavori(Auth::id(), $idArticle);

// L'utilisateur a-t-il acheté cet article (pour pouvoir noter) ?
$peutNoter = Auth::estConnecte() && NoteArticle::aAcheteArticle(Auth::id(), $idArticle);
$saNote    = $peutNoter ? NoteArticle::noteDuMembre(Auth::id(), $idArticle) : null;

// Articles similaires (même catégorie, max 4)
$similaires = Article::lister([
    'categorie' => (int)$article['id_categorie'],
    'parPage'   => 5,  // on en prendra 4 en excluant l'article courant
]);
$articlesSimilaires = array_filter(
    $similaires['articles'],
    fn($a) => (int)$a['id_article'] !== $idArticle
);
$articlesSimilaires = array_slice($articlesSimilaires, 0, 4);

// Tags associés (Phase 3.2)
$tagsArticle = Article::tagsDe($idArticle);

// Galerie d'images (migration 12)
$imagesGalerie = Article::imagesDe($idArticle);

$titre = $article['nom'];
require_once VIEWS_PATH . '/catalogue/detail.php';
