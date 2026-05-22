<?php
/**
 * public/historique.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Historique des achats du membre connecté.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

Auth::requireLogin();

$commandes = Facture::listerDuMembre(Auth::id());

$titre = 'Mes commandes';
require_once VIEWS_PATH . '/auth/historique.php';
