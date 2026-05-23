<?php
/**
 * public/panier_add.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Ajout d'un article au panier.
 *
 * Supporte deux modes :
 *   - AJAX (avec header X-Requested-With) → réponse JSON, pas de redirect
 *   - Classique (sans JS) → redirection vers $retour (fallback graceful)
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

// Détection AJAX (header envoyé par fetch() côté JS)
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
$quantite  = max(1, (int)($_POST['quantite'] ?? 1));
$retour    = retour_securise($_POST['retour'] ?? null, url('/catalogue.php'));

$resultat = Panier::ajouter($idArticle, $quantite);

// === Réponse AJAX ===
if ($estAjax) {
    header('Content-Type: application/json');
    echo json_encode([
        'succes'       => $resultat['succes'],
        'message'      => $resultat['message'],
        'nb_articles'  => Panier::nbArticles(),
    ]);
    exit;
}

// === Réponse classique (compatibilité, sans JS) ===
if ($resultat['succes']) {
    Flash::succes($resultat['message']);
} else {
    Flash::erreur($resultat['message']);
}

header('Location: ' . $retour);
exit;
