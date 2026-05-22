<?php
/**
 * public/admin/commandes.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Liste de toutes les commandes.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

$commandes = Facture::listerToutes();
$statuts = Db::pdo()->query("SELECT * FROM statut_commande ORDER BY ordre")->fetchAll();

$titre = 'Administration — Commandes';
require_once VIEWS_PATH . '/admin/commandes.php';
