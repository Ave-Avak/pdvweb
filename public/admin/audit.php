<?php
/**
 * public/admin/audit.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Visualiseur d'audit log.
 *
 * Filtres : action, membre, entité, période.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

$action    = trim($_GET['action_filtre'] ?? '');
$idMembre  = (int)($_GET['id_membre'] ?? 0);
$entite    = trim($_GET['entite'] ?? '');
$jours     = (int)($_GET['jours'] ?? 30);
$page      = max(1, (int)($_GET['page'] ?? 1));

$resultat = AuditLog::lister([
    'action'    => $action !== '' ? $action : null,
    'id_membre' => $idMembre > 0 ? $idMembre : null,
    'entite'    => $entite !== '' ? $entite : null,
    'jours'     => $jours > 0 ? $jours : null,
    'page'      => $page,
    'parPage'   => 50,
]);

$actionsDispo = AuditLog::actionsDistinctes();

$titre = 'Administration — Audit log';
require_once VIEWS_PATH . '/admin/audit.php';
