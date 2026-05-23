<?php
/**
 * classes/Minichat.php
 * ---------------------------------------------------------------------
 * Modèle "Minichat" : gestion des messages du mini-chat.
 *
 * Méthodes statiques pour rester cohérent avec le style de Membre.php.
 * ---------------------------------------------------------------------
 */

class Minichat
{
    /**
     * Longueur maximale d'un message (cahier des charges : 255).
     * Lue depuis la table parametre si disponible, sinon valeur par défaut.
     */
    public static function longueurMax(): int
    {
        return (int)parametre('minichat.longueur_max', 255);
    }

    /**
     * Nombre de messages à afficher (cahier des charges : 10).
     */
    public static function nbAffiches(): int
    {
        return (int)parametre('minichat.nb_messages', 10);
    }

    /**
     * Récupère les N derniers messages, du plus récent au plus ancien.
     * Inclut les infos du membre via une jointure.
     *
     * @param int $limite Nombre de messages à récupérer
     * @return array
     */
    public static function listerDerniers(int $limite = 10): array
    {
        $req = Db::pdo()->prepare(
            "SELECT m.id_message, m.message, m.date_message, m.pseudo,
                    u.id_membre, u.prenom, u.nom, u.login, u.avatar, u.statut,
                    u.date_anonymisation
             FROM minichat m
             INNER JOIN membre u ON u.id_membre = m.id_membre
             ORDER BY m.date_message DESC
             LIMIT ?"
        );
        // On bind ":limite" en INT car LIMIT ne supporte pas les params string
        $req->bindValue(1, $limite, PDO::PARAM_INT);
        $req->execute();
        return $req->fetchAll();
    }

    /**
     * Récupère le dernier message d'un membre (pour anti-spam basique).
     *
     * @param int $idMembre
     * @return array|null
     */
    public static function dernierMessageDe(int $idMembre): ?array
    {
        $req = Db::pdo()->prepare(
            "SELECT * FROM minichat
             WHERE id_membre = ?
             ORDER BY date_message DESC
             LIMIT 1"
        );
        $req->execute([$idMembre]);
        $row = $req->fetch();
        return $row ?: null;
    }

    /**
     * Crée un nouveau message.
     *
     * @param int    $idMembre
     * @param string $message
     * @param string $pseudo  Pseudo choisi par le membre pour cette session
     * @return int  ID du message créé
     */
    public static function creer(int $idMembre, string $message, string $pseudo): int
    {
        $pdo = Db::pdo();
        $req = $pdo->prepare(
            "INSERT INTO minichat (id_membre, message, pseudo) VALUES (?, ?, ?)"
        );
        $req->execute([$idMembre, $message, $pseudo]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Récupère un message par son ID.
     *
     * @param int $idMessage
     * @return array|null
     */
    public static function trouverParId(int $idMessage): ?array
    {
        $req = Db::pdo()->prepare("SELECT * FROM minichat WHERE id_message = ?");
        $req->execute([$idMessage]);
        $row = $req->fetch();
        return $row ?: null;
    }

    /**
     * Supprime un message.
     *
     * @param int $idMessage
     * @return bool
     */
    public static function supprimer(int $idMessage): bool
    {
        $req = Db::pdo()->prepare("DELETE FROM minichat WHERE id_message = ?");
        return $req->execute([$idMessage]);
    }

    /**
     * Compte le nombre total de messages d'un membre (stats admin).
     */
    public static function compterMessagesDe(int $idMembre): int
    {
        $req = Db::pdo()->prepare(
            "SELECT COUNT(*) FROM minichat WHERE id_membre = ?"
        );
        $req->execute([$idMembre]);
        return (int)$req->fetchColumn();
    }
}
