<?php
/**
 * public/admin/membre_detail.php
 * ---------------------------------------------------------------------
 * CONTRÔLEUR ADMIN : Fiche détaillée d'un membre.
 *
 * Affiche : infos, stats de connexion, commandes, commentaires.
 * ---------------------------------------------------------------------
 */

require_once __DIR__ . '/../../includes/bootstrap.php';

Auth::requireAdmin();

$idMembre = (int)($_GET['id'] ?? 0);
if ($idMembre <= 0) {
    Flash::erreur('Membre introuvable.');
    header('Location: ' . url('/admin/membres.php'));
    exit;
}

$membre = Membre::trouverParId($idMembre);
if (!$membre) {
    Flash::erreur('Ce membre n\'existe pas.');
    header('Location: ' . url('/admin/membres.php'));
    exit;
}

// Stats de connexion
$nbCnxJ1  = Membre::nbConnexions($idMembre, 1);
$nbCnxJ7  = Membre::nbConnexions($idMembre, 7);
$nbCnxJ30 = Membre::nbConnexions($idMembre, 30);

// Commandes du membre
$commandes = Facture::listerDuMembre($idMembre);

// Commentaires du membre (sans soft-deleted)
$req = Db::pdo()->prepare(
    "SELECT c.*, b.titre AS billet_titre, b.id_billet
     FROM commentaire c
     INNER JOIN billet b ON b.id_billet = c.id_billet
     WHERE c.id_membre = ? AND c.date_suppression IS NULL
     ORDER BY c.date_comm DESC
     LIMIT 20"
);
$req->execute([$idMembre]);
$commentaires = $req->fetchAll();

// Adresses du membre
$adresses = Adresse::listerDuMembre($idMembre);

// 10 dernières connexions
$reqCnx = Db::pdo()->prepare(
    "SELECT date_log, ip FROM log_connexion
     WHERE id_membre = ?
     ORDER BY date_log DESC LIMIT 10"
);
$reqCnx->execute([$idMembre]);
$dernieresConnexions = $reqCnx->fetchAll();

$titre = 'Administration — ' . $membre['login'];
require_once VIEWS_PATH . '/admin/membre_detail.php';
