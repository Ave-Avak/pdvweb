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
}
