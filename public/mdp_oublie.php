<?php
/**
 * public/mdp_oublie.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Demande de réinitialisation de mot de passe.
 *
 * Génère un token unique, l'enregistre en BDD (hashé), et affiche le
 * lien à l'utilisateur (en l'absence d'envoi d'email réel pour ce TFM).
 *
 * Note pédagogique : en production, ce lien serait envoyé par email
 * via PHPMailer/Symfony Mailer. Ici, on l'affiche directement pour
 * permettre les tests.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

if (Auth::estConnecte()) {
    header('Location: ' . url('/index.php'));
    exit;
}

$email = '';
$lienReset = null;  // affiché en DEV_MODE pour tester sans email

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifierRequete()) {
        Flash::erreur('Session expirée.');
        header('Location: ' . url('/mdp_oublie.php'));
        exit;
    }

    // Rate limit
    if (Securite::estRateLimited('mdp_reset', 5, 60)) {
        Flash::erreur('Trop de demandes. Réessayez dans 1 heure.');
        header('Location: ' . url('/mdp_oublie.php'));
        exit;
    }

    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        Flash::erreur('Adresse email invalide.');
    } else {
        $membre = Membre::trouverParEmail($email);

        // ATTENTION SÉCURITÉ : on affiche toujours le MÊME message,
        // que l'email existe ou non, pour éviter l'énumération de comptes.
        // On enregistre quand même la tentative (rate limit).
        Securite::enregistrerActionRateLimit('mdp_reset');

        if ($membre && empty($membre['date_anonymisation']) && (int)$membre['indesirable'] === 0) {
            // Génération du token
            $token     = Securite::genererToken(32);
            $tokenHash = Securite::hasherToken($token);
            $expiration = (new DateTime('+1 hour'))->format('Y-m-d H:i:s');

            $req = Db::pdo()->prepare(
                "INSERT INTO token (id_membre, type, token_hash, date_expiration)
                 VALUES (?, 'reset_password', ?, ?)"
            );
            $req->execute([$membre['id_membre'], $tokenHash, $expiration]);

            AuditLog::enregistrer('membre.mdp_reset_demande', $membre['id_membre'], 'membre', $membre['id_membre']);

            // En production, ENVOYER PAR EMAIL.
            // En dev (DEV_MODE actif), on affiche le lien directement.
            if (DEV_MODE) {
                $lienReset = url('/mdp_reset.php?token=' . $token);
            }
        }

        Flash::succes('Si cette adresse est associée à un compte, un email de réinitialisation vient de vous être envoyé. Vérifiez votre boîte de réception.');
    }
}

$titre = 'Mot de passe oublié';
require_once VIEWS_PATH . '/auth/mdp_oublie.php';
