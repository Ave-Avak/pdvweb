<?php
/**
 * classes/LikeContenu.php
 * ---------------------------------------------------------------------
 * Modèle "LikeContenu" : système de likes polymorphes.
 *
 * Un like peut porter sur :
 *   - un billet      (type = 'billet')
 *   - un commentaire (type = 'commentaire')
 *   - un article     (type = 'article')
 *
 * La clé primaire composite (id_membre, type, id_cible) garantit
 * qu'un membre ne peut liker qu'une fois la même chose.
 * ---------------------------------------------------------------------
 */

class LikeContenu
{
    /**
     * Types autorisés (whitelist - protection contre injection).
     */
    private const TYPES_VALIDES = ['billet', 'commentaire', 'article'];

    /**
     * Vérifie qu'un type est valide.
     */
    public static function typeEstValide(string $type): bool
    {
        return in_array($type, self::TYPES_VALIDES, true);
    }

    /**
     * Un membre a-t-il déjà liké ce contenu ?
     */
    public static function aLike(int $idMembre, string $type, int $idCible): bool
    {
        if (!self::typeEstValide($type)) return false;

        $req = Db::pdo()->prepare(
            "SELECT COUNT(*) FROM like_contenu
             WHERE id_membre = ? AND type = ? AND id_cible = ?"
        );
        $req->execute([$idMembre, $type, $idCible]);
        return $req->fetchColumn() > 0;
    }

    /**
     * Compte les likes d'un contenu.
     */
    public static function compter(string $type, int $idCible): int
    {
        if (!self::typeEstValide($type)) return 0;

        $req = Db::pdo()->prepare(
            "SELECT COUNT(*) FROM like_contenu WHERE type = ? AND id_cible = ?"
        );
        $req->execute([$type, $idCible]);
        return (int)$req->fetchColumn();
    }

    /**
     * Toggle : ajoute ou retire un like selon l'état actuel.
     *
     * @return bool true = liké maintenant, false = unliké
     */
    public static function toggle(int $idMembre, string $type, int $idCible): bool
    {
        if (!self::typeEstValide($type)) {
            throw new InvalidArgumentException("Type de contenu invalide : $type");
        }

        $pdo = Db::pdo();

        if (self::aLike($idMembre, $type, $idCible)) {
            // Déjà liké → on retire
            $pdo->prepare(
                "DELETE FROM like_contenu
                 WHERE id_membre = ? AND type = ? AND id_cible = ?"
            )->execute([$idMembre, $type, $idCible]);
            return false;
        } else {
            // Pas encore liké → on ajoute
            $pdo->prepare(
                "INSERT INTO like_contenu (id_membre, type, id_cible) VALUES (?, ?, ?)"
            )->execute([$idMembre, $type, $idCible]);
            return true;
        }
    }
}
