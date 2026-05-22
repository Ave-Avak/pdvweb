<?php
/**
 * classes/Commentaire.php
 * ---------------------------------------------------------------------
 * Modèle "Commentaire" : commentaires postés par les UM sur les billets.
 *
 * IMPORTANT : implémente le SOFT DELETE.
 *   - supprimer() marque le commentaire comme supprimé sans l'effacer
 *   - Toutes les listes filtrent date_suppression IS NULL
 * ---------------------------------------------------------------------
 */

class Commentaire
{
    /**
     * Liste les commentaires d'un billet (uniquement les non-supprimés).
     */
    public static function listerParBillet(int $idBillet): array
    {
        $req = Db::pdo()->prepare(
            "SELECT c.*,
                    u.id_membre, u.prenom, u.nom, u.login, u.avatar, u.statut,
                    u.date_anonymisation,
                    (SELECT COUNT(*) FROM like_contenu
                     WHERE type = 'commentaire' AND id_cible = c.id_commentaire) AS nb_likes
             FROM commentaire c
             INNER JOIN membre u ON u.id_membre = c.id_membre
             WHERE c.id_billet = ?
               AND c.date_suppression IS NULL
             ORDER BY c.date_comm ASC"
        );
        $req->execute([$idBillet]);
        return $req->fetchAll();
    }

    /**
     * Recherche un commentaire par son ID (même s'il est supprimé,
     * utile pour les vérifications de droit avant restauration).
     */
    public static function trouverParId(int $idCommentaire, bool $inclureSupprimes = false): ?array
    {
        $clauseSupp = $inclureSupprimes ? '' : ' AND date_suppression IS NULL';
        $req = Db::pdo()->prepare(
            "SELECT * FROM commentaire WHERE id_commentaire = ?" . $clauseSupp
        );
        $req->execute([$idCommentaire]);
        $row = $req->fetch();
        return $row ?: null;
    }

    /**
     * Liste les commentaires d'un membre (admin only).
     */
    public static function dernierDe(int $idMembre, int $limite = 5): array
    {
        $req = Db::pdo()->prepare(
            "SELECT c.*, b.titre AS titre_billet
             FROM commentaire c
             INNER JOIN billet b ON b.id_billet = c.id_billet
             WHERE c.id_membre = ?
               AND c.date_suppression IS NULL
             ORDER BY c.date_comm DESC
             LIMIT ?"
        );
        $req->bindValue(1, $idMembre, PDO::PARAM_INT);
        $req->bindValue(2, $limite,   PDO::PARAM_INT);
        $req->execute();
        return $req->fetchAll();
    }

    /**
     * Crée un nouveau commentaire.
     */
    public static function creer(int $idBillet, int $idMembre, string $corps): int
    {
        $pdo = Db::pdo();
        $req = $pdo->prepare(
            "INSERT INTO commentaire (id_billet, id_membre, corps) VALUES (?, ?, ?)"
        );
        $req->execute([$idBillet, $idMembre, $corps]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Modifie un commentaire (réservé à l'AUTEUR uniquement).
     * L'admin n'a PAS le droit de modifier (intégrité du discours).
     */
    public static function modifier(int $idCommentaire, string $corps): bool
    {
        $req = Db::pdo()->prepare(
            "UPDATE commentaire SET corps = ? WHERE id_commentaire = ?"
        );
        return $req->execute([$corps, $idCommentaire]);
    }

    /**
     * SUPPRIME (soft) un commentaire.
     *
     * @param int $idCommentaire
     * @param int $idMembreSupp ID du membre qui supprime (pour audit)
     */
    public static function supprimer(int $idCommentaire, int $idMembreSupp): bool
    {
        $req = Db::pdo()->prepare(
            "UPDATE commentaire
             SET date_suppression = NOW(), id_membre_suppression = ?
             WHERE id_commentaire = ? AND date_suppression IS NULL"
        );
        return $req->execute([$idMembreSupp, $idCommentaire]);
    }

    /**
     * Restaure un commentaire supprimé.
     */
    public static function restaurer(int $idCommentaire): bool
    {
        $req = Db::pdo()->prepare(
            "UPDATE commentaire
             SET date_suppression = NULL, id_membre_suppression = NULL
             WHERE id_commentaire = ?"
        );
        return $req->execute([$idCommentaire]);
    }

    /**
     * Compte les commentaires actifs (non supprimés).
     */
    public static function compterTous(): int
    {
        return (int)Db::pdo()->query(
            "SELECT COUNT(*) FROM commentaire WHERE date_suppression IS NULL"
        )->fetchColumn();
    }
}
