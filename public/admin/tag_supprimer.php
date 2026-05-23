<?php
/**
 * public/admin/tag_supprimer.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Supprime un tag.
 * Les associations billet_tag sont supprimées en cascade.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::verifierRequete()) {
    header('Location: ' . url('/admin/tags.php'));
    exit;
}

$idTag = (int)($_POST['id_tag'] ?? 0);
if ($idTag <= 0) {
    Flash::erreur('Tag introuvable.');
    header('Location: ' . url('/admin/tags.php'));
    exit;
}

$tag = Tag::trouverParId($idTag);
if (!$tag) {
    Flash::erreur('Tag introuvable.');
    header('Location: ' . url('/admin/tags.php'));
    exit;
}

try {
    Tag::supprimer($idTag);
    AuditLog::enregistrer('tag.supprimer', Auth::id(), 'tag', $idTag, [
        'code' => $tag['code'], 'nom' => $tag['nom'],
    ]);
    Flash::succes('Tag « ' . h($tag['nom']) . ' » supprimé.');
} catch (Throwable $e) {
    Flash::erreur('Erreur lors de la suppression.');
    if (DEV_MODE) {
        Flash::erreur($e->getMessage());
    }
}

header('Location: ' . url('/admin/tags.php'));
exit;
