<?php
/**
 * public/admin/article_form.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Création/édition d'article.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

$idArticle = (int)($_GET['id'] ?? 0);
$modeEdition = $idArticle > 0;

$article = null;
if ($modeEdition) {
    $article = Article::trouverParId($idArticle, true);  // inclure dispo=0
    if (!$article) {
        Flash::erreur('Article introuvable.');
        header('Location: ' . url('/admin/articles.php'));
        exit;
    }
}

$donnees = [
    'nom'           => $modeEdition ? $article['nom']           : '',
    'id_categorie'  => $modeEdition ? (int)$article['id_categorie'] : 0,
    'description'   => $modeEdition ? $article['description']   : '',
    'prix'          => $modeEdition ? $article['prix']          : '',
    'stock'         => $modeEdition ? (int)$article['stock']    : 0,
    'image'         => $modeEdition ? $article['image']         : null,
    'poids_grammes' => $modeEdition ? $article['poids_grammes'] : '',
    'dispo'         => $modeEdition ? (int)$article['dispo']    : 1,
];
$erreurs = [];


// =====================================================================
// TRAITEMENT POST
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifierRequete()) {
        Flash::erreur('Session expirée.');
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }

    $donnees['nom']           = trim($_POST['nom'] ?? '');
    $donnees['id_categorie']  = (int)($_POST['id_categorie'] ?? 0);
    $donnees['description']   = trim($_POST['description'] ?? '');
    $donnees['prix']          = (float)str_replace(',', '.', (string)($_POST['prix'] ?? 0));
    $donnees['stock']         = max(0, (int)($_POST['stock'] ?? 0));
    $donnees['poids_grammes'] = $_POST['poids_grammes'] !== '' ? (int)$_POST['poids_grammes'] : null;
    $donnees['dispo']         = !empty($_POST['dispo']) ? 1 : 0;

    // Validation
    if ($donnees['nom'] === '') $erreurs['nom'] = 'Le nom est obligatoire.';
    if ($donnees['id_categorie'] <= 0) $erreurs['id_categorie'] = 'Choisissez une catégorie.';
    if ($donnees['prix'] <= 0) $erreurs['prix'] = 'Le prix doit être positif.';

    // Upload d'image (si présent)
    if (!empty($_FILES['image']['name'])) {
        $upload = Upload::image($_FILES['image'], UPLOADS_PATH . '/articles');
        if ($upload['succes']) {
            // Supprime l'ancienne image si elle existait
            if ($modeEdition && !empty($article['image'])) {
                Upload::supprimer($article['image'], UPLOADS_PATH . '/articles');
            }
            $donnees['image'] = $upload['fichier'];
        } else {
            $erreurs['image'] = $upload['erreur'];
        }
    }

    if (empty($erreurs)) {
        try {
            if ($modeEdition) {
                Article::modifier($idArticle, $donnees);
                Flash::succes('Article mis à jour.');
            } else {
                $idArticle = Article::creer($donnees);
                Flash::succes('Article créé avec succès.');
            }
            header('Location: ' . url('/admin/articles.php'));
            exit;
        } catch (Throwable $e) {
            $erreurs['general'] = 'Erreur d\'enregistrement.';
            if (DEV_MODE) $erreurs['general'] .= ' [' . $e->getMessage() . ']';
        }
    }
}

$categories = Categorie::listerTous();

$titre = $modeEdition ? 'Modifier article' : 'Nouvel article';
require_once VIEWS_PATH . '/admin/article_form.php';
