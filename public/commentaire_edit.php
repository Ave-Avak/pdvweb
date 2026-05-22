<?php
/**
 * public/commentaire_edit.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Modification d'un commentaire.
 *
 * IMPORTANT : seul l'AUTEUR peut modifier son commentaire.
 * L'admin n'a PAS le droit de modifier (intégrité du discours,
 * exigence éthique et légale - diffamation potentielle).
 * L'admin peut uniquement SUPPRIMER (modération).
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

// SEUL L'AUTEUR peut modifier son commentaire (l'admin NE PEUT PAS)
if ((int)$commentaire['id_membre'] !== Auth::id()) {
    Flash::erreur('Vous ne pouvez modifier que vos propres commentaires.');
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
