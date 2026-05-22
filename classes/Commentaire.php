<?php
/**
 * classes/Commentaire.php
 * ---------------------------------------------------------------------
 * Modèle "Commentaire" : commentaires postés par les UM sur les billets.
 * ---------------------------------------------------------------------
 */

class Commentaire
{
    /**
     * Liste tous les commentaires d'un billet, avec infos auteur.
     */
    public static function listerParBillet(int $idBillet): array
    {
        $req = Db::pdo()->prepare(
            "SELECT c.*,
                    u.id_membre, u.prenom, u.nom, u.login, u.avatar, u.statut,
                    (SELECT COUNT(*) FROM like_contenu
                     WHERE type = 'commentaire' AND id_cible = c.id_commentaire) AS nb_likes
             FROM commentaire c
             INNER JOIN membre u ON u.id_membre = c.id_membre
             WHERE c.id_billet = ?
             ORDER BY c.date_comm ASC"
        );
        $req->execute([$idBillet]);
        return $req->fetchAll();
    }

    /**
     * Recherche un commentaire par son ID.
     */
    public static function trouverParId(int $idCommentaire): ?array
    {
        $req = Db::pdo()->prepare(
            "SELECT * FROM commentaire WHERE id_commentaire = ?"
        );
        $req->execute([$idCommentaire]);
        $row = $req->fetch();
        return $row ?: null;
    }

    /**
     * Liste les N derniers commentaires d'un membre (vue admin).
     */
    public static function dernierDe(int $idMembre, int $limite = 5): array
    {
        $req = Db::pdo()->prepare(
            "SELECT c.*, b.titre AS titre_billet
             FROM commentaire c
             INNER JOIN billet b ON b.id_billet = c.id_billet
             WHERE c.id_membre = ?
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
     * Modifie un commentaire.
     */
    public static function modifier(int $idCommentaire, string $corps): bool
    {
        $req = Db::pdo()->prepare(
            "UPDATE commentaire SET corps = ? WHERE id_commentaire = ?"
        );
        return $req->execute([$corps, $idCommentaire]);
    }

    /**
     * Supprime un commentaire (les likes associés sont nettoyés par la requête séparée
     * — like_contenu n'a pas de FK vers commentaire car le like est polymorphe).
     */
    public static function supprimer(int $idCommentaire): bool
    {
        $pdo = Db::pdo();
        // Nettoyer les likes manuellement (la table like_contenu est polymorphe)
        $pdo->prepare("DELETE FROM like_contenu WHERE type = 'commentaire' AND id_cible = ?")
            ->execute([$idCommentaire]);
        return $pdo->prepare("DELETE FROM commentaire WHERE id_commentaire = ?")
                   ->execute([$idCommentaire]);
    }

    /**
     * Compte total (stats admin).
     */
    public static function compterTous(): int
    {
        return (int)Db::pdo()->query("SELECT COUNT(*) FROM commentaire")->fetchColumn();
    }
}
