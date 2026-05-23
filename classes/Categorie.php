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
    /**
     * Liste TOUTES les catégories (actives + inactives), avec nombre d'articles.
     * Utilisé en administration.
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
     * Liste UNIQUEMENT les catégories actives (pour le catalogue public, l'accueil).
     */
    public static function listerActives(): array
    {
        return Db::pdo()->query(
            "SELECT c.*,
                    (SELECT COUNT(*) FROM article WHERE id_categorie = c.id_categorie AND dispo = 1) AS nb_articles
             FROM categorie c
             WHERE c.actif = 1
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


    /**
     * Crée une nouvelle catégorie.
     */
    public static function creer(array $donnees): int
    {
        $pdo = Db::pdo();
        $req = $pdo->prepare(
            "INSERT INTO categorie (code, nom, description, ordre, actif)
             VALUES (?, ?, ?, ?, ?)"
        );
        $req->execute([
            $donnees['code'],
            $donnees['nom'],
            $donnees['description'] ?? null,
            $donnees['ordre'] ?? 0,
            isset($donnees['actif']) ? (int)(bool)$donnees['actif'] : 1,
        ]);
        return (int)$pdo->lastInsertId();
    }


    /**
     * Modifie une catégorie.
     */
    public static function modifier(int $idCategorie, array $donnees): bool
    {
        $req = Db::pdo()->prepare(
            "UPDATE categorie
             SET code = ?, nom = ?, description = ?, ordre = ?, actif = ?
             WHERE id_categorie = ?"
        );
        return $req->execute([
            $donnees['code'],
            $donnees['nom'],
            $donnees['description'] ?? null,
            $donnees['ordre'] ?? 0,
            isset($donnees['actif']) ? (int)(bool)$donnees['actif'] : 1,
            $idCategorie,
        ]);
    }


    /**
     * Active ou désactive rapidement une catégorie.
     */
    public static function basculerActif(int $idCategorie): bool
    {
        $req = Db::pdo()->prepare(
            "UPDATE categorie SET actif = 1 - actif WHERE id_categorie = ?"
        );
        return $req->execute([$idCategorie]);
    }


    /**
     * Vérifie si une catégorie peut être supprimée (aucun article rattaché).
     */
    public static function estVide(int $idCategorie): bool
    {
        $req = Db::pdo()->prepare(
            "SELECT COUNT(*) FROM article WHERE id_categorie = ?"
        );
        $req->execute([$idCategorie]);
        return (int)$req->fetchColumn() === 0;
    }


    /**
     * Supprime une catégorie SI elle est vide.
     * Retourne false si des articles sont encore rattachés.
     */
    public static function supprimer(int $idCategorie): bool
    {
        if (!self::estVide($idCategorie)) {
            return false;
        }
        $req = Db::pdo()->prepare("DELETE FROM categorie WHERE id_categorie = ?");
        return $req->execute([$idCategorie]);
    }


    /**
     * Vérifie si un code (slug) est déjà pris (sauf pour la cat. en cours d'édition).
     */
    public static function codeExiste(string $code, int $idIgnore = 0): bool
    {
        $req = Db::pdo()->prepare(
            "SELECT COUNT(*) FROM categorie
             WHERE code = ? AND id_categorie != ?"
        );
        $req->execute([$code, $idIgnore]);
        return $req->fetchColumn() > 0;
    }
}
