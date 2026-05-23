<?php
/**
 * public/messages_thread.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Fil de discussion avec un autre membre.
 *
 * URL : ?with=ID_AUTRE_MEMBRE
 *
 * - Affiche tous les messages échangés
 * - Marque comme lus les messages reçus
 * - Permet d'envoyer une réponse inline
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

Auth::requireLogin();

$idMembre = Auth::id();
$idAutre  = (int)($_GET['with'] ?? $_POST['with'] ?? 0);

if ($idAutre <= 0 || $idAutre === $idMembre) {
    Flash::erreur('Conversation invalide.');
    header('Location: ' . url('/messages.php'));
    exit;
}

$autre = Membre::trouverParId($idAutre);
if (!$autre || !empty($autre['date_anonymisation'])) {
    Flash::erreur('Ce membre n\'est plus disponible.');
    header('Location: ' . url('/messages.php'));
    exit;
}


// ---------------------------------------------------------------------
// TRAITEMENT POST : envoi d'un message
// ---------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifierRequete()) {
        Flash::erreur('Session expirée.');
        header('Location: ' . url('/messages_thread.php?with=' . $idAutre));
        exit;
    }

    // Rate limit anti-spam (10 messages / 5 min)
    if (Securite::estRateLimited('mp_envoi', 10, 5)) {
        Flash::erreur('Trop de messages envoyés. Patientez quelques minutes.');
        header('Location: ' . url('/messages_thread.php?with=' . $idAutre));
        exit;
    }

    $corps = $_POST['corps'] ?? '';
    $resultat = MessagePrive::envoyer($idMembre, $idAutre, $corps);

    if ($resultat['succes']) {
        Securite::enregistrerActionRateLimit('mp_envoi');
        Flash::succes('Message envoyé.');
    } else {
        Flash::erreur($resultat['erreur']);
    }
    header('Location: ' . url('/messages_thread.php?with=' . $idAutre));
    exit;
}


// ---------------------------------------------------------------------
// AFFICHAGE
// ---------------------------------------------------------------------
$messages = MessagePrive::listerThread($idMembre, $idAutre);

// Détermine si on est bloqué ou si on a bloqué l'autre
$jaiBloque  = MessagePrive::estBloque($idAutre, $idMembre);   // est-ce que MOI je bloque l'autre ?
$ilMaBloque = MessagePrive::estBloque($idMembre, $idAutre);   // est-ce que l'autre ME bloque ?

$titre = 'Conversation avec ' . $autre['prenom'];
require_once VIEWS_PATH . '/auth/messages_thread.php';
