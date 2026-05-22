<?php
/**
 * public/commentaire_edit.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Modification d'un commentaire.
 *
 * Autorisé pour : l'auteur lui-même OU un admin.
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
$nouveauCorps  = trim($_POST['corps'] ?? '');

$commentaire = Commentaire::trouverParId($idCommentaire);
if (!$commentaire) {
    Flash::erreur('Commentaire introuvable.');
    header('Location: ' . url('/blog.php'));
    exit;
}

// Vérification des droits (auteur ou admin)
$estAuteur = ((int)$commentaire['id_membre'] === Auth::id());
if (!$estAuteur && !Auth::estAdmin()) {
    Flash::erreur('Vous n\'avez pas le droit de modifier ce commentaire.');
    header('Location: ' . url('/billet.php?id=' . $commentaire['id_billet']));
    exit;
}

if ($nouveauCorps === '') {
    Flash::erreur('Le commentaire ne peut pas être vide.');
} else {
    Commentaire::modifier($idCommentaire, $nouveauCorps);
    Flash::succes('Commentaire mis à jour.');
}

header('Location: ' . url('/billet.php?id=' . $commentaire['id_billet'] . '#c' . $idCommentaire));
exit;
