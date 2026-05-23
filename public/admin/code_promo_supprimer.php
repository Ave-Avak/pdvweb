<?php
/**
 * public/admin/code_promo_supprimer.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Suppression définitive d'un code promo.
 *
 * Note : ON DELETE CASCADE retire aussi les enregistrements de la
 * table code_promo_utilisation. Si le code a été utilisé, on garde
 * une trace dans l'audit log.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . url('/admin/codes_promo.php'));
    exit;
}

if (!Csrf::verifierRequete()) {
    Flash::erreur('Session expirée.');
    header('Location: ' . url('/admin/codes_promo.php'));
    exit;
}

$idCode = (int)($_POST['id_code'] ?? 0);
if ($idCode <= 0) {
    Flash::erreur('Code promo introuvable.');
    header('Location: ' . url('/admin/codes_promo.php'));
    exit;
}

$codePromo = CodePromo::trouverParId($idCode);
if (!$codePromo) {
    Flash::erreur('Code promo introuvable.');
    header('Location: ' . url('/admin/codes_promo.php'));
    exit;
}

try {
    CodePromo::supprimer($idCode);
    AuditLog::enregistrer('code_promo.supprimer', Auth::id(), 'code_promo', $idCode, [
        'code' => $codePromo['code'],
    ]);
    Flash::succes('Code promo « ' . h($codePromo['code']) . ' » supprimé.');
} catch (Throwable $e) {
    Flash::erreur('Erreur lors de la suppression.');
    if (DEV_MODE) {
        Flash::erreur($e->getMessage());
    }
}

header('Location: ' . url('/admin/codes_promo.php'));
exit;
