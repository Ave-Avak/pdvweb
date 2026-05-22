<?php
/**
 * classes/Adresse.php
 * ---------------------------------------------------------------------
 * Modèle "Adresse" : adresses de livraison/facturation des membres.
 *
 * Un membre peut avoir plusieurs adresses (domicile, bureau, etc.).
 * À la commande, on choisit une adresse de livraison et une de facturation.
 * ---------------------------------------------------------------------
 */

class Adresse
{
    /**
     * Liste les adresses d'un membre.
     */
    public static function listerDuMembre(int $idMembre): array
    {
        $req = Db::pdo()->prepare(
            "SELECT * FROM adresse
             WHERE id_membre = ?
             ORDER BY est_defaut DESC, libelle"
        );
        $req->execute([$idMembre]);
        return $req->fetchAll();
    }

    /**
     * Trouve par ID.
     */
    public static function trouverParId(int $id): ?array
    {
        $req = Db::pdo()->prepare("SELECT * FROM adresse WHERE id_adresse = ?");
        $req->execute([$id]);
        $row = $req->fetch();
        return $row ?: null;
    }

    /**
     * Récupère l'adresse par défaut d'un membre (ou la première, ou null).
     */
    public static function defautDuMembre(int $idMembre): ?array
    {
        $req = Db::pdo()->prepare(
            "SELECT * FROM adresse
             WHERE id_membre = ?
             ORDER BY est_defaut DESC, id_adresse ASC
             LIMIT 1"
        );
        $req->execute([$idMembre]);
        $row = $req->fetch();
        return $row ?: null;
    }

    /**
     * Crée une nouvelle adresse.
     */
    public static function creer(int $idMembre, array $donnees): int
    {
        $pdo = Db::pdo();

        // Si est_defaut = 1, on retire le défaut des autres adresses du membre
        if (!empty($donnees['est_defaut'])) {
            $pdo->prepare("UPDATE adresse SET est_defaut = 0 WHERE id_membre = ?")
                ->execute([$idMembre]);
        }

        $req = $pdo->prepare(
            "INSERT INTO adresse
                (id_membre, libelle, nom, prenom, rue, numero, complement,
                 cp, ville, pays, telephone, type, est_defaut)
             VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $req->execute([
            $idMembre,
            $donnees['libelle'],
            $donnees['nom'],
            $donnees['prenom'],
            $donnees['rue'],
            $donnees['numero'],
            $donnees['complement'] ?? null,
            $donnees['cp'],
            $donnees['ville'],
            $donnees['pays']       ?? 'Belgique',
            $donnees['telephone']  ?? null,
            $donnees['type']       ?? 'les_deux',
            $donnees['est_defaut'] ?? 0,
        ]);

        return (int)$pdo->lastInsertId();
    }

    /**
     * Vérifie qu'une adresse appartient bien à un membre (sécurité).
     */
    public static function appartientAuMembre(int $idAdresse, int $idMembre): bool
    {
        $req = Db::pdo()->prepare(
            "SELECT COUNT(*) FROM adresse
             WHERE id_adresse = ? AND id_membre = ?"
        );
        $req->execute([$idAdresse, $idMembre]);
        return $req->fetchColumn() > 0;
    }

    /**
     * Supprime une adresse (hard delete OK car peu sensible).
     */
    public static function supprimer(int $idAdresse, int $idMembre): bool
    {
        $req = Db::pdo()->prepare(
            "DELETE FROM adresse WHERE id_adresse = ? AND id_membre = ?"
        );
        return $req->execute([$idAdresse, $idMembre]);
    }
}
