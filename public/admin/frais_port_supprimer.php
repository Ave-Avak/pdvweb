<?php
/**
 * public/admin/frais_port_supprimer.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Supprime une grille de frais de port.
 *
 * Note : si des commandes existantes utilisent cette grille via id_frais,
 * elles continueront de fonctionner car le prix est figé dans la facture.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verifierRequete()) {
    header('Location: ' . url('/admin/frais_port.php'));
    exit;
}

$idFrais = (int)($_POST['id_frais'] ?? 0);
if ($idFrais <= 0) {
    Flash::erreur('Grille tarifaire introuvable.');
    header('Location: ' . url('/admin/frais_port.php'));
    exit;
}

$frais = FraisPort::trouverParId($idFrais);
if (!$frais) {
    Flash::erreur('Grille tarifaire introuvable.');
    header('Location: ' . url('/admin/frais_port.php'));
    exit;
}

try {
    FraisPort::supprimer($idFrais);
    AuditLog::enregistrer('frais_port.supprimer', Auth::id(), 'frais_port', $idFrais, [
        'nom' => $frais['nom'], 'pays' => $frais['pays'],
    ]);
    Flash::succes('Grille « ' . h($frais['nom']) . ' » supprimée.');
} catch (Throwable $e) {
    Flash::erreur('Impossible de supprimer cette grille (utilisée par des commandes existantes ?).');
    if (DEV_MODE) {
        Flash::erreur($e->getMessage());
    }
}

header('Location: ' . url('/admin/frais_port.php'));
exit;
