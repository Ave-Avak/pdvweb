<?php
/**
 * public/commentaire_post.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Ajout d'un commentaire sur un billet.
 *
 * Réservé aux UM (cahier des charges).
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

$idBillet = (int)($_POST['id_billet'] ?? 0);
$corps    = trim($_POST['corps'] ?? '');

// Vérification du billet
$billet = Billet::trouverParId($idBillet);
if (!$billet) {
    Flash::erreur('Billet introuvable.');
    header('Location: ' . url('/blog.php'));
    exit;
}

// Vérification du membre (pas bloqué)
$membreInfos = Membre::trouverParId(Auth::id());
if ($membreInfos && (int)$membreInfos['indesirable'] === 1) {
    Flash::erreur('Votre compte est suspendu, vous ne pouvez plus commenter.');
    header('Location: ' . url('/billet.php?id=' . $idBillet));
    exit;
}

// Validation du contenu
if ($corps === '') {
    Flash::erreur('Le commentaire ne peut pas être vide.');
} elseif (mb_strlen($corps) > 5000) {
    Flash::erreur('Le commentaire est trop long (5000 caractères max).');
} else {
    Commentaire::creer($idBillet, Auth::id(), $corps);
    Flash::succes('Commentaire publié.');
}

// Redirection vers le billet, ancre sur les commentaires
header('Location: ' . url('/billet.php?id=' . $idBillet . '#commentaires'));
exit;
