<?php
/**
 * classes/Tag.php
 * ---------------------------------------------------------------------
 * Modèle "Tag" : étiquettes associables aux billets.
 * ---------------------------------------------------------------------
 */

class Tag
{
    /**
     * Liste tous les tags, triés par nom.
     */
    public static function listerTous(): array
    {
        return Db::pdo()->query(
            "SELECT t.*,
                    (SELECT COUNT(*) FROM billet_tag WHERE id_tag = t.id_tag) AS nb_billets
             FROM tag t
             ORDER BY t.nom"
        )->fetchAll();
    }

    /**
     * Trouve par ID.
     */
    public static function trouverParId(int $id): ?array
    {
        $req = Db::pdo()->prepare("SELECT * FROM tag WHERE id_tag = ?");
        $req->execute([$id]);
        $row = $req->fetch();
        return $row ?: null;
    }

    /**
     * Trouve par code (slug).
     */
    public static function trouverParCode(string $code): ?array
    {
        $req = Db::pdo()->prepare("SELECT * FROM tag WHERE code = ?");
        $req->execute([$code]);
        $row = $req->fetch();
        return $row ?: null;
    }

    /**
     * Vérifie si un code existe déjà.
     */
    public static function codeExiste(string $code, int $idIgnore = 0): bool
    {
        $req = Db::pdo()->prepare(
            "SELECT COUNT(*) FROM tag WHERE code = ? AND id_tag <> ?"
        );
        $req->execute([$code, $idIgnore]);
        return (int)$req->fetchColumn() > 0;
    }

    /**
     * Crée un tag.
     *
     * @param array $donnees ['code' => slug, 'nom' => libellé]
     * @return int ID du tag créé
     */
    public static function creer(array $donnees): int
    {
        $pdo = Db::pdo();
        $req = $pdo->prepare("INSERT INTO tag (code, nom) VALUES (?, ?)");
        $req->execute([$donnees['code'], $donnees['nom']]);
        return (int)$pdo->lastInsertId();
    }

    /**
     * Modifie un tag.
     */
    public static function modifier(int $idTag, array $donnees): bool
    {
        $req = Db::pdo()->prepare(
            "UPDATE tag SET code = ?, nom = ? WHERE id_tag = ?"
        );
        return $req->execute([$donnees['code'], $donnees['nom'], $idTag]);
    }

    /**
     * Supprime un tag.
     * Les associations dans billet_tag sont supprimées via ON DELETE CASCADE.
     */
    public static function supprimer(int $idTag): bool
    {
        $req = Db::pdo()->prepare("DELETE FROM tag WHERE id_tag = ?");
        return $req->execute([$idTag]);
    }
}
