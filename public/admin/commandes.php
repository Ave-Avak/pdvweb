<?php
/**
 * public/admin/commandes.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Liste des commandes avec filtres et pagination.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

// Récupération des filtres depuis la query string
$filtres = [
    'statut'    => (int)($_GET['statut']    ?? 0),
    'recherche' => trim($_GET['q']          ?? ''),
    'date_min'  => trim($_GET['date_min']   ?? ''),
    'date_max'  => trim($_GET['date_max']   ?? ''),
    'page'      => max(1, (int)($_GET['page'] ?? 1)),
    'parPage'   => 20,
];

$resultat   = Facture::listerToutes($filtres);
$commandes  = $resultat['commandes'];
$total      = $resultat['total'];
$totalPages = $resultat['totalPages'];
$page       = $resultat['page'];
$parPage    = $resultat['parPage'];

$statuts = Db::pdo()->query("SELECT * FROM statut_commande ORDER BY ordre")->fetchAll();

$titre = 'Administration — Commandes';
require_once VIEWS_PATH . '/admin/commandes.php';
