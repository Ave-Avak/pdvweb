<?php
/**
 * public/messages_action.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Actions sur la messagerie privée.
 *   - bloquer / debloquer un membre
 *   - supprimer une conversation entière
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verifierRequete()) {
    header('Location: ' . url('/messages.php'));
    exit;
}

$idMembre = Auth::id();
$action   = $_POST['action'] ?? '';
$idCible  = (int)($_POST['id_cible'] ?? 0);

if ($idCible <= 0 || $idCible === $idMembre) {
    Flash::erreur('Action invalide.');
    header('Location: ' . url('/messages.php'));
    exit;
}

switch ($action) {
    case 'bloquer':
        MessagePrive::bloquer($idMembre, $idCible);
        Flash::succes('Membre bloqué. Il ne pourra plus vous envoyer de messages.');
        header('Location: ' . url('/messages.php'));
        break;

    case 'debloquer':
        MessagePrive::debloquer($idMembre, $idCible);
        Flash::succes('Membre débloqué.');
        header('Location: ' . url('/messages.php'));
        break;

    case 'supprimer_thread':
        $nb = MessagePrive::supprimerThread($idMembre, $idCible);
        Flash::succes("$nb message(s) supprimé(s). La conversation a été effacée des deux côtés.");
        header('Location: ' . url('/messages.php'));
        break;

    default:
        Flash::erreur('Action inconnue.');
        header('Location: ' . url('/messages.php'));
}

exit;
