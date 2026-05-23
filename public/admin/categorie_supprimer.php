<?php
/**
 * public/admin/categorie_supprimer.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Suppression d'une catégorie (si vide).
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verifierRequete()) {
    header('Location: ' . url('/admin/categories.php'));
    exit;
}

$idCategorie = (int)($_POST['id_categorie'] ?? 0);
if ($idCategorie <= 0) {
    Flash::erreur('Catégorie invalide.');
    header('Location: ' . url('/admin/categories.php'));
    exit;
}

if (!Categorie::estVide($idCategorie)) {
    Flash::erreur('Impossible : la catégorie contient encore des articles. Déplacez-les avant suppression.');
    header('Location: ' . url('/admin/categories.php'));
    exit;
}

Categorie::supprimer($idCategorie);
AuditLog::enregistrer('categorie.supprimer', Auth::id(), 'categorie', $idCategorie);
Flash::succes('Catégorie supprimée.');

header('Location: ' . url('/admin/categories.php'));
exit;
