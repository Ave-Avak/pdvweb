<?php
/**
 * public/admin/article_galerie.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : gestion de la galerie d'images d'un article.
 *
 * Actions :
 *   - GET    ?id=X         → page de gestion de la galerie
 *   - POST   ?id=X         → upload d'une nouvelle image
 *   - POST   ?id=X&supprimer=Y → suppression d'une image (id_image=Y)
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

$idArticle = (int)($_GET['id'] ?? 0);
if ($idArticle <= 0) {
    Flash::erreur('Article introuvable.');
    header('Location: ' . url('/admin/articles.php'));
    exit;
}

$article = Article::trouverParId($idArticle, true);
if (!$article) {
    Flash::erreur('Article introuvable.');
    header('Location: ' . url('/admin/articles.php'));
    exit;
}

// === Suppression d'une image ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['supprimer_image'])) {
    if (!Csrf::verifierRequete()) {
        Flash::erreur('Session expirée.');
    } else {
        $idImage = (int)$_POST['supprimer_image'];
        if (Article::supprimerImage($idImage)) {
            AuditLog::enregistrer('article.image.supprimer', Auth::id(), 'article', $idArticle,
                ['id_image' => $idImage]);
            Flash::succes('Image supprimée.');
        } else {
            Flash::erreur('Erreur de suppression.');
        }
    }
    header('Location: ' . url('/admin/article_galerie.php?id=' . $idArticle));
    exit;
}

// === Upload d'images ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['images']['name'][0])) {
    if (!Csrf::verifierRequete()) {
        Flash::erreur('Session expirée.');
        header('Location: ' . url('/admin/article_galerie.php?id=' . $idArticle));
        exit;
    }

    $nbAjoutees = 0;
    $erreurs = [];

    // Réorganiser le tableau $_FILES (PHP est bizarre avec les multi-uploads)
    $fichiers = [];
    foreach ($_FILES['images']['name'] as $idx => $nom) {
        $fichiers[] = [
            'name'     => $_FILES['images']['name'][$idx],
            'type'     => $_FILES['images']['type'][$idx],
            'tmp_name' => $_FILES['images']['tmp_name'][$idx],
            'error'    => $_FILES['images']['error'][$idx],
            'size'     => $_FILES['images']['size'][$idx],
        ];
    }

    foreach ($fichiers as $fichier) {
        if ($fichier['error'] !== UPLOAD_ERR_OK) continue;

        // Pour la galerie d'articles, on accepte plus de formats que pour les avatars
        // (les avatars CDC = gif/jpeg uniquement, les images produit = standard web)
        $extensionsGalerie = ['gif', 'jpg', 'jpeg', 'png', 'webp'];
        $upload = Upload::image($fichier, UPLOADS_PATH . '/articles', $extensionsGalerie);
        if ($upload['succes']) {
            $ordreMax = Article::nbImages($idArticle);
            $idImage = Article::ajouterImage($idArticle, $upload['fichier'], $ordreMax);
            if ($idImage > 0) {
                $nbAjoutees++;
                AuditLog::enregistrer('article.image.ajouter', Auth::id(), 'article', $idArticle,
                    ['id_image' => $idImage, 'fichier' => $upload['fichier']]);
            }
        } else {
            $erreurs[] = $upload['erreur'];
        }
    }

    if ($nbAjoutees > 0) {
        Flash::succes("$nbAjoutees image" . ($nbAjoutees > 1 ? 's' : '') . " ajoutée" . ($nbAjoutees > 1 ? 's' : '') . " à la galerie.");
    }
    if (!empty($erreurs)) {
        Flash::erreur('Certaines images n\'ont pas pu être uploadées : ' . implode(', ', $erreurs));
    }

    header('Location: ' . url('/admin/article_galerie.php?id=' . $idArticle));
    exit;
}

$images = Article::imagesDe($idArticle);

$titre = 'Galerie : ' . $article['nom'];
require_once VIEWS_PATH . '/admin/article_galerie.php';
