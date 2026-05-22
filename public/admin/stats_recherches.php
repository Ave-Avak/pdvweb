<?php
/**
 * public/admin/stats_recherches.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Analyse des recherches.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

$jours = max(1, (int)($_GET['jours'] ?? 30));

$nbTotal     = Stats::nbRecherchesTotales($jours);
$topRech     = Stats::topRecherches($jours, 25);
$sansResult  = Stats::recherchesSansResultat($jours, 25);

$titre = 'Administration — Recherches';
require_once VIEWS_PATH . '/admin/stats_recherches.php';
