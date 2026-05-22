<?php
/**
 * public/admin/membres.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Liste paginée des membres avec filtres et recherche.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

$recherche = trim($_GET['q']      ?? '');
$statut    = trim($_GET['statut'] ?? '');
$filtre    = trim($_GET['filtre'] ?? 'tous');
$page      = max(1, (int)($_GET['page'] ?? 1));

$resultat = Membre::listerAvecFiltres([
    'recherche' => $recherche,
    'statut'    => $statut,
    'filtre'    => $filtre,
    'page'      => $page,
    'parPage'   => 25,
]);

$titre = 'Administration — Membres';
require_once VIEWS_PATH . '/admin/membres.php';
