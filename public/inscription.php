<?php
/**
 * public/inscription.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Création de compte UM.
 *
 * Affiche le formulaire en GET, le traite en POST.
 * Validation côté serveur, hash bcrypt, upload optionnel d'avatar.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

// Si déjà connecté, pas besoin de s'inscrire à nouveau
if (Auth::estConnecte()) {
    Flash::info('Vous êtes déjà connecté.');
    header('Location: ' . url('/index.php'));
    exit;
}

// Variables passées à la vue (pré-remplissage en cas d'erreur)
$donnees = [
    'nom' => '',
    'prenom' => '',
    'date_naissance' => '',
    'email' => '',
    'login' => '',
    'rue' => '',
    'numero' => '',
    'cp' => '',
    'ville' => '',
    'pays' => 'Belgique',
];
$erreurs = [];


// =====================================================================
// TRAITEMENT DU FORMULAIRE (POST)
// =====================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // -----------------------------------------------------------------
    // 1. Vérification CSRF (anti Cross-Site Request Forgery)
    // -----------------------------------------------------------------
    if (!Csrf::verifierRequete()) {
        Flash::erreur('Session expirée ou jeton invalide. Veuillez réessayer.');
        header('Location: ' . url('/inscription.php'));
        exit;
    }

    // -----------------------------------------------------------------
    // 2. Récupération et nettoyage des données
    // -----------------------------------------------------------------
    $donnees = [
        'nom'            => trim($_POST['nom']            ?? ''),
        'prenom'         => trim($_POST['prenom']         ?? ''),
        'date_naissance' => trim($_POST['date_naissance'] ?? ''),
        'email'          => trim($_POST['email']          ?? ''),
        'login'          => trim($_POST['login']          ?? ''),
        // Adresse postale (exigée par le cahier des charges)
        'rue'            => trim($_POST['rue']            ?? ''),
        'numero'         => trim($_POST['numero']         ?? ''),
        'cp'             => trim($_POST['cp']             ?? ''),
        'ville'          => trim($_POST['ville']          ?? ''),
        'pays'           => trim($_POST['pays']           ?? 'Belgique'),
    ];
    $motPasse        = $_POST['mot_passe']         ?? '';
    $motPasseConfirm = $_POST['mot_passe_confirm'] ?? '';

    // -----------------------------------------------------------------
    // 3. Validation
    // -----------------------------------------------------------------

    // Nom
    if ($donnees['nom'] === '') {
        $erreurs['nom'] = 'Le nom est obligatoire.';
    } elseif (mb_strlen($donnees['nom']) > 60) {
        $erreurs['nom'] = 'Le nom ne doit pas dépasser 60 caractères.';
    }

    // Prénom
    if ($donnees['prenom'] === '') {
        $erreurs['prenom'] = 'Le prénom est obligatoire.';
    } elseif (mb_strlen($donnees['prenom']) > 60) {
        $erreurs['prenom'] = 'Le prénom ne doit pas dépasser 60 caractères.';
    }

    // Date de naissance
    if ($donnees['date_naissance'] === '') {
        $erreurs['date_naissance'] = 'La date de naissance est obligatoire.';
    } else {
        // Format YYYY-MM-DD attendu (type="date" HTML5)
        $dt = DateTime::createFromFormat('Y-m-d', $donnees['date_naissance']);
        if (!$dt || $dt->format('Y-m-d') !== $donnees['date_naissance']) {
            $erreurs['date_naissance'] = 'Date de naissance invalide.';
        } elseif ($dt > new DateTime()) {
            $erreurs['date_naissance'] = 'La date de naissance ne peut pas être dans le futur.';
        }
    }

    // Email
    if ($donnees['email'] === '') {
        $erreurs['email'] = 'L\'email est obligatoire.';
    } elseif (!filter_var($donnees['email'], FILTER_VALIDATE_EMAIL)) {
        $erreurs['email'] = 'L\'email n\'est pas dans un format valide.';
    } elseif (mb_strlen($donnees['email']) > 150) {
        $erreurs['email'] = 'L\'email est trop long.';
    } elseif (Membre::emailExiste($donnees['email'])) {
        $erreurs['email'] = 'Cet email est déjà utilisé.';
    }

    // Login
    if ($donnees['login'] === '') {
        $erreurs['login'] = 'Le login est obligatoire.';
    } elseif (!preg_match('/^[a-zA-Z0-9_-]{3,50}$/', $donnees['login'])) {
        $erreurs['login'] = 'Le login doit faire 3 à 50 caractères : lettres, chiffres, _ ou - uniquement.';
    } elseif (Membre::loginExiste($donnees['login'])) {
        $erreurs['login'] = 'Ce login est déjà pris.';
    }

    // Mot de passe
    if ($motPasse === '') {
        $erreurs['mot_passe'] = 'Le mot de passe est obligatoire.';
    } elseif (mb_strlen($motPasse) < 8) {
        $erreurs['mot_passe'] = 'Le mot de passe doit faire au moins 8 caractères.';
    } elseif ($motPasse !== $motPasseConfirm) {
        $erreurs['mot_passe_confirm'] = 'Les mots de passe ne correspondent pas.';
    }

    // Adresse postale (cahier des charges : obligatoire à l'inscription)
    if ($donnees['rue'] === '') {
        $erreurs['rue'] = 'La rue est obligatoire.';
    }
    if ($donnees['numero'] === '') {
        $erreurs['numero'] = 'Le numéro est obligatoire.';
    }
    if ($donnees['cp'] === '') {
        $erreurs['cp'] = 'Le code postal est obligatoire.';
    }
    if ($donnees['ville'] === '') {
        $erreurs['ville'] = 'La ville est obligatoire.';
    }
    if ($donnees['pays'] === '') {
        $erreurs['pays'] = 'Le pays est obligatoire.';
    }

    // -----------------------------------------------------------------
    // 4. Upload d'avatar (optionnel)
    // -----------------------------------------------------------------
    $nomAvatar = null;
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
        $resultatUpload = Upload::image($_FILES['avatar'], UPLOADS_PATH . '/avatars');
        if (!$resultatUpload['succes']) {
            $erreurs['avatar'] = $resultatUpload['erreur'];
        } else {
            $nomAvatar = $resultatUpload['fichier'];
        }
    }

    // -----------------------------------------------------------------
    // 5. Si tout est OK → création du compte
    // -----------------------------------------------------------------
    if (empty($erreurs)) {
        $donnees['mot_passe'] = $motPasse;
        $donnees['avatar']    = $nomAvatar;

        try {
            $idNouveauMembre = Membre::creer($donnees);

            // Créer l'adresse par défaut (cahier des charges)
            Adresse::creer($idNouveauMembre, [
                'libelle'    => 'Adresse principale',
                'nom'        => $donnees['nom'],
                'prenom'     => $donnees['prenom'],
                'rue'        => $donnees['rue'],
                'numero'     => $donnees['numero'],
                'complement' => null,
                'cp'         => $donnees['cp'],
                'ville'      => $donnees['ville'],
                'pays'       => $donnees['pays'],
                'telephone'  => null,
                'type'       => 'les_deux',
                'est_defaut' => 1,
            ]);

            // Connexion automatique après inscription
            $nouveauMembre = Membre::trouverParId($idNouveauMembre);
            Auth::connecter($nouveauMembre);

            Flash::succes('Inscription réussie ! Bienvenue, ' . h($donnees['prenom']) . '.');
            header('Location: ' . url('/index.php'));
            exit;

        } catch (Throwable $e) {
            // En cas d'erreur BDD, on supprime l'avatar uploadé (rollback)
            if ($nomAvatar) {
                Upload::supprimer($nomAvatar, UPLOADS_PATH . '/avatars');
            }
            $erreurs['general'] = 'Erreur lors de la création du compte. Veuillez réessayer.';
            if (DEV_MODE) {
                $erreurs['general'] .= ' [DEV] ' . $e->getMessage();
            }
        }
    } else {
        // Si l'upload avait réussi mais qu'il y a d'autres erreurs : nettoyer
        if ($nomAvatar) {
            Upload::supprimer($nomAvatar, UPLOADS_PATH . '/avatars');
        }
    }
}


// =====================================================================
// AFFICHAGE
// =====================================================================
$titre = 'Inscription';
require_once VIEWS_PATH . '/auth/inscription.php';
