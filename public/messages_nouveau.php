<?php
/**
 * public/messages_nouveau.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Démarrer une nouvelle conversation.
 *
 * Permet de chercher un membre par login/prénom/nom puis d'écrire
 * le premier message. Après envoi, redirige vers le thread.
 *
 * Peut être pré-rempli avec ?to=ID (depuis bouton "Envoyer un message"
 * sur un profil ou commentaire).
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

Auth::requireLogin();

$idMembre = Auth::id();
$idDestinataire = (int)($_GET['to'] ?? $_POST['id_destinataire'] ?? 0);
$rechercheQ = trim($_GET['q'] ?? '');
$destinataire = null;
$resultatsRecherche = [];

if ($idDestinataire > 0) {
    $destinataire = Membre::trouverParId($idDestinataire);
    if (!$destinataire || !empty($destinataire['date_anonymisation']) || (int)$destinataire['id_membre'] === $idMembre) {
        Flash::erreur('Destinataire invalide.');
        header('Location: ' . url('/messages_nouveau.php'));
        exit;
    }
    // Si une conversation existe déjà → rediriger vers le thread
    $existant = Db::pdo()->prepare(
        "SELECT COUNT(*) FROM message_prive
         WHERE (id_expediteur = ? AND id_destinataire = ?)
            OR (id_expediteur = ? AND id_destinataire = ?)"
    );
    $existant->execute([$idMembre, $idDestinataire, $idDestinataire, $idMembre]);
    if ((int)$existant->fetchColumn() > 0 && $_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . url('/messages_thread.php?with=' . $idDestinataire));
        exit;
    }
} elseif ($rechercheQ !== '') {
    $resultatsRecherche = MessagePrive::rechercherDestinataires($idMembre, $rechercheQ);
}


// ---------------------------------------------------------------------
// TRAITEMENT POST
// ---------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifierRequete()) {
        Flash::erreur('Session expirée.');
        header('Location: ' . url('/messages_nouveau.php'));
        exit;
    }

    if (Securite::estRateLimited('mp_envoi', 10, 5)) {
        Flash::erreur('Trop de messages envoyés. Patientez quelques minutes.');
        header('Location: ' . url('/messages.php'));
        exit;
    }

    $corps = $_POST['corps'] ?? '';
    $sujet = trim($_POST['sujet'] ?? '');

    $resultat = MessagePrive::envoyer($idMembre, $idDestinataire, $corps, $sujet);

    if ($resultat['succes']) {
        Securite::enregistrerActionRateLimit('mp_envoi');
        Flash::succes('Message envoyé.');
        header('Location: ' . url('/messages_thread.php?with=' . $idDestinataire));
        exit;
    } else {
        Flash::erreur($resultat['erreur']);
    }
}

$titre = 'Nouveau message';
require_once VIEWS_PATH . '/auth/messages_nouveau.php';
