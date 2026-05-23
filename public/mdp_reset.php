<?php
/**
 * public/mdp_reset.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Réinitialisation effective du mot de passe via token.
 *
 * Reçoit ?token=... dans l'URL. Vérifie validité + expiration + non-utilisation.
 * Sur POST avec nouveau mot de passe : update + invalide le token.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

if (Auth::estConnecte()) {
    header('Location: ' . url('/index.php'));
    exit;
}

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
if ($token === '') {
    Flash::erreur('Lien invalide.');
    header('Location: ' . url('/login.php'));
    exit;
}

$tokenHash = Securite::hasherToken($token);

// Recherche du token : valide, pas expiré, pas encore utilisé
$req = Db::pdo()->prepare(
    "SELECT t.*, m.login, m.email, m.indesirable, m.date_anonymisation
     FROM token t
     INNER JOIN membre m ON m.id_membre = t.id_membre
     WHERE t.token_hash = ?
       AND t.type = 'reset_password'
       AND t.date_expiration > NOW()
       AND t.date_utilisation IS NULL
     LIMIT 1"
);
$req->execute([$tokenHash]);
$tokenRow = $req->fetch();

if (!$tokenRow || $tokenRow['indesirable'] || $tokenRow['date_anonymisation']) {
    Flash::erreur('Ce lien de réinitialisation est invalide, expiré, déjà utilisé, ou le compte n\'est plus actif.');
    header('Location: ' . url('/login.php'));
    exit;
}

$erreurs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifierRequete()) {
        Flash::erreur('Session expirée.');
        header('Location: ' . url('/mdp_reset.php?token=' . $token));
        exit;
    }

    $nouveauMdp     = $_POST['mot_passe']         ?? '';
    $confirmationMdp = $_POST['confirmation_mdp'] ?? '';

    if (strlen($nouveauMdp) < 8) {
        $erreurs['mot_passe'] = 'Le mot de passe doit faire au moins 8 caractères.';
    }
    if ($nouveauMdp !== $confirmationMdp) {
        $erreurs['confirmation_mdp'] = 'Les deux mots de passe ne correspondent pas.';
    }

    if (empty($erreurs)) {
        $pdo = Db::pdo();
        $pdo->beginTransaction();
        try {
            // 1. Changer le mot de passe
            Membre::changerMotPasse((int)$tokenRow['id_membre'], $nouveauMdp);

            // 2. Marquer le token comme utilisé
            $pdo->prepare(
                "UPDATE token SET date_utilisation = NOW() WHERE id_token = ?"
            )->execute([$tokenRow['id_token']]);

            // 3. Invalider tous les autres tokens reset de ce membre (sécurité)
            $pdo->prepare(
                "UPDATE token SET date_utilisation = NOW()
                 WHERE id_membre = ? AND type = 'reset_password' AND date_utilisation IS NULL"
            )->execute([$tokenRow['id_membre']]);

            $pdo->commit();

            AuditLog::enregistrer('membre.mdp_reset_effectue', (int)$tokenRow['id_membre'], 'membre', (int)$tokenRow['id_membre']);

            Flash::succes('Mot de passe réinitialisé. Vous pouvez maintenant vous connecter.');
            header('Location: ' . url('/login.php'));
            exit;

        } catch (Throwable $e) {
            $pdo->rollBack();
            $erreurs['general'] = 'Erreur lors de la réinitialisation.';
            if (DEV_MODE) $erreurs['general'] .= ' [' . $e->getMessage() . ']';
        }
    }
}

$titre = 'Nouveau mot de passe';
require_once VIEWS_PATH . '/auth/mdp_reset.php';
