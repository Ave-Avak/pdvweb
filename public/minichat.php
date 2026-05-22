<?php
/**
 * public/minichat.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Mini-chat.
 *
 * Réservé aux UM connectés (cahier des charges).
 * Affiche les 10 derniers messages et propose un formulaire pour
 * en poster un nouveau.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

// Réservé aux UM
Auth::requireLogin();

$idMembre = Auth::id();
$membre   = Auth::membre();
$erreur   = null;


// =====================================================================
// TRAITEMENT POST : envoi d'un nouveau message
// =====================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Vérification CSRF
    if (!Csrf::verifierRequete()) {
        Flash::erreur('Session expirée. Veuillez recharger la page.');
        header('Location: ' . url('/minichat.php'));
        exit;
    }

    // 2. Vérification que le membre n'est pas bloqué (indésirable)
    $infosMembre = Membre::trouverParId($idMembre);
    if ($infosMembre && (int)$infosMembre['indesirable'] === 1) {
        Flash::erreur('Votre compte est suspendu, vous ne pouvez plus poster.');
        header('Location: ' . url('/minichat.php'));
        exit;
    }

    // 3. Récupération et validation du message
    $message = trim($_POST['message'] ?? '');

    $longueurMax = Minichat::longueurMax();

    if ($message === '') {
        $erreur = 'Le message ne peut pas être vide.';
    } elseif (mb_strlen($message) > $longueurMax) {
        $erreur = "Le message dépasse la limite de $longueurMax caractères.";
    } else {
        // 4. Anti-spam basique : ne pas autoriser deux messages identiques d'affilée
        $dernier = Minichat::dernierMessageDe($idMembre);
        if ($dernier && $dernier['message'] === $message) {
            $erreur = 'Vous venez déjà d\'envoyer ce message.';
        } else {
            // 5. Création du message
            Minichat::creer($idMembre, $message);
            // Pattern POST-Redirect-GET pour éviter le re-post au refresh
            header('Location: ' . url('/minichat.php'));
            exit;
        }
    }
}


// =====================================================================
// AFFICHAGE
// =====================================================================

// On récupère les messages dans l'ordre antichronologique (plus récent en premier)
// puis on les inverse en PHP pour afficher du plus ancien au plus récent.
// Pourquoi ce double mouvement ? Pour avoir TOUJOURS les 10 plus RÉCENTS
// même s'il y a 10 000 messages en BDD, tout en les affichant chronologiquement.
$messages = Minichat::listerDerniers(Minichat::nbAffiches());
$messages = array_reverse($messages);

$titre = 'Mini-chat';
require_once VIEWS_PATH . '/minichat.php';
