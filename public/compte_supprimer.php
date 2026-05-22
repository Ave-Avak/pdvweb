<?php
/**
 * public/compte_supprimer.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Suppression du compte membre (anonymisation RGPD).
 *
 * Implémente le DROIT À L'OUBLI (RGPD article 17).
 *
 * Au lieu de hard-delete le compte (ce qui casserait les FK et créerait
 * des "trous" dans les commentaires/factures), on ANONYMISE :
 *   - Toutes les données personnelles sont remplacées par "supprimé"
 *   - Les commentaires/billets passés apparaissent sous "Utilisateur supprimé"
 *   - Les factures restent (obligation légale comptable 10 ans en Belgique)
 *   - L'utilisateur ne peut plus se connecter
 *
 * Demande la confirmation du mot de passe avant action.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

Auth::requireLogin();

$idMembre = Auth::id();
$membre = Membre::trouverParId($idMembre);

// Vérification : un admin ne peut pas se supprimer lui-même via cette page
// (sinon plus aucun admin sur le site)
if ($membre['statut'] === 'admin') {
    Flash::erreur('Un compte administrateur ne peut pas être supprimé via cette page. Contactez Anthropic.');
    header('Location: ' . url('/profil.php'));
    exit;
}

$erreur = null;


// =====================================================================
// TRAITEMENT DU POST (confirmation de suppression)
// =====================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF
    if (!Csrf::verifierRequete()) {
        Flash::erreur('Session expirée. Veuillez réessayer.');
        header('Location: ' . url('/compte_supprimer.php'));
        exit;
    }

    $motPasse  = $_POST['mot_passe']  ?? '';
    $confirmation = $_POST['confirmation'] ?? '';

    // 1. Vérification du mot de passe (sécurité critique)
    if (!Membre::verifierMotPasse($idMembre, $motPasse)) {
        $erreur = 'Mot de passe incorrect.';
    }
    // 2. Vérification de la phrase de confirmation
    elseif (strtoupper(trim($confirmation)) !== 'SUPPRIMER') {
        $erreur = 'Veuillez taper exactement "SUPPRIMER" en majuscules pour confirmer.';
    }
    // 3. Tout est bon : anonymisation
    else {
        try {
            Membre::anonymiser($idMembre);

            // Déconnexion forcée
            Auth::deconnecter();

            // Nouvelle session pour le message flash
            session_start();
            Flash::succes(
                'Votre compte a été supprimé conformément au RGPD. ' .
                'Vos données personnelles ont été anonymisées. ' .
                'Vos achats restent archivés conformément à la loi comptable.'
            );

            header('Location: ' . url('/index.php'));
            exit;

        } catch (Throwable $e) {
            $erreur = 'Erreur lors de la suppression. Veuillez réessayer.';
            if (DEV_MODE) {
                $erreur .= ' [' . $e->getMessage() . ']';
            }
        }
    }
}


// =====================================================================
// AFFICHAGE
// =====================================================================
$titre = 'Supprimer mon compte';
require_once VIEWS_PATH . '/auth/compte_supprimer.php';
