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
// Cahier des charges : "consulter les 5 derniers commentaires édités par un UM"
// On affiche 5 par défaut, avec option ?tous_commentaires=1 pour tout voir.
$voirTousCommentaires = !empty($_GET['tous_commentaires']);

// Total des commentaires (pour afficher "X commentaires au total")
$reqNb = Db::pdo()->prepare(
    "SELECT COUNT(*) FROM commentaire
     WHERE id_membre = ? AND date_suppression IS NULL"
);
$reqNb->execute([$idMembre]);
$nbTotalCommentaires = (int)$reqNb->fetchColumn();

// Limite selon le mode
$limiteCommentaires = $voirTousCommentaires ? 100 : 5;

$req = Db::pdo()->prepare(
    "SELECT c.*, b.titre AS billet_titre, b.id_billet
     FROM commentaire c
     INNER JOIN billet b ON b.id_billet = c.id_billet
     WHERE c.id_membre = ? AND c.date_suppression IS NULL
     ORDER BY c.date_comm DESC
     LIMIT ?"
);
$req->bindValue(1, $idMembre, PDO::PARAM_INT);
$req->bindValue(2, $limiteCommentaires, PDO::PARAM_INT);
$req->execute();
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

// Historique des pseudos utilisés dans le minichat (modération admin)
// Le membre peut changer de pseudo entre les sessions ; cette requête
// liste TOUS les pseudos qu'il a utilisés, avec le nombre de messages
// et la dernière date d'utilisation pour chacun.
$reqPseudos = Db::pdo()->prepare(
    "SELECT pseudo,
            COUNT(*) AS nb_messages,
            MIN(date_message) AS premiere_utilisation,
            MAX(date_message) AS derniere_utilisation
     FROM minichat
     WHERE id_membre = ? AND pseudo IS NOT NULL
     GROUP BY pseudo
     ORDER BY derniere_utilisation DESC"
);
$reqPseudos->execute([$idMembre]);
$pseudosMinichat = $reqPseudos->fetchAll();

$titre = 'Administration — ' . $membre['login'];
require_once VIEWS_PATH . '/admin/membre_detail.php';
