<?php
/**
 * public/admin/commande_statut.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Changement de statut d'une commande.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verifierRequete()) {
    header('Location: ' . url('/admin/commandes.php'));
    exit;
}

$idFacture = (int)($_POST['id_facture'] ?? 0);
$idStatut  = (int)($_POST['id_statut']  ?? 0);

if ($idFacture <= 0 || $idStatut <= 0) {
    Flash::erreur('Données invalides.');
    header('Location: ' . url('/admin/commandes.php'));
    exit;
}

Facture::changerStatut($idFacture, $idStatut);
AuditLog::enregistrer('commande.changer_statut', Auth::id(), 'achat_facture', $idFacture, [
    'nouveau_statut' => $idStatut,
]);
Flash::succes('Statut mis à jour.');

header('Location: ' . url('/admin/commandes.php'));
exit;
