<?php
/**
 * public/panier_remove.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Retire un article du panier.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verifierRequete()) {
    header('Location: ' . url('/panier.php'));
    exit;
}

$idArticle = (int)($_POST['id_article'] ?? 0);
$resultat = Panier::retirer($idArticle);

if ($resultat['succes']) {
    Flash::succes($resultat['message']);
}

header('Location: ' . url('/panier.php'));
exit;
