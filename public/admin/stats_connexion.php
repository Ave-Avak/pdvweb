<?php
/**
 * public/admin/stats_connexion.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Statistiques de connexion.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

$nbCnxJ1  = Stats::nbConnexionsRecentes(1);
$nbCnxJ7  = Stats::nbConnexionsRecentes(7);
$nbCnxJ30 = Stats::nbConnexionsRecentes(30);

$nbActifs7  = Stats::nbMembresActifs(7);
$nbActifs30 = Stats::nbMembresActifs(30);

$activiteParJour = Stats::connexionsParJour(30);
$topMembresCnx   = Stats::topMembresConnexion(30, 10);

$titre = 'Administration — Statistiques de connexion';
require_once VIEWS_PATH . '/admin/stats_connexion.php';
