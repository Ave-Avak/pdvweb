<?php
/**
 * public/note_add.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR : Création/mise à jour d'un avis sur un article.
 *
 * Requiert : membre connecté ET ayant acheté l'article.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../includes/bootstrap.php';

Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verifierRequete()) {
    header('Location: ' . url('/catalogue.php'));
    exit;
}

$idArticle   = (int)($_POST['id_article'] ?? 0);
$note        = (int)($_POST['note'] ?? 0);
$commentaire = trim($_POST['commentaire'] ?? '');

if ($idArticle <= 0 || $note < 1 || $note > 5) {
    Flash::erreur('Données invalides.');
    header('Location: ' . url('/article.php?id=' . $idArticle));
    exit;
}

// Vérification : le membre doit avoir acheté cet article
if (!NoteArticle::aAcheteArticle(Auth::id(), $idArticle)) {
    Flash::erreur('Vous devez avoir acheté cet article pour le noter.');
    header('Location: ' . url('/article.php?id=' . $idArticle));
    exit;
}

NoteArticle::enregistrer(Auth::id(), $idArticle, $note,
                          $commentaire !== '' ? $commentaire : null);
Flash::succes('Merci pour votre avis !');

header('Location: ' . url('/article.php?id=' . $idArticle . '#avis'));
exit;
