<?php
/**
 * public/minichat.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Mini-chat.
 *
 * Réservé aux UM connectés (cahier des charges).
 * Affiche les 10 derniers messages et propose un formulaire pour
 * en poster un nouveau.
 *
 * PSEUDO DE SESSION :
 * Conformément au cahier des charges, chaque UM peut choisir un pseudo
 * pour sa session courante. Ce pseudo est stocké dans $_SESSION['pseudo_minichat']
 * et utilisé à chaque post de message.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

// Réservé aux UM
Auth::requireLogin();

$idMembre = Auth::id();
$membre   = Auth::membre();
$erreur   = null;

// Initialisation du pseudo de session (par défaut = login du membre)
// Le pseudo est verrouillé une fois choisi pendant la session (anti-confusion)
if (!isset($_SESSION['pseudo_minichat'])) {
    $_SESSION['pseudo_minichat'] = $membre['login'];
    $_SESSION['pseudo_minichat_verrouille'] = false;
}
$pseudoSession = $_SESSION['pseudo_minichat'];
$pseudoVerrouille = $_SESSION['pseudo_minichat_verrouille'] ?? false;


// =====================================================================
// TRAITEMENT POST : changement de pseudo OU envoi d'un message
// =====================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Vérification CSRF
    if (!Csrf::verifierRequete()) {
        Flash::erreur('Session expirée. Veuillez recharger la page.');
        header('Location: ' . url('/minichat.php'));
        exit;
    }

    $action = $_POST['action'] ?? 'message';

    // -----------------------------------------------------------------
    // ACTION : changer le pseudo de session (UNE SEULE FOIS par session)
    // -----------------------------------------------------------------
    if ($action === 'changer_pseudo') {
        // Vérifier que le pseudo n'a pas déjà été choisi cette session
        if ($pseudoVerrouille) {
            Flash::erreur('Vous avez déjà choisi votre pseudo pour cette session. '
                        . 'Déconnectez-vous et reconnectez-vous pour en choisir un autre.');
            header('Location: ' . url('/minichat.php'));
            exit;
        }

        $nouveauPseudo = trim($_POST['pseudo'] ?? '');

        if ($nouveauPseudo === '') {
            $erreur = 'Le pseudo ne peut pas être vide.';
        } elseif (mb_strlen($nouveauPseudo) > 50) {
            $erreur = 'Le pseudo est trop long (50 caractères maximum).';
        } elseif (!preg_match('/^[\p{L}\p{N}_\- ]+$/u', $nouveauPseudo)) {
            $erreur = 'Le pseudo contient des caractères non autorisés.';
        } else {
            $_SESSION['pseudo_minichat'] = $nouveauPseudo;
            $_SESSION['pseudo_minichat_verrouille'] = true;  // verrouillé pour cette session
            Flash::succes('Votre pseudo pour cette session est : ' . h($nouveauPseudo)
                        . '. Il sera utilisé jusqu\'à votre déconnexion.');
            header('Location: ' . url('/minichat.php'));
            exit;
        }
    }

    // -----------------------------------------------------------------
    // ACTION : envoyer un message
    // -----------------------------------------------------------------
    elseif ($action === 'message') {
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
                // 5. Création du message AVEC le pseudo de session
                Minichat::creer($idMembre, $message, $pseudoSession);
                // Pattern POST-Redirect-GET pour éviter le re-post au refresh
                header('Location: ' . url('/minichat.php'));
                exit;
            }
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
