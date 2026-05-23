<?php
/**
 * public/admin/categorie_form.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Création/édition d'une catégorie.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

$idCategorie = (int)($_GET['id'] ?? 0);
$modeEdition = $idCategorie > 0;

$categorie = null;
if ($modeEdition) {
    $categorie = Categorie::trouverParId($idCategorie);
    if (!$categorie) {
        Flash::erreur('Catégorie introuvable.');
        header('Location: ' . url('/admin/categories.php'));
        exit;
    }
}

$donnees = [
    'code'        => $categorie['code']        ?? '',
    'nom'         => $categorie['nom']         ?? '',
    'description' => $categorie['description'] ?? '',
    'ordre'       => (int)($categorie['ordre'] ?? 0),
];
$erreurs = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifierRequete()) {
        Flash::erreur('Session expirée.');
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }

    $donnees['code']        = strtolower(trim($_POST['code'] ?? ''));
    $donnees['nom']         = trim($_POST['nom'] ?? '');
    $donnees['description'] = trim($_POST['description'] ?? '');
    $donnees['ordre']       = (int)($_POST['ordre'] ?? 0);

    // Validation
    if ($donnees['code'] === '')                       $erreurs['code'] = 'Le code est obligatoire.';
    elseif (!preg_match('/^[a-z0-9_-]+$/', $donnees['code'])) {
        $erreurs['code'] = 'Le code doit contenir uniquement lettres minuscules, chiffres, tirets et underscores.';
    }
    elseif (Categorie::codeExiste($donnees['code'], $idCategorie)) {
        $erreurs['code'] = 'Ce code est déjà utilisé.';
    }
    if ($donnees['nom'] === '') $erreurs['nom'] = 'Le nom est obligatoire.';

    if (empty($erreurs)) {
        try {
            if ($modeEdition) {
                Categorie::modifier($idCategorie, $donnees);
                AuditLog::enregistrer('categorie.modifier', Auth::id(), 'categorie', $idCategorie, [
                    'nom' => $donnees['nom'],
                ]);
                Flash::succes('Catégorie mise à jour.');
            } else {
                $idCategorie = Categorie::creer($donnees);
                AuditLog::enregistrer('categorie.creer', Auth::id(), 'categorie', $idCategorie, [
                    'nom' => $donnees['nom'],
                ]);
                Flash::succes('Catégorie créée.');
            }
            header('Location: ' . url('/admin/categories.php'));
            exit;
        } catch (Throwable $e) {
            $erreurs['general'] = 'Erreur d\'enregistrement.';
            if (DEV_MODE) $erreurs['general'] .= ' [' . $e->getMessage() . ']';
        }
    }
}

$titre = $modeEdition ? 'Modifier la catégorie' : 'Nouvelle catégorie';
require_once VIEWS_PATH . '/admin/categorie_form.php';
