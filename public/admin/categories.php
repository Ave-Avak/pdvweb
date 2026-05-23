<?php
/**
 * public/admin/categories.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Gestion des catégories de produits.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

$categories = Categorie::listerTous();

$titre = 'Administration — Catégories';
require_once VIEWS_PATH . '/admin/categories.php';
