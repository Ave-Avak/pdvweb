<?php
/**
 * public/admin/stats_top.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Tableaux des top articles et top membres.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

$topVendus  = Stats::topArticlesVendus(10);
$topVus     = Stats::topArticlesVus(30, 10);
$topNotes   = Stats::topArticlesNotes(10);

$topAcheteurs = Stats::topMembresAcheteurs(10);
$topBlog      = Stats::topMembresBlog(10);

$stockBas = Stats::articlesStockBas(5);

$titre = 'Administration — Top articles & membres';
require_once VIEWS_PATH . '/admin/stats_top.php';
