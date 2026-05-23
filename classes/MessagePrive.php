<?php
/**
 * classes/MessagePrive.php
 * ---------------------------------------------------------------------
 * Messagerie privée entre membres.
 *
 * Concepts :
 *  - Une "conversation" = ensemble de messages échangés entre 2 membres
 *  - Le "thread" = vue chronologique d'une conversation
 *  - Blocage : un membre peut bloquer un autre, qui ne pourra plus
 *    lui envoyer de MP. Le blocage est unidirectionnel.
 *
 * Sécurité :
 *  - Un admin peut TOUJOURS écrire à n'importe qui (modération)
 *  - Un membre ne peut pas s'écrire à lui-même
 *  - Vérification de propriété à chaque accès (un membre ne voit que
 *    les threads dont il est partie prenante)
 * ---------------------------------------------------------------------
 */

class MessagePrive
{
    /**
     * Liste les conversations d'un membre.
     * Une conversation est groupée par interlocuteur (l'autre membre).
     *
     * Retourne pour chaque interlocuteur :
     *  - id_membre, login, prenom, nom, avatar, statut, date_anonymisation
     *  - dernier_message, dernier_message_date, dernier_expediteur
     *  - nb_non_lus (depuis ce contact)
     */
    public static function listerConversations(int $idMembre): array
    {
        // Pour chaque "autre" membre avec qui il y a eu échange, on prend
        // le dernier message et le nombre de non lus envoyés PAR lui.
        $req = Db::pdo()->prepare(
            "SELECT
                u.id_membre, u.login, u.prenom, u.nom, u.avatar, u.statut,
                u.date_anonymisation,
                (SELECT mp2.corps
                 FROM message_prive mp2
                 WHERE (mp2.id_expediteur = u.id_membre AND mp2.id_destinataire = :me1)
                    OR (mp2.id_expediteur = :me2 AND mp2.id_destinataire = u.id_membre)
                 ORDER BY mp2.date_envoi DESC LIMIT 1) AS dernier_message,
                (SELECT mp3.date_envoi
                 FROM message_prive mp3
                 WHERE (mp3.id_expediteur = u.id_membre AND mp3.id_destinataire = :me3)
                    OR (mp3.id_expediteur = :me4 AND mp3.id_destinataire = u.id_membre)
                 ORDER BY mp3.date_envoi DESC LIMIT 1) AS dernier_message_date,
                (SELECT mp4.id_expediteur
                 FROM message_prive mp4
                 WHERE (mp4.id_expediteur = u.id_membre AND mp4.id_destinataire = :me5)
                    OR (mp4.id_expediteur = :me6 AND mp4.id_destinataire = u.id_membre)
                 ORDER BY mp4.date_envoi DESC LIMIT 1) AS dernier_expediteur,
                (SELECT COUNT(*)
                 FROM message_prive mp5
                 WHERE mp5.id_expediteur = u.id_membre
                   AND mp5.id_destinataire = :me7
                   AND mp5.lu = 0) AS nb_non_lus
             FROM membre u
             WHERE u.id_membre != :me8
               AND u.id_membre IN (
                   SELECT id_expediteur FROM message_prive WHERE id_destinataire = :me9
                   UNION
                   SELECT id_destinataire FROM message_prive WHERE id_expediteur = :me10
               )
             ORDER BY dernier_message_date DESC"
        );
        $params = [];
        for ($i = 1; $i <= 10; $i++) $params[":me$i"] = $idMembre;
        $req->execute($params);
        return $req->fetchAll();
    }


    /**
     * Liste tous les messages d'un thread (entre 2 membres).
     * Marque automatiquement comme lus les messages reçus.
     */
    public static function listerThread(int $idMembre, int $idAutre): array
    {
        // Récupérer le thread
        $req = Db::pdo()->prepare(
            "SELECT mp.*, u.login AS exp_login, u.prenom AS exp_prenom,
                    u.avatar AS exp_avatar, u.date_anonymisation AS exp_anonyme
             FROM message_prive mp
             INNER JOIN membre u ON u.id_membre = mp.id_expediteur
             WHERE (mp.id_expediteur = ? AND mp.id_destinataire = ?)
                OR (mp.id_expediteur = ? AND mp.id_destinataire = ?)
             ORDER BY mp.date_envoi ASC"
        );
        $req->execute([$idMembre, $idAutre, $idAutre, $idMembre]);
        $messages = $req->fetchAll();

        // Marquer comme lus les messages reçus
        $reqLu = Db::pdo()->prepare(
            "UPDATE message_prive
             SET lu = 1
             WHERE id_expediteur = ? AND id_destinataire = ? AND lu = 0"
        );
        $reqLu->execute([$idAutre, $idMembre]);

        return $messages;
    }


