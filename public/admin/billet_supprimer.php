<?php
/**
 * public/admin/billet_supprimer.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Suppression d'un billet.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('/admin/billets.php'));
    exit;
}

if (!Csrf::verifierRequete()) {
    Flash::erreur('Session expirée.');
    header('Location: ' . url('/admin/billets.php'));
    exit;
}

$idBillet = (int)($_POST['id_billet'] ?? 0);
if ($idBillet <= 0) {
    Flash::erreur('Billet introuvable.');
    header('Location: ' . url('/admin/billets.php'));
    exit;
}

Billet::supprimer($idBillet);
Flash::succes('Billet supprimé.');
header('Location: ' . url('/admin/billets.php'));
exit;
