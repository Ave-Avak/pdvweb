<?php
/**
 * public/favori_toggle.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Ajoute/retire un article des favoris.
 * Supporte AJAX (JSON) et redirect classique.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

Auth::requireLogin();

$estAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verifierRequete()) {
    if ($estAjax) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['succes' => false, 'message' => 'Requête invalide.']);
        exit;
    }
    header('Location: ' . url('/catalogue.php'));
    exit;
}

$idArticle = (int)($_POST['id_article'] ?? 0);
$retour    = retour_securise($_POST['retour'] ?? null, url('/article.php?id=' . $idArticle));

if ($idArticle <= 0) {
    if ($estAjax) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['succes' => false, 'message' => 'Article introuvable.']);
        exit;
    }
    header('Location: ' . url('/catalogue.php'));
    exit;
}

$ajoute = Favori::toggle(Auth::id(), $idArticle);

if ($estAjax) {
    header('Content-Type: application/json');
    echo json_encode([
        'succes'  => true,
        'ajoute'  => $ajoute,
        'message' => $ajoute ? 'Ajouté aux favoris.' : 'Retiré des favoris.',
    ]);
    exit;
}

Flash::succes($ajoute ? 'Ajouté aux favoris.' : 'Retiré des favoris.');
header('Location: ' . $retour);
exit;
