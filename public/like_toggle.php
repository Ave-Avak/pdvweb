<?php
/**
 * public/like_toggle.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Toggle d'un like (sur billet ou commentaire).
 *
 * Réservé aux UM. Méthode POST + CSRF.
 * Redirige vers le billet d'origine après action.
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

$type     = $_POST['type'] ?? '';
$idCible  = (int)($_POST['id_cible'] ?? 0);
$idBillet = (int)($_POST['id_billet'] ?? 0); // pour la redirection

// Validation du type
if (!LikeContenu::typeEstValide($type)) {
    Flash::erreur('Type de contenu invalide.');
    header('Location: ' . url('/blog.php'));
    exit;
}

if ($idCible <= 0) {
    Flash::erreur('Contenu introuvable.');
    header('Location: ' . url('/blog.php'));
    exit;
}

// Toggle
LikeContenu::toggle(Auth::id(), $type, $idCible);

// Redirection vers le billet d'origine
if ($idBillet > 0) {
    $ancre = $type === 'commentaire' ? '#c' . $idCible : '';
    header('Location: ' . url('/billet.php?id=' . $idBillet . $ancre));
} else {
    header('Location: ' . url('/blog.php'));
}
exit;
