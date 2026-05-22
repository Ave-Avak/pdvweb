<?php
/**
 * classes/Categorie.php
 * ---------------------------------------------------------------------
 * Modèle "Categorie" : catégories d'articles.
 * Le cahier des charges impose 3 catégories : informatique, livre, hi-fi.
 * ---------------------------------------------------------------------
 */

class Categorie
{
    /**
     * Liste toutes les catégories triées.
     */
    public static function listerTous(): array
    {
        return Db::pdo()->query(
            "SELECT c.*,
                    (SELECT COUNT(*) FROM article WHERE id_categorie = c.id_categorie AND dispo = 1) AS nb_articles
             FROM categorie c
             ORDER BY c.ordre, c.nom"
        )->fetchAll();
    }

    /**
     * Trouve par ID.
     */
    public static function trouverParId(int $id): ?array
    {
        $req = Db::pdo()->prepare("SELECT * FROM categorie WHERE id_categorie = ?");
        $req->execute([$id]);
        $row = $req->fetch();
        return $row ?: null;
    }

    /**
     * Trouve par code (slug).
     */
    public static function trouverParCode(string $code): ?array
    {
        $req = Db::pdo()->prepare("SELECT * FROM categorie WHERE code = ?");
        $req->execute([$code]);
        $row = $req->fetch();
        return $row ?: null;
    }
}
