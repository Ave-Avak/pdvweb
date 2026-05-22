<?php
/**
 * public/commentaire_delete.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Suppression d'un commentaire (auteur ou admin).
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('/blog.php'));
    exit;
}

if (!Csrf::verifierRequete()) {
    Flash::erreur('Session expirée.');
    header('Location: ' . url('/blog.php'));
    exit;
}

$idCommentaire = (int)($_POST['id_commentaire'] ?? 0);

$commentaire = Commentaire::trouverParId($idCommentaire);
if (!$commentaire) {
    Flash::erreur('Commentaire introuvable.');
    header('Location: ' . url('/blog.php'));
    exit;
}

$estAuteur = ((int)$commentaire['id_membre'] === Auth::id());
if (!$estAuteur && !Auth::estAdmin()) {
    Flash::erreur('Vous n\'avez pas le droit de supprimer ce commentaire.');
    header('Location: ' . url('/billet.php?id=' . $commentaire['id_billet']));
    exit;
}

Commentaire::supprimer($idCommentaire);
Flash::succes($estAuteur ? 'Commentaire supprimé.' : 'Commentaire modéré.');

header('Location: ' . url('/billet.php?id=' . $commentaire['id_billet'] . '#commentaires'));
exit;
