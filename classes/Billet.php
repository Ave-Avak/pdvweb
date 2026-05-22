<?php
/**
 * classes/Billet.php
 * ---------------------------------------------------------------------
 * Modèle "Billet" : billets de blog publiés par les administrateurs.
 *
 * IMPORTANT : implémente le SOFT DELETE.
 *   - La méthode supprimer() ne fait PAS de DELETE en BDD.
 *   - Elle marque le billet comme supprimé (date_suppression, id_membre_suppression).
 *   - Toutes les requêtes de lecture filtrent les billets supprimés.
 *
 * Avantages :
 *   - Modération réversible (l'admin peut restaurer)
 *   - Audit (qui a supprimé quoi, quand)
 *   - Pas de perte des commentaires liés
 * ---------------------------------------------------------------------
 */

class Billet
{
    /**
     * Récupère un billet (non supprimé) par son ID, avec infos auteur.
     *
     * @param int  $id
     * @param bool $inclureSupprimes Si true, retourne aussi les billets supprimés (pour admin)
     */
    public static function trouverParId(int $id, bool $inclureSupprimes = false): ?array
    {
        $clauseSupp = $inclureSupprimes ? '' : ' AND b.date_suppression IS NULL';

        $req = Db::pdo()->prepare(
            "SELECT b.*,
                    u.id_membre, u.prenom AS auteur_prenom, u.nom AS auteur_nom,
                    u.login AS auteur_login, u.avatar AS auteur_avatar
             FROM billet b
             INNER JOIN membre u ON u.id_membre = b.id_membre
             WHERE b.id_billet = ?" . $clauseSupp
        );
        $req->execute([$id]);
        $billet = $req->fetch();
        return $billet ?: null;
    }

    /**
     * Liste les billets avec pagination, recherche et tri.
     * Exclut automatiquement les billets supprimés.
     */
    public static function lister(array $opts = []): array
    {
        $recherche = trim($opts['recherche'] ?? '');
        $idTag     = (int)($opts['tag']      ?? 0);
        $tri       = $opts['tri']            ?? 'recents';
        $page      = max(1, (int)($opts['page'] ?? 1));
        $parPage   = max(1, (int)($opts['parPage'] ?? 10));
        $offset    = ($page - 1) * $parPage;

        // Filtre soft delete TOUJOURS appliqué pour les listes publiques
        $where  = ['b.date_suppression IS NULL'];
        $params = [];

        if ($recherche !== '') {
            $where[]  = "b.titre LIKE ?";
            $params[] = '%' . $recherche . '%';
        }

        if ($idTag > 0) {
            $where[]  = "b.id_billet IN (SELECT id_billet FROM billet_tag WHERE id_tag = ?)";
            $params[] = $idTag;
        }

        $clauseWhere = ' WHERE ' . implode(' AND ', $where);

        $orderBy = match ($tri) {
            'populaires' => 'nb_likes DESC, b.date_billet DESC',
            'commentes'  => 'nb_commentaires DESC, b.date_billet DESC',
            default      => 'b.date_billet DESC',
        };

        // Comptage total
        $reqTotal = Db::pdo()->prepare(
            "SELECT COUNT(*) FROM billet b" . $clauseWhere
        );
        $reqTotal->execute($params);
        $total = (int)$reqTotal->fetchColumn();

        // Requête principale : on compte aussi les commentaires NON supprimés
        $sql = "SELECT b.*,
                       u.prenom AS auteur_prenom, u.nom AS auteur_nom,
                       u.login AS auteur_login, u.avatar AS auteur_avatar,
                       (SELECT COUNT(*) FROM commentaire
                        WHERE id_billet = b.id_billet AND date_suppression IS NULL) AS nb_commentaires,
                       (SELECT COUNT(*) FROM like_contenu
                        WHERE type = 'billet' AND id_cible = b.id_billet) AS nb_likes
                FROM billet b
                INNER JOIN membre u ON u.id_membre = b.id_membre
                $clauseWhere
                ORDER BY $orderBy
                LIMIT $parPage OFFSET $offset";

        $req = Db::pdo()->prepare($sql);
        $req->execute($params);
        $billets = $req->fetchAll();

        return [
            'billets'    => $billets,
            'total'      => $total,
            'totalPages' => (int)ceil($total / $parPage),
            'page'       => $page,
        ];
    }

