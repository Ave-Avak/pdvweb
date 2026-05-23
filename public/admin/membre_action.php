<?php
/**
 * public/admin/membre_action.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Actions sur un membre.
 *
 * Actions supportées :
 *   - 'bloquer'        : bascule indesirable à 1
 *   - 'debloquer'      : bascule indesirable à 0
 *   - 'promouvoir'     : passe statut à 'admin'
 *   - 'degrader'       : passe statut à 'membre'
 *   - 'reset_mdp'      : génère un mot de passe temporaire
 *   - 'anonymiser'     : suppression RGPD (irréversible)
 *   - 'verifier_email' : marque l'email comme vérifié manuellement
 *
 * Sécurités :
 *   - POST + CSRF obligatoire
 *   - admin ne peut pas se modifier lui-même (anti-bricolage)
 *   - on ne peut pas modifier un compte anonymisé
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verifierRequete()) {
    header('Location: ' . url('/admin/membres.php'));
    exit;
}

$idMembre = (int)($_POST['id_membre'] ?? 0);
$action   = trim($_POST['action'] ?? '');
$retour   = retour_securise($_POST['retour'] ?? null, url('/admin/membres.php'));

if ($idMembre <= 0) {
    Flash::erreur('Membre invalide.');
    header('Location: ' . $retour);
    exit;
}

// Sécurité : admin ne peut pas se modifier lui-même
if ($idMembre === Auth::id()) {
    Flash::erreur('Vous ne pouvez pas effectuer cette action sur votre propre compte.');
    header('Location: ' . $retour);
    exit;
}

$membre = Membre::trouverParId($idMembre);
if (!$membre) {
    Flash::erreur('Membre introuvable.');
    header('Location: ' . $retour);
    exit;
}

// On ne touche pas aux comptes anonymisés
if (!empty($membre['date_anonymisation'])) {
    Flash::erreur('Action impossible sur un compte anonymisé.');
    header('Location: ' . $retour);
    exit;
}

switch ($action) {
    case 'bloquer':
        Membre::bloquer($idMembre, true);
        AuditLog::enregistrer('membre.bloquer', Auth::id(), 'membre', $idMembre);
        Flash::succes('Membre bloqué.');
        break;

    case 'debloquer':
        Membre::bloquer($idMembre, false);
        AuditLog::enregistrer('membre.debloquer', Auth::id(), 'membre', $idMembre);
        Flash::succes('Membre débloqué.');
        break;

    case 'promouvoir':
        Membre::changerRole($idMembre, 'admin', Auth::id());
        Flash::succes($membre['login'] . ' est maintenant administrateur.');
        break;

    case 'degrader':
        Membre::changerRole($idMembre, 'membre', Auth::id());
        Flash::succes($membre['login'] . ' n\'est plus administrateur.');
        break;

    // -----------------------------------------------------------------
    // Réinitialisation du mot de passe
    // -----------------------------------------------------------------
    case 'reset_mdp':
        // Génère un mot de passe temporaire fort (12 caractères)
        $mdpTemp = self_genererMotPasseTemporaire(12);
        if (Membre::changerMotPasse($idMembre, $mdpTemp)) {
            AuditLog::enregistrer('membre.admin_reset_mdp', Auth::id(), 'membre', $idMembre);
            // ATTENTION : on affiche le mot de passe temporaire à l'admin
            // pour qu'il le communique au membre. C'est un compromis :
            // sans serveur SMTP, on n'a pas d'autre moyen.
            // On stocke le mdp temporaire en session pour l'affichage spécial sur la fiche.
            $_SESSION['mdp_temp_genere'] = [
                'login'   => $membre['login'],
                'mdp'     => $mdpTemp,
                'id'      => $idMembre,
                'expire'  => time() + 300, // 5 minutes max
            ];
            Flash::succes(
                'Mot de passe réinitialisé pour ' . $membre['login']
                . '. Le mot de passe temporaire est affiché ci-dessous.'
            );
            // Retour sur la fiche membre pour afficher le mdp
            $retour = url('/admin/membre_detail.php?id=' . $idMembre);
        } else {
            Flash::erreur('Erreur lors de la réinitialisation.');
        }
        break;

    // -----------------------------------------------------------------
    // Anonymisation RGPD (irréversible)
    // -----------------------------------------------------------------
    case 'anonymiser':
        // Double confirmation : le champ 'confirmation' doit valoir le login
        $confirmation = trim($_POST['confirmation'] ?? '');
        if ($confirmation !== $membre['login']) {
            Flash::erreur(
                'Confirmation invalide. Pour anonymiser, vous devez taper exactement '
                . 'le login du membre (« ' . h($membre['login']) . ' »).'
            );
            $retour = url('/admin/membre_detail.php?id=' . $idMembre);
            break;
        }

        if (Membre::anonymiser($idMembre)) {
            AuditLog::enregistrer('membre.anonymiser', Auth::id(), 'membre', $idMembre, [
                'login_origine' => $membre['login'],
            ]);
            Flash::succes(
                'Membre anonymisé. Toutes ses données personnelles ont été remplacées. '
                . 'Ses commandes sont conservées pour des raisons comptables, '
                . 'mais sont désormais anonymes.'
            );
            // Après anonymisation, on retourne à la liste (la fiche n'a plus de sens)
            $retour = url('/admin/membres.php');
        } else {
            Flash::erreur('Erreur lors de l\'anonymisation.');
        }
        break;

    // -----------------------------------------------------------------
    // Vérification manuelle de l'email
    // -----------------------------------------------------------------
    case 'verifier_email':
        $pdo = Db::pdo();
        $req = $pdo->prepare("UPDATE membre SET email_verifie = 1 WHERE id_membre = ?");
        if ($req->execute([$idMembre])) {
            AuditLog::enregistrer('membre.email_verifie_admin', Auth::id(), 'membre', $idMembre);
            Flash::succes('Email de ' . h($membre['login']) . ' marqué comme vérifié.');
        } else {
            Flash::erreur('Erreur.');
        }
        break;

    default:
        Flash::erreur('Action inconnue.');
}

header('Location: ' . $retour);
exit;


// =====================================================================
// HELPER : génération d'un mot de passe temporaire
// =====================================================================
/**
 * Génère un mot de passe temporaire cryptographiquement sûr.
 * Format : 12 caractères mélangés (lettres maj + min + chiffres),
 *          en évitant les caractères ambigus (0/O, 1/l/I).
 *
 * @param int $longueur Longueur du mot de passe (défaut 12)
 * @return string
 */
function self_genererMotPasseTemporaire(int $longueur = 12): string
{
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789';
    $longueurAlphabet = strlen($alphabet);
    $mdp = '';
    for ($i = 0; $i < $longueur; $i++) {
        $mdp .= $alphabet[random_int(0, $longueurAlphabet - 1)];
    }
    return $mdp;
}
