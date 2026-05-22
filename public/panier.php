<?php
/**
 * public/panier.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Affichage du panier.
 *
 * Accessible aussi aux UNM (pour qu'ils puissent voir leur panier avant
 * de se connecter), mais l'achat lui-même requiert d'être connecté.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

$detail = Panier::detail();

$titre = 'Mon panier';
require_once VIEWS_PATH . '/panier/voir.php';