    /**
     * Envoie un message privé.
     * Vérifie l'absence de blocage (sauf si admin).
     *
     * @return array ['succes' => bool, 'erreur' => string|null, 'id' => int|null]
     */
    public static function envoyer(int $idExpediteur, int $idDestinataire, string $corps, ?string $sujet = null): array
    {
        $corps = trim($corps);

        if ($corps === '') {
            return ['succes' => false, 'erreur' => 'Le message ne peut pas être vide.'];
        }
        if (strlen($corps) > 5000) {
            return ['succes' => false, 'erreur' => 'Message trop long (max 5000 caractères).'];
        }
        if ($idExpediteur === $idDestinataire) {
            return ['succes' => false, 'erreur' => 'Impossible de s\'envoyer un message à soi-même.'];
        }

        // Vérifier que le destinataire existe et est actif
        $destinataire = Membre::trouverParId($idDestinataire);
        if (!$destinataire || !empty($destinataire['date_anonymisation'])) {
            return ['succes' => false, 'erreur' => 'Destinataire introuvable.'];
        }

        // Vérifier le blocage (sauf si admin)
        if (!Auth::estAdmin() && self::estBloque($idExpediteur, $idDestinataire)) {
            // Message volontairement vague (anti-énumération)
            return ['succes' => false, 'erreur' => 'Vous ne pouvez pas envoyer de message à ce membre.'];
        }

        $pdo = Db::pdo();
        $req = $pdo->prepare(
            "INSERT INTO message_prive (id_expediteur, id_destinataire, sujet, corps)
             VALUES (?, ?, ?, ?)"
        );
        $req->execute([
            $idExpediteur,
            $idDestinataire,
            $sujet ?: null,
            $corps,
        ]);
        $id = (int)$pdo->lastInsertId();

        // Notification au destinataire
        if (class_exists('Notification')) {
            $expediteur = Membre::trouverParId($idExpediteur);
            $nomExp = $expediteur['prenom'] . ' ' . $expediteur['nom'];
            Notification::creer(
                $idDestinataire,
                'mp.recu',
                'Nouveau message de ' . $nomExp,
                substr($corps, 0, 100) . (strlen($corps) > 100 ? '…' : ''),
                '/messages_thread.php?with=' . $idExpediteur
            );
        }

        return ['succes' => true, 'erreur' => null, 'id' => $id];
    }


    /**
     * Compte les messages non lus reçus par un membre.
     */
    public static function nbNonLus(int $idMembre): int
    {
        $req = Db::pdo()->prepare(
            "SELECT COUNT(*) FROM message_prive
             WHERE id_destinataire = ? AND lu = 0"
        );
        $req->execute([$idMembre]);
        return (int)$req->fetchColumn();
    }


    /**
     * Vrai si $idExpediteur est bloqué par $idDestinataire.
     */
    public static function estBloque(int $idExpediteur, int $idDestinataire): bool
    {
        $req = Db::pdo()->prepare(
            "SELECT COUNT(*) FROM mp_blocage
             WHERE id_membre = ? AND id_membre_bloque = ?"
        );
        $req->execute([$idDestinataire, $idExpediteur]);
        return $req->fetchColumn() > 0;
    }


    /**
     * Bloque un membre (l'empêche de m'écrire).
     */
    public static function bloquer(int $idMembre, int $idMembreBloque): bool
    {
        if ($idMembre === $idMembreBloque) return false;
        $req = Db::pdo()->prepare(
            "INSERT IGNORE INTO mp_blocage (id_membre, id_membre_bloque)
             VALUES (?, ?)"
        );
        return $req->execute([$idMembre, $idMembreBloque]);
    }


    /**
     * Débloque un membre.
     */
    public static function debloquer(int $idMembre, int $idMembreBloque): bool
    {
        $req = Db::pdo()->prepare(
            "DELETE FROM mp_blocage
             WHERE id_membre = ? AND id_membre_bloque = ?"
        );
        return $req->execute([$idMembre, $idMembreBloque]);
    }


    /**
     * Supprime toute une conversation (les 2 sens) entre deux membres.
     * Utilisable par chacun des 2 protagonistes.
     */
    public static function supprimerThread(int $idMembre, int $idAutre): int
    {
        $req = Db::pdo()->prepare(
            "DELETE FROM message_prive
             WHERE (id_expediteur = ? AND id_destinataire = ?)
                OR (id_expediteur = ? AND id_destinataire = ?)"
        );
        $req->execute([$idMembre, $idAutre, $idAutre, $idMembre]);
        return $req->rowCount();
    }


    /**
     * Liste les membres bloqués par un membre.
     */
    public static function listerBloques(int $idMembre): array
    {
        $req = Db::pdo()->prepare(
            "SELECT u.id_membre, u.login, u.prenom, u.nom, u.avatar,
                    b.date_blocage
             FROM mp_blocage b
             INNER JOIN membre u ON u.id_membre = b.id_membre_bloque
             WHERE b.id_membre = ?
             ORDER BY b.date_blocage DESC"
        );
        $req->execute([$idMembre]);
        return $req->fetchAll();
    }


    /**
     * Recherche des membres pour démarrer une conversation (autocomplete).
     * Exclut soi-même + membres anonymisés/bloqués/qui m'ont bloqué.
     */
    public static function rechercherDestinataires(int $idMembreCourant, string $terme, int $limite = 10): array
    {
        $terme = trim($terme);
        if (strlen($terme) < 2) return [];

        $req = Db::pdo()->prepare(
            "SELECT id_membre, login, prenom, nom, avatar
             FROM membre
             WHERE id_membre != ?
               AND date_anonymisation IS NULL
               AND indesirable = 0
               AND (login LIKE ? OR prenom LIKE ? OR nom LIKE ?)
               AND id_membre NOT IN (
                   SELECT id_membre_bloque FROM mp_blocage WHERE id_membre = ?
               )
               AND id_membre NOT IN (
                   SELECT id_membre FROM mp_blocage WHERE id_membre_bloque = ?
               )
             ORDER BY login ASC
             LIMIT ?"
        );
        $like = '%' . $terme . '%';
        $req->bindValue(1, $idMembreCourant, PDO::PARAM_INT);
        $req->bindValue(2, $like);
        $req->bindValue(3, $like);
        $req->bindValue(4, $like);
        $req->bindValue(5, $idMembreCourant, PDO::PARAM_INT);
        $req->bindValue(6, $idMembreCourant, PDO::PARAM_INT);
        $req->bindValue(7, $limite, PDO::PARAM_INT);
        $req->execute();
        return $req->fetchAll();
    }
}
