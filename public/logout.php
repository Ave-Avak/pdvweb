<?php
/**
 * public/logout.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Déconnexion.
 * Vide la session et redirige vers l'accueil.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

// On ne stocke que le prénom avant déconnexion pour le message
$prenom = $_SESSION['prenom'] ?? '';

Auth::deconnecter();

// Démarrer une nouvelle session pour pouvoir afficher le message flash
session_start();
Flash::info('À bientôt' . ($prenom ? ', ' . $prenom : '') . ' !');

header('Location: ' . url('/index.php'));
exit;
