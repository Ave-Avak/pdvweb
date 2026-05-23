<?php
/**
 * public/favori_toggle.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Ajoute/retire un article des favoris.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verifierRequete()) {
    header('Location: ' . url('/catalogue.php'));
    exit;
}

$idArticle = (int)($_POST['id_article'] ?? 0);
$retour    = retour_securise($_POST['retour'] ?? null, url('/article.php?id=' . $idArticle));

if ($idArticle <= 0) {
    header('Location: ' . url('/catalogue.php'));
    exit;
}

$ajoute = Favori::toggle(Auth::id(), $idArticle);
Flash::succes($ajoute ? 'Ajouté aux favoris.' : 'Retiré des favoris.');

header('Location: ' . $retour);
exit;
