<?php
/**
 * public/admin/billets.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Liste de tous les billets pour gestion.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

// On veut TOUS les billets, sans pagination ici (page admin = tableau complet)
$resultat = Billet::lister(['parPage' => 1000]);
$billets  = $resultat['billets'];

$titre = 'Administration — Billets de blog';
require_once VIEWS_PATH . '/admin/billets.php';
