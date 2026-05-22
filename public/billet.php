<?php
/**
 * public/billet.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Détail d'un billet de blog.
 *
 * Affiche le billet (rendu Markdown), ses tags, ses likes et la
 * liste des commentaires. Si UM connecté, propose un formulaire
 * d'ajout de commentaire.
 *
 * Si UNM (visiteur), la zone de commentaires est en lecture seule.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

// Récupération du billet
$idBillet = (int)($_GET['id'] ?? 0);
if ($idBillet <= 0) {
    Flash::erreur('Billet introuvable.');
    header('Location: ' . url('/blog.php'));
    exit;
}

$billet = Billet::trouverParId($idBillet);
if (!$billet) {
    Flash::erreur('Ce billet n\'existe pas ou a été supprimé.');
    header('Location: ' . url('/blog.php'));
    exit;
}

// Données associées
$tags         = Billet::tagsDe($idBillet);
$commentaires = Commentaire::listerParBillet($idBillet);
$nbLikes      = LikeContenu::compter('billet', $idBillet);

// État du like pour le membre connecté
$aLikeBillet = Auth::estConnecte()
    ? LikeContenu::aLike(Auth::id(), 'billet', $idBillet)
    : false;

// Pré-calcul des "j'ai liké" pour chaque commentaire (évite N requêtes dans la vue)
$mesLikesCommentaires = [];
if (Auth::estConnecte()) {
    foreach ($commentaires as $c) {
        $mesLikesCommentaires[$c['id_commentaire']] =
            LikeContenu::aLike(Auth::id(), 'commentaire', (int)$c['id_commentaire']);
    }
}

$titre = $billet['titre'];
require_once VIEWS_PATH . '/blog/detail.php';
