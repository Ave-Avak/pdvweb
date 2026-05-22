<?php
/**
 * classes/Favori.php
 * ---------------------------------------------------------------------
 * Modèle "Favori" : articles favoris d'un membre.
 * Clé composite (id_membre, id_article) = un favori unique par paire.
 * ---------------------------------------------------------------------
 */

class Favori
{
    /**
     * Un membre a-t-il mis cet article en favori ?
     */
    public static function aFavori(int $idMembre, int $idArticle): bool
    {
        $req = Db::pdo()->prepare(
            "SELECT COUNT(*) FROM favori WHERE id_membre = ? AND id_article = ?"
        );
        $req->execute([$idMembre, $idArticle]);
        return $req->fetchColumn() > 0;
    }

    /**
     * Toggle : ajoute ou retire l'article des favoris.
     * Retourne true si maintenant favori, false sinon.
     */
    public static function toggle(int $idMembre, int $idArticle): bool
    {
        if (self::aFavori($idMembre, $idArticle)) {
            Db::pdo()->prepare(
                "DELETE FROM favori WHERE id_membre = ? AND id_article = ?"
            )->execute([$idMembre, $idArticle]);
            return false;
        }
        Db::pdo()->prepare(
            "INSERT INTO favori (id_membre, id_article) VALUES (?, ?)"
        )->execute([$idMembre, $idArticle]);
        return true;
    }

    /**
     * Liste les articles favoris d'un membre.
     */
    public static function listerDuMembre(int $idMembre): array
    {
        $req = Db::pdo()->prepare(
            "SELECT a.*, c.nom AS categorie_nom, f.date_ajout AS date_favori
             FROM favori f
             INNER JOIN article a ON a.id_article = f.id_article
             INNER JOIN categorie c ON c.id_categorie = a.id_categorie
             WHERE f.id_membre = ? AND a.dispo = 1
             ORDER BY f.date_ajout DESC"
        );
        $req->execute([$idMembre]);
        return $req->fetchAll();
    }
}
