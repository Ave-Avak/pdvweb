<?php
/**
 * public/panier_update.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Mise à jour des quantités du panier.
 * Reçoit un tableau $_POST['quantites'][id_article] = nouvelle_qte.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verifierRequete()) {
    header('Location: ' . url('/panier.php'));
    exit;
}

$quantites = $_POST['quantites'] ?? [];
if (!is_array($quantites)) {
    header('Location: ' . url('/panier.php'));
    exit;
}

$erreurs = [];
$majOk = 0;

foreach ($quantites as $idArticle => $qte) {
    $idArticle = (int)$idArticle;
    $qte       = (int)$qte;

    if ($idArticle <= 0) continue;

    $resultat = Panier::modifierQuantite($idArticle, $qte);
    if ($resultat['succes']) {
        $majOk++;
    } elseif ($resultat['message']) {
        $erreurs[] = $resultat['message'];
    }
}

if (!empty($erreurs)) {
    Flash::erreur(implode(' ', array_unique($erreurs)));
} elseif ($majOk > 0) {
    Flash::succes('Panier mis à jour.');
}

header('Location: ' . url('/panier.php'));
exit;
