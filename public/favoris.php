<?php
/**
 * public/favoris.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Liste des articles favoris du membre.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

Auth::requireLogin();

$favoris = Favori::listerDuMembre(Auth::id());

$titre = 'Mes favoris';
require_once VIEWS_PATH . '/auth/favoris.php';
