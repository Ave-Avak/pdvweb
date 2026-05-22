<?php
/**
 * public/index.php
 * ---------------------------------------------------------------------
 * Page d'accueil du site PDVWeb.
 *
 * Selon le cahier des charges :
 *   - Description des services accessibles aux UM connectés vs UNM
 *   - Zone de saisie login / mot de passe pour s'authentifier
 *   - Accessible à tous (UNM et UM)
 * ---------------------------------------------------------------------
 */

// Amorçage de l'application
require_once __DIR__ . '/../includes/bootstrap.php';

// Définition des variables pour la vue
$titre = 'Accueil';

// On affiche le formulaire de login dans l'accueil uniquement si l'utilisateur
// n'est pas déjà connecté.
$afficherLogin = !Auth::estConnecte();
$membre = Auth::membre();

// Inclusion de la vue
require_once VIEWS_PATH . '/accueil.php';
