<?php
/**
 * public/panier_add.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Ajout d'un article au panier.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verifierRequete()) {
    header('Location: ' . url('/catalogue.php'));
    exit;
}

$idArticle = (int)($_POST['id_article'] ?? 0);
$quantite  = max(1, (int)($_POST['quantite'] ?? 1));
$retour    = retour_securise($_POST['retour'] ?? null, url('/catalogue.php'));

$resultat = Panier::ajouter($idArticle, $quantite);

if ($resultat['succes']) {
    Flash::succes($resultat['message']);
} else {
    Flash::erreur($resultat['message']);
}

header('Location: ' . $retour);
exit;
