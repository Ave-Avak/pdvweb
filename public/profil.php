<?php
/**
 * public/profil.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Affichage et modification du profil de l'UM connecté.
 *
 * Trois actions possibles via le même formulaire (différenciées par
 * un champ caché "action") :
 *   - profil      : modifier nom/prénom/email/date naissance
 *   - mot_passe   : changer le mot de passe
 *   - avatar      : changer/supprimer l'avatar
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

// Réservé aux UM
Auth::requireLogin();

$idMembre = Auth::id();
$membre = Membre::trouverParId($idMembre);

// Sécurité supplémentaire : si le compte n'existe plus (supprimé entre-temps), logout
if (!$membre) {
    Auth::deconnecter();
    header('Location: ' . url('/login.php'));
    exit;
}

$erreurs = [];


// =====================================================================
// TRAITEMENT DU POST
// =====================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!Csrf::verifierRequete()) {
        Flash::erreur('Session expirée. Veuillez réessayer.');
        header('Location: ' . url('/profil.php'));
        exit;
    }

    $action = $_POST['action'] ?? '';

    // -----------------------------------------------------------------
    // Action 1 : Modification des infos personnelles
    // -----------------------------------------------------------------
    if ($action === 'profil') {
        $donnees = [
            'nom'            => trim($_POST['nom']            ?? ''),
            'prenom'         => trim($_POST['prenom']         ?? ''),
            'date_naissance' => trim($_POST['date_naissance'] ?? ''),
            'email'          => trim($_POST['email']          ?? ''),
        ];

        // Validations
        if ($donnees['nom'] === '')    $erreurs['nom']    = 'Le nom est obligatoire.';
        if ($donnees['prenom'] === '') $erreurs['prenom'] = 'Le prénom est obligatoire.';

        if ($donnees['date_naissance'] === '') {
            $erreurs['date_naissance'] = 'La date de naissance est obligatoire.';
        } else {
            $dt = DateTime::createFromFormat('Y-m-d', $donnees['date_naissance']);
            if (!$dt || $dt->format('Y-m-d') !== $donnees['date_naissance']) {
                $erreurs['date_naissance'] = 'Date invalide.';
            }
        }

        if ($donnees['email'] === '') {
            $erreurs['email'] = 'L\'email est obligatoire.';
        } elseif (!filter_var($donnees['email'], FILTER_VALIDATE_EMAIL)) {
            $erreurs['email'] = 'Email invalide.';
        } else {
            // Email unique (sauf le sien)
            $autre = Membre::trouverParEmail($donnees['email']);
            if ($autre && (int)$autre['id_membre'] !== $idMembre) {
                $erreurs['email'] = 'Cet email est déjà utilisé par un autre compte.';
            }
        }

        if (empty($erreurs)) {
            Membre::mettreAJour($idMembre, $donnees);

            // Mise à jour des données en session pour reflet immédiat
            $_SESSION['prenom'] = $donnees['prenom'];
            $_SESSION['nom']    = $donnees['nom'];
            $_SESSION['email']  = $donnees['email'];

            Flash::succes('Vos informations ont été mises à jour.');
            header('Location: ' . url('/profil.php'));
            exit;
        }
    }

    // -----------------------------------------------------------------
    // Action 2 : Changement de mot de passe
    // -----------------------------------------------------------------
    elseif ($action === 'mot_passe') {
        $motActuel    = $_POST['mot_actuel']    ?? '';
        $motNouveau   = $_POST['mot_nouveau']   ?? '';
        $motConfirm   = $_POST['mot_confirm']   ?? '';

        // Validations
        if (!Membre::verifierMotPasse($idMembre, $motActuel)) {
            $erreurs['mot_actuel'] = 'Mot de passe actuel incorrect.';
        }

        if (mb_strlen($motNouveau) < 8) {
            $erreurs['mot_nouveau'] = 'Le nouveau mot de passe doit faire au moins 8 caractères.';
        }

        if ($motNouveau !== $motConfirm) {
            $erreurs['mot_confirm'] = 'Les mots de passe ne correspondent pas.';
        }

        if (empty($erreurs)) {
            Membre::changerMotPasse($idMembre, $motNouveau);
            Flash::succes('Mot de passe modifié avec succès.');
            header('Location: ' . url('/profil.php'));
            exit;
        }
    }

    // -----------------------------------------------------------------
    // Action 3 : Changement / suppression d'avatar
    // -----------------------------------------------------------------
    elseif ($action === 'avatar') {

        // Sous-action : suppression de l'avatar actuel
        if (!empty($_POST['supprimer'])) {
            if (!empty($membre['avatar'])) {
                Upload::supprimer($membre['avatar'], UPLOADS_PATH . '/avatars');
                Membre::mettreAJourAvatar($idMembre, null);
                $_SESSION['avatar'] = null;
                Flash::succes('Avatar supprimé.');
            }
            header('Location: ' . url('/profil.php'));
            exit;
        }

        // Sous-action : upload d'un nouvel avatar
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
            $resultatUpload = Upload::image($_FILES['avatar'], UPLOADS_PATH . '/avatars');
            if ($resultatUpload['succes']) {
                // Supprimer l'ancien avatar pour ne pas laisser de fichiers orphelins
                if (!empty($membre['avatar'])) {
                    Upload::supprimer($membre['avatar'], UPLOADS_PATH . '/avatars');
                }
                Membre::mettreAJourAvatar($idMembre, $resultatUpload['fichier']);
                $_SESSION['avatar'] = $resultatUpload['fichier'];
                Flash::succes('Avatar mis à jour.');
                header('Location: ' . url('/profil.php'));
                exit;
            } else {
                $erreurs['avatar'] = $resultatUpload['erreur'];
            }
        } else {
            $erreurs['avatar'] = 'Aucun fichier sélectionné.';
        }
    }

    // Si on arrive ici, c'est qu'il y a eu des erreurs : on recharge $membre
    // pour avoir les dernières valeurs en BDD
    $membre = Membre::trouverParId($idMembre);
}


// =====================================================================
// AFFICHAGE
// =====================================================================
$titre = 'Mon profil';
require_once VIEWS_PATH . '/auth/profil.php';
