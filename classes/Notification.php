<?php
/**
 * classes/Notification.php
 * ---------------------------------------------------------------------
 * Notifications dans l'application (cloche de la navbar).
 *
 * Types prévus :
 *   - 'commande.statut'      : changement de statut d'une commande
 *   - 'commentaire.reponse'  : quelqu'un a répondu / liké votre commentaire
 *   - 'compte.bloque'        : message de l'admin
 * ---------------------------------------------------------------------
 */

class Notification
{
    /**
     * Crée une notification pour un membre.
     */
    public static function creer(int $idMembre, string $type, string $titre, string $message, ?string $urlCible = null): int
    {
        $pdo = Db::pdo();
        $req = $pdo->prepare(
            "INSERT INTO notification (id_membre, type, titre, message, url_cible)
             VALUES (?, ?, ?, ?, ?)"
        );
        $req->execute([$idMembre, $type, $titre, $message, $urlCible]);
        return (int)$pdo->lastInsertId();
    }


    /**
     * Liste les notifications d'un membre.
     */
    public static function listerDuMembre(int $idMembre, int $limite = 20, bool $nonLuesSeulement = false): array
    {
        $where = $nonLuesSeulement ? "AND lue = 0" : '';
        $req = Db::pdo()->prepare(
            "SELECT * FROM notification
             WHERE id_membre = ? $where
             ORDER BY date_creation DESC
             LIMIT ?"
        );
        $req->bindValue(1, $idMembre, PDO::PARAM_INT);
        $req->bindValue(2, $limite,   PDO::PARAM_INT);
        $req->execute();
        return $req->fetchAll();
    }


    /**
     * Compte les notifications non lues d'un membre (pour le badge).
     */
    public static function nbNonLues(int $idMembre): int
    {
        $req = Db::pdo()->prepare(
            "SELECT COUNT(*) FROM notification WHERE id_membre = ? AND lue = 0"
        );
        $req->execute([$idMembre]);
        return (int)$req->fetchColumn();
    }


    /**
     * Marque une notification comme lue.
     */
    public static function marquerLue(int $idNotification, int $idMembre): bool
    {
        $req = Db::pdo()->prepare(
            "UPDATE notification
             SET lue = 1, date_lecture = NOW()
             WHERE id_notification = ? AND id_membre = ?"
        );
        return $req->execute([$idNotification, $idMembre]);
    }


    /**
     * Marque toutes les notifications d'un membre comme lues.
     */
    public static function marquerToutesLues(int $idMembre): int
    {
        $req = Db::pdo()->prepare(
            "UPDATE notification
             SET lue = 1, date_lecture = NOW()
             WHERE id_membre = ? AND lue = 0"
        );
        $req->execute([$idMembre]);
        return $req->rowCount();
    }


    /**
     * Trouve une notification par ID, vérifiant qu'elle appartient au membre.
     */
    public static function trouverParId(int $idNotification, int $idMembre): ?array
    {
        $req = Db::pdo()->prepare(
            "SELECT * FROM notification
             WHERE id_notification = ? AND id_membre = ?"
        );
        $req->execute([$idNotification, $idMembre]);
        $row = $req->fetch();
        return $row ?: null;
    }
}
