<?php
/**
 * public/admin/article_supprimer.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : "Suppression" d'un article (dispo=0).
 * On ne hard-delete jamais car les factures référencent l'article.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verifierRequete()) {
    header('Location: ' . url('/admin/articles.php'));
    exit;
}

$idArticle = (int)($_POST['id_article'] ?? 0);
if ($idArticle <= 0) {
    Flash::erreur('Article introuvable.');
    header('Location: ' . url('/admin/articles.php'));
    exit;
}

Article::supprimer($idArticle);
AuditLog::enregistrer('article.supprimer', Auth::id(), 'article', $idArticle);
Flash::succes('Article retiré du catalogue (les commandes existantes restent valides).');

header('Location: ' . url('/admin/articles.php'));
exit;
