<?php
/**
 * public/admin/codes_promo.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Liste et activation/désactivation des codes promo.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

// Action de toggle actif/inactif
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Csrf::verifierRequete()) {
    $action = $_POST['action'] ?? '';
    $idCode = (int)($_POST['id_code'] ?? 0);

    if ($action === 'toggle' && $idCode > 0) {
        Db::pdo()->prepare("UPDATE code_promo SET actif = 1 - actif WHERE id_code = ?")
                 ->execute([$idCode]);
        AuditLog::enregistrer('code_promo.toggle', Auth::id(), 'code_promo', $idCode);
        Flash::succes('Code promo mis à jour.');
    }
    header('Location: ' . url('/admin/codes_promo.php'));
    exit;
}

$codes = CodePromo::listerTous();

$titre = 'Administration — Codes promo';
require_once VIEWS_PATH . '/admin/codes_promo.php';
