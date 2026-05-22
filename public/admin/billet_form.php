<?php
/**
 * public/admin/billet_form.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Créer ou éditer un billet.
 *
 * - Sans paramètre id → création
 * - Avec ?id=X        → édition du billet X
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
    'titre' => $modeEdition ? $billet['titre'] : '',
    'corps' => $modeEdition ? $billet['corps'] : '',
];
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

    $donnees['titre']     = trim($_POST['titre'] ?? '');
    $donnees['corps']     = trim($_POST['corps'] ?? '');
    $idsTagsSelectionnes  = array_map('intval', $_POST['tags'] ?? []);

    // Validation
    if ($donnees['titre'] === '') {
        $erreurs['titre'] = 'Le titre est obligatoire.';
    } elseif (mb_strlen($donnees['titre']) > 200) {
        $erreurs['titre'] = 'Le titre ne doit pas dépasser 200 caractères.';
    }

    if ($donnees['corps'] === '') {
        $erreurs['corps'] = 'Le contenu est obligatoire.';
    }

    if (empty($erreurs)) {
        try {
            if ($modeEdition) {
                Billet::modifier($idBillet, $donnees['titre'], $donnees['corps'], $idsTagsSelectionnes);
                Flash::succes('Billet mis à jour.');
            } else {
                $idBillet = Billet::creer(Auth::id(), $donnees['titre'], $donnees['corps'], $idsTagsSelectionnes);
                Flash::succes('Billet créé avec succès.');
            }
            header('Location: ' . url('/admin/billets.php'));
            exit;
        } catch (Throwable $e) {
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
