<?php
/**
 * public/admin/tags.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Liste de tous les tags.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

$tags = Tag::listerTous();

$titre = 'Administration — Tags';
require_once VIEWS_PATH . '/admin/tags.php';
