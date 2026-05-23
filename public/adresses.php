<?php
/**
 * public/adresses.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Liste des adresses du membre connecté.
 *
 * Permet de gérer ses adresses de livraison/facturation (CRUD).
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

Auth::requireLogin();

$adresses = Adresse::listerDuMembre(Auth::id());

$titre = 'Mes adresses';
require_once VIEWS_PATH . '/auth/adresses.php';
