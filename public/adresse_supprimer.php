<?php
/**
 * public/adresse_supprimer.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Suppression d'une adresse du membre.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verifierRequete()) {
    header('Location: ' . url('/adresses.php'));
    exit;
}

$idAdresse = (int)($_POST['id_adresse'] ?? 0);
if ($idAdresse <= 0) {
    Flash::erreur('Adresse invalide.');
    header('Location: ' . url('/adresses.php'));
    exit;
}

// Vérification de propriété
if (!Adresse::appartientAuMembre($idAdresse, Auth::id())) {
    Flash::erreur('Cette adresse ne vous appartient pas.');
    header('Location: ' . url('/adresses.php'));
    exit;
}

Adresse::supprimer($idAdresse, Auth::id());
Flash::succes('Adresse supprimée.');

header('Location: ' . url('/adresses.php'));
exit;
