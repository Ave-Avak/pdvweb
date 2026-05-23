<?php
/**
 * public/admin/billet_form.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Créer ou éditer un billet.
 *
 * - Sans paramètre id → création
 * - Avec ?id=X        → édition du billet X
 *
 * Supporte : titre, corps (Markdown), résumé court, image illustrative,
 * tags multiples.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

// Mode édition ou création
$idBillet = (int)($_GET['id'] ?? 0);
$modeEdition = $idBillet > 0;

$billet = null;
$idsTagsActuels = [];

if ($modeEdition) {
    $billet = Billet::trouverParId($idBillet);
    if (!$billet) {
        Flash::erreur('Billet introuvable.');
        header('Location: ' . url('/admin/billets.php'));
        exit;
    }
    $idsTagsActuels = array_map(fn($t) => (int)$t['id_tag'], Billet::tagsDe($idBillet));
}

// Variables pour pré-remplissage du formulaire
$donnees = [
    'titre'  => $modeEdition ? $billet['titre']  : '',
    'corps'  => $modeEdition ? $billet['corps']  : '',
    'resume' => $modeEdition ? ($billet['resume'] ?? '') : '',
];
$imageActuelle = $modeEdition ? ($billet['image'] ?? null) : null;
$idsTagsSelectionnes = $idsTagsActuels;
$erreurs = [];


// =====================================================================
// TRAITEMENT DU POST
// =====================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!Csrf::verifierRequete()) {
        Flash::erreur('Session expirée.');
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }

    $donnees['titre']     = trim($_POST['titre']  ?? '');
    $donnees['corps']     = trim($_POST['corps']  ?? '');
    $donnees['resume']    = trim($_POST['resume'] ?? '');
    $idsTagsSelectionnes  = array_map('intval', $_POST['tags'] ?? []);
    $supprimerImage       = !empty($_POST['supprimer_image']);

    // Validation
    if ($donnees['titre'] === '') {
        $erreurs['titre'] = 'Le titre est obligatoire.';
    } elseif (mb_strlen($donnees['titre']) > 200) {
        $erreurs['titre'] = 'Le titre ne doit pas dépasser 200 caractères.';
    }

    if ($donnees['corps'] === '') {
        $erreurs['corps'] = 'Le contenu est obligatoire.';
    }

    if (mb_strlen($donnees['resume']) > 500) {
        $erreurs['resume'] = 'Le résumé ne doit pas dépasser 500 caractères.';
    }

    // -----------------------------------------------------------------
    // Gestion de l'image illustrative (optionnelle)
    // -----------------------------------------------------------------
    $nouvelleImage = $imageActuelle;
    $uploadEffectue = false;

    if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        $res = Upload::image($_FILES['image'], UPLOADS_PATH . '/articles');
        if (!$res['succes']) {
            $erreurs['image'] = $res['erreur'];
        } else {
            // Supprimer l'ancienne image si remplacement
            if ($imageActuelle) {
                Upload::supprimer($imageActuelle, UPLOADS_PATH . '/articles');
            }
            $nouvelleImage = $res['fichier'];
            $uploadEffectue = true;
        }
    } elseif ($supprimerImage && $imageActuelle) {
        // Suppression demandée explicitement
        Upload::supprimer($imageActuelle, UPLOADS_PATH . '/articles');
        $nouvelleImage = null;
    }

    if (empty($erreurs)) {
        try {
            if ($modeEdition) {
                Billet::modifier(
                    $idBillet,
                    $donnees['titre'],
                    $donnees['corps'],
                    $idsTagsSelectionnes,
                    $donnees['resume'],
                    $nouvelleImage
                );
                AuditLog::enregistrer('billet.modifier', Auth::id(), 'billet', $idBillet, [
                    'titre' => $donnees['titre'],
                ]);
                Flash::succes('Billet mis à jour.');
            } else {
                $idBillet = Billet::creer(
                    Auth::id(),
                    $donnees['titre'],
                    $donnees['corps'],
                    $idsTagsSelectionnes,
                    $donnees['resume'],
                    $nouvelleImage
                );
                AuditLog::enregistrer('billet.creer', Auth::id(), 'billet', $idBillet, [
                    'titre' => $donnees['titre'],
                ]);
                Flash::succes('Billet créé avec succès.');
            }
            header('Location: ' . url('/admin/billets.php'));
            exit;
        } catch (Throwable $e) {
            // En cas d'erreur, supprimer l'image qui aurait été uploadée
            if ($uploadEffectue) {
                Upload::supprimer($nouvelleImage, UPLOADS_PATH . '/articles');
            }
            $erreurs['general'] = 'Erreur lors de l\'enregistrement.';
            if (DEV_MODE) {
                $erreurs['general'] .= ' [' . $e->getMessage() . ']';
            }
        }
    }
}

$tousLesTags = Tag::listerTous();

$titre = $modeEdition ? 'Modifier un billet' : 'Nouveau billet';
require_once VIEWS_PATH . '/admin/billet_form.php';
