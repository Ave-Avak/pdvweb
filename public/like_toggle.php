<?php
/**
 * public/like_toggle.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Toggle d'un like (sur billet ou commentaire).
 * Supporte AJAX (JSON) et redirect classique.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

Auth::requireLogin();

$estAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    if ($estAjax) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['succes' => false, 'message' => 'Méthode invalide.']);
        exit;
    }
    header('Location: ' . url('/blog.php'));
    exit;
}

if (!Csrf::verifierRequete()) {
    if ($estAjax) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['succes' => false, 'message' => 'Session expirée.']);
        exit;
    }
    Flash::erreur('Session expirée.');
    header('Location: ' . url('/blog.php'));
    exit;
}

$type     = $_POST['type'] ?? '';
$idCible  = (int)($_POST['id_cible'] ?? 0);
$idBillet = (int)($_POST['id_billet'] ?? 0);

if (!LikeContenu::typeEstValide($type) || $idCible <= 0) {
    if ($estAjax) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['succes' => false, 'message' => 'Paramètres invalides.']);
        exit;
    }
    Flash::erreur('Paramètres invalides.');
    header('Location: ' . url('/blog.php'));
    exit;
}

$ajoute = LikeContenu::toggle(Auth::id(), $type, $idCible);

if ($estAjax) {
    header('Content-Type: application/json');
    echo json_encode([
        'succes'   => true,
        'ajoute'   => $ajoute,
        'nb_likes' => LikeContenu::compter($type, $idCible),
    ]);
    exit;
}

if ($idBillet > 0) {
    $ancre = $type === 'commentaire' ? '#c' . $idCible : '';
    header('Location: ' . url('/billet.php?id=' . $idBillet . $ancre));
} else {
    header('Location: ' . url('/blog.php'));
}
exit;
