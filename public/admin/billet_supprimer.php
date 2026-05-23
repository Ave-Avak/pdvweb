<?php
/**
 * public/admin/billet_supprimer.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Suppression (soft) d'un billet.
 *
 * Le billet n'est pas effacé : marqué comme supprimé, ses commentaires
 * deviennent inaccessibles (mais conservés en BDD pour audit).
 * Restauration possible depuis la page corbeille.
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

// Soft delete
Billet::supprimer($idBillet, Auth::id());
AuditLog::enregistrer('billet.supprimer', Auth::id(), 'billet', $idBillet);

Flash::succes('Billet supprimé (visible dans la corbeille pour restauration).');
header('Location: ' . url('/admin/billets.php'));
exit;
