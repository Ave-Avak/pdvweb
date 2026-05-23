<?php
/**
 * public/admin/frais_port_form.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Créer ou modifier une grille de frais de port.
 *
 * - Sans paramètre id → création
 * - Avec ?id=X        → édition
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

$idFrais = (int)($_GET['id'] ?? 0);
$modeEdition = $idFrais > 0;

$frais = null;
if ($modeEdition) {
    $frais = FraisPort::trouverParId($idFrais);
    if (!$frais) {
        Flash::erreur('Grille tarifaire introuvable.');
        header('Location: ' . url('/admin/frais_port.php'));
        exit;
    }
}

$donnees = [
    'nom'                => $modeEdition ? $frais['nom'] : '',
    'pays'               => $modeEdition ? $frais['pays'] : 'Belgique',
    'montant_min_panier' => $modeEdition ? ($frais['montant_min_panier'] ?? '') : '',
    'montant_max_panier' => $modeEdition ? ($frais['montant_max_panier'] ?? '') : '',
    'prix'               => $modeEdition ? $frais['prix'] : '',
    'delai_jours'        => $modeEdition ? ($frais['delai_jours'] ?? '') : '',
    'actif'              => $modeEdition ? (int)$frais['actif'] : 1,
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

    $donnees['nom']                = trim($_POST['nom']                ?? '');
    $donnees['pays']               = trim($_POST['pays']               ?? '');
    $donnees['montant_min_panier'] = trim($_POST['montant_min_panier'] ?? '');
    $donnees['montant_max_panier'] = trim($_POST['montant_max_panier'] ?? '');
    $donnees['prix']               = trim($_POST['prix']               ?? '');
    $donnees['delai_jours']        = trim($_POST['delai_jours']        ?? '');
    $donnees['actif']              = !empty($_POST['actif']) ? 1 : 0;

    // -----------------------------------------------------------------
    // Validation
    // -----------------------------------------------------------------

    if ($donnees['nom'] === '') {
        $erreurs['nom'] = 'Le nom est obligatoire.';
    } elseif (mb_strlen($donnees['nom']) > 80) {
        $erreurs['nom'] = 'Le nom ne doit pas dépasser 80 caractères.';
    }

    if ($donnees['pays'] === '') {
        $erreurs['pays'] = 'Le pays est obligatoire.';
    } elseif (mb_strlen($donnees['pays']) > 60) {
        $erreurs['pays'] = 'Le pays ne doit pas dépasser 60 caractères.';
    }

    // Prix
    if ($donnees['prix'] === '') {
        $erreurs['prix'] = 'Le prix est obligatoire (0 pour livraison offerte).';
    } else {
        $prixFloat = (float)str_replace(',', '.', $donnees['prix']);
        if ($prixFloat < 0) {
            $erreurs['prix'] = 'Le prix doit être positif ou nul.';
        }
        $donnees['prix'] = $prixFloat;
    }

    // Montants min/max
    if ($donnees['montant_min_panier'] !== '') {
        $val = (float)str_replace(',', '.', $donnees['montant_min_panier']);
        if ($val < 0) {
            $erreurs['montant_min_panier'] = 'Doit être positif ou nul.';
        }
        $donnees['montant_min_panier'] = $val;
    }
    if ($donnees['montant_max_panier'] !== '') {
        $val = (float)str_replace(',', '.', $donnees['montant_max_panier']);
        if ($val < 0) {
            $erreurs['montant_max_panier'] = 'Doit être positif ou nul.';
        }
        $donnees['montant_max_panier'] = $val;
    }
    if (is_numeric($donnees['montant_min_panier']) && is_numeric($donnees['montant_max_panier'])
        && (float)$donnees['montant_max_panier'] < (float)$donnees['montant_min_panier']) {
        $erreurs['montant_max_panier'] = 'Le montant maximum doit être supérieur au minimum.';
    }

    // Délai jours
    if ($donnees['delai_jours'] !== '') {
        $val = (int)$donnees['delai_jours'];
        if ($val < 0) {
            $erreurs['delai_jours'] = 'Doit être positif ou nul.';
        }
        $donnees['delai_jours'] = $val;
    }

    // -----------------------------------------------------------------
    // Enregistrement
    // -----------------------------------------------------------------
    if (empty($erreurs)) {
        try {
            if ($modeEdition) {
                FraisPort::modifier($idFrais, $donnees);
                AuditLog::enregistrer('frais_port.modifier', Auth::id(), 'frais_port', $idFrais, [
                    'nom' => $donnees['nom'], 'pays' => $donnees['pays'],
                ]);
                Flash::succes('Grille tarifaire « ' . h($donnees['nom']) . ' » mise à jour.');
            } else {
                $idFrais = FraisPort::creer($donnees);
                AuditLog::enregistrer('frais_port.creer', Auth::id(), 'frais_port', $idFrais, [
                    'nom' => $donnees['nom'], 'pays' => $donnees['pays'],
                ]);
                Flash::succes('Grille tarifaire « ' . h($donnees['nom']) . ' » créée.');
            }
            header('Location: ' . url('/admin/frais_port.php'));
            exit;
        } catch (Throwable $e) {
            $erreurs['general'] = 'Erreur lors de l\'enregistrement.';
            if (DEV_MODE) {
                $erreurs['general'] .= ' [' . $e->getMessage() . ']';
            }
        }
    }
}

$titre = $modeEdition ? 'Modifier la grille tarifaire' : 'Nouvelle grille tarifaire';
require_once VIEWS_PATH . '/admin/frais_port_form.php';
