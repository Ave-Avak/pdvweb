<?php
/**
 * public/minichat_supprimer.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Suppression d'un message du mini-chat.
 *
 * Autorisé pour :
 *   - L'auteur du message lui-même
 *   - L'administrateur (modération)
 *
 * Méthode POST uniquement (jamais via lien GET, pour éviter les CSRF
 * et les suppressions par crawler).
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

// Réservé aux membres connectés
Auth::requireLogin();

// Méthode POST uniquement
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Flash::erreur('Action non autorisée.');
    header('Location: ' . url('/minichat.php'));
    exit;
}

// Vérification CSRF
if (!Csrf::verifierRequete()) {
    Flash::erreur('Session expirée. Veuillez recharger la page.');
    header('Location: ' . url('/minichat.php'));
    exit;
}

// Récupération de l'ID du message à supprimer
$idMessage = (int)($_POST['id_message'] ?? 0);
if ($idMessage <= 0) {
    Flash::erreur('Message introuvable.');
    header('Location: ' . url('/minichat.php'));
    exit;
}

// Recherche du message
$message = Minichat::trouverParId($idMessage);
if (!$message) {
    Flash::erreur('Ce message n\'existe pas ou a déjà été supprimé.');
    header('Location: ' . url('/minichat.php'));
    exit;
}

// Vérification des droits :
// - le membre est l'auteur du message
// - OU le membre est administrateur (modération)
$idMembreCourant = Auth::id();
$estAuteur = (int)$message['id_membre'] === $idMembreCourant;
$estAdmin  = Auth::estAdmin();

if (!$estAuteur && !$estAdmin) {
    Flash::erreur('Vous n\'avez pas le droit de supprimer ce message.');
    header('Location: ' . url('/minichat.php'));
    exit;
}

// Suppression
Minichat::supprimer($idMessage);

Flash::succes($estAuteur ? 'Message supprimé.' : 'Message modéré.');
header('Location: ' . url('/minichat.php'));
exit;
