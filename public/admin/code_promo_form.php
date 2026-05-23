<?php
/**
 * public/admin/code_promo_form.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Créer ou modifier un code promo.
 *
 * - Sans paramètre id → création
 * - Avec ?id=X        → édition
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

$idCode = (int)($_GET['id'] ?? 0);
$modeEdition = $idCode > 0;

$codePromo = null;
if ($modeEdition) {
    $codePromo = CodePromo::trouverParId($idCode);
    if (!$codePromo) {
        Flash::erreur('Code promo introuvable.');
        header('Location: ' . url('/admin/codes_promo.php'));
        exit;
    }
}

// Valeurs par défaut / pré-remplissage
$donnees = [
    'code'                    => $modeEdition ? $codePromo['code'] : '',
    'description'             => $modeEdition ? ($codePromo['description'] ?? '') : '',
    'type_remise'             => $modeEdition ? $codePromo['type_remise'] : 'pourcentage',
    'valeur'                  => $modeEdition ? $codePromo['valeur'] : '',
    'montant_min_panier'      => $modeEdition ? $codePromo['montant_min_panier'] : '0',
    'utilisations_max'        => $modeEdition ? ($codePromo['utilisations_max'] ?? '') : '',
    'utilisations_par_membre' => $modeEdition ? ($codePromo['utilisations_par_membre'] ?? '1') : '1',
    'date_debut'              => $modeEdition ? substr($codePromo['date_debut'], 0, 16) : date('Y-m-d\TH:i'),
    'date_fin'                => $modeEdition ? substr($codePromo['date_fin'], 0, 16) : date('Y-m-d\TH:i', strtotime('+1 month')),
    'actif'                   => $modeEdition ? (int)$codePromo['actif'] : 1,
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

    // Nettoyage des données
    $donnees['code']                    = strtoupper(trim($_POST['code']                    ?? ''));
    $donnees['description']             = trim($_POST['description']             ?? '');
    $donnees['type_remise']             = $_POST['type_remise']                  ?? 'pourcentage';
    $donnees['valeur']                  = trim($_POST['valeur']                  ?? '');
    $donnees['montant_min_panier']      = trim($_POST['montant_min_panier']      ?? '0');
    $donnees['utilisations_max']        = trim($_POST['utilisations_max']        ?? '');
    $donnees['utilisations_par_membre'] = trim($_POST['utilisations_par_membre'] ?? '');
    $donnees['date_debut']              = $_POST['date_debut']                   ?? '';
    $donnees['date_fin']                = $_POST['date_fin']                     ?? '';
    $donnees['actif']                   = !empty($_POST['actif']) ? 1 : 0;

    // -----------------------------------------------------------------
    // Validation
    // -----------------------------------------------------------------

    // Code
    if ($donnees['code'] === '') {
        $erreurs['code'] = 'Le code est obligatoire.';
    } elseif (!preg_match('/^[A-Z0-9_-]{3,40}$/', $donnees['code'])) {
        $erreurs['code'] = 'Le code doit faire 3 à 40 caractères : lettres MAJ, chiffres, _ ou -.';
    } elseif (CodePromo::codeExiste($donnees['code'], $idCode)) {
        $erreurs['code'] = 'Ce code existe déjà.';
    }

    // Type de remise
    if (!in_array($donnees['type_remise'], ['pourcentage', 'montant_fixe', 'livraison_offerte'], true)) {
        $erreurs['type_remise'] = 'Type de remise invalide.';
    }

    // Valeur
    if ($donnees['type_remise'] === 'livraison_offerte') {
        $donnees['valeur'] = 0;
    } else {
        $valeur = (float)str_replace(',', '.', $donnees['valeur']);
        if ($valeur <= 0) {
            $erreurs['valeur'] = 'La valeur doit être > 0.';
        } elseif ($donnees['type_remise'] === 'pourcentage' && $valeur > 100) {
            $erreurs['valeur'] = 'Le pourcentage ne peut pas dépasser 100 %.';
        }
        $donnees['valeur'] = $valeur;
    }

    // Montant minimum panier
    $minPanier = (float)str_replace(',', '.', $donnees['montant_min_panier']);
    if ($minPanier < 0) {
        $erreurs['montant_min_panier'] = 'Le montant minimum doit être ≥ 0.';
    }
    $donnees['montant_min_panier'] = $minPanier;

    // Utilisations max (NULL si vide)
    if ($donnees['utilisations_max'] === '') {
        $donnees['utilisations_max'] = null;
    } else {
        $val = (int)$donnees['utilisations_max'];
        if ($val < 1) {
            $erreurs['utilisations_max'] = 'Doit être ≥ 1 ou vide pour illimité.';
        }
        $donnees['utilisations_max'] = $val;
    }

    // Utilisations par membre (NULL si vide)
    if ($donnees['utilisations_par_membre'] === '') {
        $donnees['utilisations_par_membre'] = null;
    } else {
        $val = (int)$donnees['utilisations_par_membre'];
        if ($val < 1) {
            $erreurs['utilisations_par_membre'] = 'Doit être ≥ 1 ou vide pour illimité.';
        }
        $donnees['utilisations_par_membre'] = $val;
    }

    // Dates
    if ($donnees['date_debut'] === '') {
        $erreurs['date_debut'] = 'La date de début est obligatoire.';
    }
    if ($donnees['date_fin'] === '') {
        $erreurs['date_fin'] = 'La date de fin est obligatoire.';
    }
    if (empty($erreurs['date_debut']) && empty($erreurs['date_fin'])) {
        if (strtotime($donnees['date_fin']) <= strtotime($donnees['date_debut'])) {
            $erreurs['date_fin'] = 'La date de fin doit être après la date de début.';
        }
    }

    // -----------------------------------------------------------------
    // Si tout est OK → enregistrement
    // -----------------------------------------------------------------
    if (empty($erreurs)) {
        try {
            if ($modeEdition) {
                CodePromo::modifier($idCode, $donnees);
                AuditLog::enregistrer('code_promo.modifier', Auth::id(), 'code_promo', $idCode, [
                    'code' => $donnees['code'],
                ]);
                Flash::succes('Code promo « ' . h($donnees['code']) . ' » mis à jour.');
            } else {
                $idCode = CodePromo::creer($donnees);
                AuditLog::enregistrer('code_promo.creer', Auth::id(), 'code_promo', $idCode, [
                    'code' => $donnees['code'],
                ]);
                Flash::succes('Code promo « ' . h($donnees['code']) . ' » créé.');
            }
            header('Location: ' . url('/admin/codes_promo.php'));
            exit;
        } catch (Throwable $e) {
            $erreurs['general'] = 'Erreur lors de l\'enregistrement.';
            if (DEV_MODE) {
                $erreurs['general'] .= ' [' . $e->getMessage() . ']';
            }
        }
    }
}

$titre = $modeEdition ? 'Modifier le code promo' : 'Nouveau code promo';

// Pour l'avertissement en mode édition (déplacé hors de la vue par respect MVC)
$nbUtilisations = $modeEdition ? CodePromo::nbUtilisations($idCode) : 0;

require_once VIEWS_PATH . '/admin/code_promo_form.php';
