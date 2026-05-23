<?php
/**
 * public/admin/corbeille.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Corbeille - billets et commentaires supprimés.
 * Permet la restauration.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

// Traitement de la restauration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verifierRequete()) {
        Flash::erreur('Session expirée.');
        header('Location: ' . url('/admin/corbeille.php'));
        exit;
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'restaurer_billet') {
        $idBillet = (int)($_POST['id_billet'] ?? 0);
        if (Billet::restaurer($idBillet)) {
            AuditLog::enregistrer('billet.restaurer', Auth::id(), 'billet', $idBillet);
            Flash::succes('Billet restauré.');
        }
    } elseif ($action === 'restaurer_commentaire') {
        $idCommentaire = (int)($_POST['id_commentaire'] ?? 0);
        if (Commentaire::restaurer($idCommentaire)) {
            AuditLog::enregistrer('commentaire.restaurer', Auth::id(), 'commentaire', $idCommentaire);
            Flash::succes('Commentaire restauré.');
        }
    }

    header('Location: ' . url('/admin/corbeille.php'));
    exit;
}

// Récupération des éléments supprimés
$billetsSupprimes = Billet::listerSupprimes();

// Récupération des commentaires supprimés
$reqComm = Db::pdo()->query(
    "SELECT c.*, b.titre AS titre_billet,
            u.prenom, u.nom,
            s.prenom AS supp_prenom, s.nom AS supp_nom
     FROM commentaire c
     INNER JOIN billet b ON b.id_billet = c.id_billet
     INNER JOIN membre u ON u.id_membre = c.id_membre
     LEFT JOIN membre s ON s.id_membre = c.id_membre_suppression
     WHERE c.date_suppression IS NOT NULL
     ORDER BY c.date_suppression DESC"
);
$commentairesSupprimes = $reqComm->fetchAll();

$titre = 'Administration — Corbeille';
require_once VIEWS_PATH . '/admin/corbeille.php';
