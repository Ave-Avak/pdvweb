<?php
/**
 * public/login.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Connexion.
 *
 * Reçoit aussi les soumissions du mini-formulaire d'accueil.
 * Anti brute-force intégré (via Membre::tenterConnexion).
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

// Si déjà connecté, redirection
if (Auth::estConnecte()) {
    header('Location: ' . url('/index.php'));
    exit;
}

$login = '';   // pré-remplissage en cas d'erreur


// =====================================================================
// TRAITEMENT DU FORMULAIRE (POST)
// =====================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF
    if (!Csrf::verifierRequete()) {
        Flash::erreur('Session expirée. Veuillez réessayer.');
        header('Location: ' . url('/login.php'));
        exit;
    }

    $login    = trim($_POST['login']     ?? '');
    $motPasse = $_POST['mot_passe'] ?? '';

    // Tentative de connexion (gère anti brute-force + audit en interne)
    $resultat = Membre::tenterConnexion($login, $motPasse);

    if ($resultat['succes']) {
        // Connexion (stocke en session + log connexion + maj derniere_connexion)
        Auth::connecter($resultat['membre']);

        Flash::succes('Bienvenue, ' . h($resultat['membre']['prenom']) . ' !');

        // Redirection vers la page demandée avant login, sinon accueil
        $urlSuivante = $_SESSION['redirect_after_login'] ?? url('/index.php');
        unset($_SESSION['redirect_after_login']);

        header('Location: ' . $urlSuivante);
        exit;
    } else {
        // Échec : on affiche l'erreur, le login reste rempli (mais pas le mdp)
        Flash::erreur($resultat['erreur']);
    }
}


// =====================================================================
// AFFICHAGE
// =====================================================================
$titre = 'Connexion';
require_once VIEWS_PATH . '/auth/login.php';