    /**
     * Liste les billets SUPPRIMÉS (page corbeille admin).
     */
    public static function listerSupprimes(): array
    {
        $req = Db::pdo()->query(
            "SELECT b.*,
                    u.prenom AS auteur_prenom, u.nom AS auteur_nom,
                    s.prenom AS supp_prenom, s.nom AS supp_nom
             FROM billet b
             INNER JOIN membre u ON u.id_membre = b.id_membre
             LEFT JOIN membre s ON s.id_membre = b.id_membre_suppression
             WHERE b.date_suppression IS NOT NULL
             ORDER BY b.date_suppression DESC"
        );
        return $req->fetchAll();
    }

    /**
     * Crée un nouveau billet.
     */
    public static function creer(int $idAuteur, string $titre, string $corps, array $idsTags = []): int
    {
        $pdo = Db::pdo();
        $pdo->beginTransaction();
        try {
            $req = $pdo->prepare(
                "INSERT INTO billet (id_membre, titre, corps) VALUES (?, ?, ?)"
            );
            $req->execute([$idAuteur, $titre, $corps]);
            $idBillet = (int)$pdo->lastInsertId();

            self::associerTags($idBillet, $idsTags);

            $pdo->commit();
            return $idBillet;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Modifie un billet existant.
     */
    public static function modifier(int $idBillet, string $titre, string $corps, array $idsTags = []): bool
    {
        $pdo = Db::pdo();
        $pdo->beginTransaction();
        try {
            $req = $pdo->prepare(
                "UPDATE billet SET titre = ?, corps = ? WHERE id_billet = ?"
            );
            $req->execute([$titre, $corps, $idBillet]);

            $pdo->prepare("DELETE FROM billet_tag WHERE id_billet = ?")->execute([$idBillet]);
            self::associerTags($idBillet, $idsTags);

            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * SUPPRIME (soft) un billet.
     * Le billet n'est plus visible mais reste en BDD pour audit/restauration.
     *
     * @param int $idBillet
     * @param int $idMembreSupp ID du membre qui effectue la suppression
     */
    public static function supprimer(int $idBillet, int $idMembreSupp): bool
    {
        $req = Db::pdo()->prepare(
            "UPDATE billet
             SET date_suppression = NOW(), id_membre_suppression = ?
             WHERE id_billet = ? AND date_suppression IS NULL"
        );
        return $req->execute([$idMembreSupp, $idBillet]);
    }

    /**
     * RESTAURE un billet précédemment supprimé.
     */
    public static function restaurer(int $idBillet): bool
    {
        $req = Db::pdo()->prepare(
            "UPDATE billet
             SET date_suppression = NULL, id_membre_suppression = NULL
             WHERE id_billet = ?"
        );
        return $req->execute([$idBillet]);
    }

    /**
     * Récupère les tags associés à un billet.
     */
    public static function tagsDe(int $idBillet): array
    {
        $req = Db::pdo()->prepare(
            "SELECT t.* FROM tag t
             INNER JOIN billet_tag bt ON bt.id_tag = t.id_tag
             WHERE bt.id_billet = ?
             ORDER BY t.nom"
        );
        $req->execute([$idBillet]);
        return $req->fetchAll();
    }

    private static function associerTags(int $idBillet, array $idsTags): void
    {
        if (empty($idsTags)) return;

        $req = Db::pdo()->prepare(
            "INSERT IGNORE INTO billet_tag (id_billet, id_tag) VALUES (?, ?)"
        );
        foreach ($idsTags as $idTag) {
            $idTag = (int)$idTag;
            if ($idTag > 0) {
                $req->execute([$idBillet, $idTag]);
            }
        }
    }

    /**
     * Compte les billets actifs (non supprimés).
     */
    public static function compterTous(): int
    {
        return (int)Db::pdo()->query(
            "SELECT COUNT(*) FROM billet WHERE date_suppression IS NULL"
        )->fetchColumn();
    }

    /**
     * Compte les billets supprimés (pour le bandeau "corbeille").
     */
    public static function compterSupprimes(): int
    {
        return (int)Db::pdo()->query(
            "SELECT COUNT(*) FROM billet WHERE date_suppression IS NOT NULL"
        )->fetchColumn();
    }
}
