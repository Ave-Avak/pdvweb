<?php
/**
 * public/admin/membre_form.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Modifier les données d'un membre.
 *
 * - Avec ?id=X → édition obligatoire (pas de création depuis l'admin,
 *                qui doit utiliser le formulaire d'inscription public)
 *
 * Champs modifiables :
 *   - nom, prénom, date de naissance
 *   - email + email_verifie (case à cocher)
 *   - login (avec vérification d'unicité)
 *
 * NON modifiables ici (autres pages) :
 *   - mot de passe → action "reset_mdp" dans membre_action
 *   - statut → action "promouvoir/degrader" dans membre_action
 *   - indesirable → action "bloquer/debloquer" dans membre_action
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

$idMembre = (int)($_GET['id'] ?? 0);
if ($idMembre <= 0) {
    Flash::erreur('Membre invalide.');
    header('Location: ' . url('/admin/membres.php'));
    exit;
}

$membre = Membre::trouverParId($idMembre);
if (!$membre) {
    Flash::erreur('Membre introuvable.');
    header('Location: ' . url('/admin/membres.php'));
    exit;
}

// Protection : ne pas modifier un compte anonymisé
if (!empty($membre['date_anonymisation'])) {
    Flash::erreur('Ce compte est anonymisé et ne peut plus être modifié.');
    header('Location: ' . url('/admin/membre_detail.php?id=' . $idMembre));
    exit;
}

// Pré-remplissage
$donnees = [
    'nom'            => $membre['nom'],
    'prenom'         => $membre['prenom'],
    'date_naissance' => $membre['date_naissance'],
    'email'          => $membre['email'],
    'login'          => $membre['login'],
    'email_verifie'  => (int)$membre['email_verifie'],
];
$erreurs = [];


// =====================================================================
// TRAITEMENT POST
// =====================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!Csrf::verifierRequete()) {
        Flash::erreur('Session expirée.');
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }

    $donnees['nom']            = trim($_POST['nom']            ?? '');
    $donnees['prenom']         = trim($_POST['prenom']         ?? '');
    $donnees['date_naissance'] = trim($_POST['date_naissance'] ?? '');
    $donnees['email']          = trim($_POST['email']          ?? '');
    $donnees['login']          = trim($_POST['login']          ?? '');
    $donnees['email_verifie']  = !empty($_POST['email_verifie']) ? 1 : 0;

    // -----------------------------------------------------------------
    // Validation
    // -----------------------------------------------------------------

    if ($donnees['nom'] === '') {
        $erreurs['nom'] = 'Le nom est obligatoire.';
    } elseif (mb_strlen($donnees['nom']) > 60) {
        $erreurs['nom'] = 'Maximum 60 caractères.';
    }

    if ($donnees['prenom'] === '') {
        $erreurs['prenom'] = 'Le prénom est obligatoire.';
    } elseif (mb_strlen($donnees['prenom']) > 60) {
        $erreurs['prenom'] = 'Maximum 60 caractères.';
    }

    if ($donnees['date_naissance'] === '') {
        $erreurs['date_naissance'] = 'La date de naissance est obligatoire.';
    } else {
        // Vérification format date + cohérence (>= 13 ans, <= 120 ans)
        $ts = strtotime($donnees['date_naissance']);
        if (!$ts) {
            $erreurs['date_naissance'] = 'Date invalide.';
        } else {
            $age = (int)date('Y') - (int)date('Y', $ts);
            if ($age < 13) {
                $erreurs['date_naissance'] = 'Le membre doit avoir au moins 13 ans.';
            } elseif ($age > 120) {
                $erreurs['date_naissance'] = 'Date trop ancienne.';
            }
        }
    }

    if ($donnees['email'] === '') {
        $erreurs['email'] = 'L\'email est obligatoire.';
    } elseif (!filter_var($donnees['email'], FILTER_VALIDATE_EMAIL)) {
        $erreurs['email'] = 'Format d\'email invalide.';
    } elseif (mb_strlen($donnees['email']) > 150) {
        $erreurs['email'] = 'Maximum 150 caractères.';
    } else {
        // Unicité (sauf si c'est le même membre)
        $autreMembre = Membre::trouverParEmail($donnees['email']);
        if ($autreMembre && (int)$autreMembre['id_membre'] !== $idMembre) {
            $erreurs['email'] = 'Cet email est déjà utilisé par un autre membre.';
        }
    }

    if ($donnees['login'] === '') {
        $erreurs['login'] = 'Le login est obligatoire.';
    } elseif (!preg_match('/^[a-zA-Z0-9_-]{3,50}$/', $donnees['login'])) {
        $erreurs['login'] = '3 à 50 caractères : lettres, chiffres, - ou _.';
    } else {
        // Unicité (sauf si c'est le même membre)
        $autreMembre = Membre::trouverParLogin($donnees['login']);
        if ($autreMembre && (int)$autreMembre['id_membre'] !== $idMembre) {
            $erreurs['login'] = 'Ce login est déjà utilisé par un autre membre.';
        }
    }

    // -----------------------------------------------------------------
    // Enregistrement
    // -----------------------------------------------------------------
    if (empty($erreurs)) {
        try {
            Membre::mettreAJourAdmin($idMembre, $donnees, Auth::id());
            Flash::succes('Membre « ' . h($donnees['login']) . ' » mis à jour.');
            header('Location: ' . url('/admin/membre_detail.php?id=' . $idMembre));
            exit;
        } catch (Throwable $e) {
            $erreurs['general'] = 'Erreur lors de l\'enregistrement.';
            if (DEV_MODE) {
                $erreurs['general'] .= ' [' . $e->getMessage() . ']';
            }
        }
    }
}

$titre = 'Modifier le membre — ' . $membre['login'];
require_once VIEWS_PATH . '/admin/membre_form.php';
