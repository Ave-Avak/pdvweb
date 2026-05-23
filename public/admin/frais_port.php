<?php
/**
 * public/admin/frais_port.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Affichage de la grille des frais de port.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

// Toggle actif/inactif
if ($_SERVER['REQUEST_METHOD'] === 'POST' && Csrf::verifierRequete()) {
    $idFrais = (int)($_POST['id_frais'] ?? 0);
    if ($idFrais > 0) {
        Db::pdo()->prepare("UPDATE frais_port SET actif = 1 - actif WHERE id_frais = ?")
                 ->execute([$idFrais]);
        AuditLog::enregistrer('frais_port.toggle', Auth::id(), 'frais_port', $idFrais);
        Flash::succes('Grille mise à jour.');
    }
    header('Location: ' . url('/admin/frais_port.php'));
    exit;
}

$grilles = FraisPort::listerTous();

$titre = 'Administration — Frais de port';
require_once VIEWS_PATH . '/admin/frais_port.php';
