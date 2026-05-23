<?php
/**
 * public/admin/tag_form.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Créer ou modifier un tag.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

$idTag = (int)($_GET['id'] ?? 0);
$modeEdition = $idTag > 0;

$tag = null;
if ($modeEdition) {
    $tag = Tag::trouverParId($idTag);
    if (!$tag) {
        Flash::erreur('Tag introuvable.');
        header('Location: ' . url('/admin/tags.php'));
        exit;
    }
}

$donnees = [
    'code' => $modeEdition ? $tag['code'] : '',
    'nom'  => $modeEdition ? $tag['nom']  : '',
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

    $donnees['code'] = strtolower(trim($_POST['code'] ?? ''));
    $donnees['nom']  = trim($_POST['nom']  ?? '');

    // Validation
    if ($donnees['code'] === '') {
        $erreurs['code'] = 'Le code est obligatoire.';
    } elseif (!preg_match('/^[a-z0-9-_]{2,40}$/', $donnees['code'])) {
        $erreurs['code'] = 'Le code doit faire 2 à 40 caractères : minuscules, chiffres, - ou _.';
    } elseif (Tag::codeExiste($donnees['code'], $idTag)) {
        $erreurs['code'] = 'Ce code existe déjà.';
    }

    if ($donnees['nom'] === '') {
        $erreurs['nom'] = 'Le nom est obligatoire.';
    } elseif (mb_strlen($donnees['nom']) > 60) {
        $erreurs['nom'] = 'Le nom ne doit pas dépasser 60 caractères.';
    }

    // Enregistrement
    if (empty($erreurs)) {
        try {
            if ($modeEdition) {
                Tag::modifier($idTag, $donnees);
                AuditLog::enregistrer('tag.modifier', Auth::id(), 'tag', $idTag, [
                    'code' => $donnees['code'], 'nom' => $donnees['nom'],
                ]);
                Flash::succes('Tag « ' . h($donnees['nom']) . ' » mis à jour.');
            } else {
                $idTag = Tag::creer($donnees);
                AuditLog::enregistrer('tag.creer', Auth::id(), 'tag', $idTag, [
                    'code' => $donnees['code'], 'nom' => $donnees['nom'],
                ]);
                Flash::succes('Tag « ' . h($donnees['nom']) . ' » créé.');
            }
            header('Location: ' . url('/admin/tags.php'));
            exit;
        } catch (Throwable $e) {
            $erreurs['general'] = 'Erreur lors de l\'enregistrement.';
            if (DEV_MODE) {
                $erreurs['general'] .= ' [' . $e->getMessage() . ']';
            }
        }
    }
}

$titre = $modeEdition ? 'Modifier le tag' : 'Nouveau tag';
require_once VIEWS_PATH . '/admin/tag_form.php';
