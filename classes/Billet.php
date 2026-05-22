<?php
/**
 * classes/Billet.php
 * ---------------------------------------------------------------------
 * Modèle "Billet" : billets de blog publiés par les administrateurs.
 *
 * Toutes les méthodes sont statiques pour rester cohérent avec
 * Membre / Minichat.
 * ---------------------------------------------------------------------
 */

class Billet
{
    /**
     * Récupère un billet par son ID, avec infos auteur.
     */
    public static function trouverParId(int $id): ?array
    {
        $req = Db::pdo()->prepare(
            "SELECT b.*,
                    u.id_membre, u.prenom AS auteur_prenom, u.nom AS auteur_nom,
                    u.login AS auteur_login, u.avatar AS auteur_avatar
             FROM billet b
             INNER JOIN membre u ON u.id_membre = b.id_membre
             WHERE b.id_billet = ?"
        );
        $req->execute([$id]);
        $billet = $req->fetch();
        return $billet ?: null;
    }

    /**
     * Liste les billets avec pagination, recherche et tri.
     *
     * @param array $opts Options :
     *   - 'recherche' string   : terme à chercher dans le titre
     *   - 'tag'       int      : filtrer par id_tag
     *   - 'tri'       string   : 'recents' (def), 'populaires', 'commentes'
     *   - 'page'      int      : page courante (1 par défaut)
     *   - 'parPage'   int      : nb par page (10 par défaut)
     * @return array ['billets' => [...], 'total' => int, 'totalPages' => int]
     */
    public static function lister(array $opts = []): array
    {
        $recherche = trim($opts['recherche'] ?? '');
        $idTag     = (int)($opts['tag']      ?? 0);
        $tri       = $opts['tri']            ?? 'recents';
        $page      = max(1, (int)($opts['page'] ?? 1));
        $parPage   = max(1, (int)($opts['parPage'] ?? 10));
        $offset    = ($page - 1) * $parPage;

        // Construction dynamique de la requête
        $where  = [];
        $params = [];

        if ($recherche !== '') {
            $where[]  = "b.titre LIKE ?";
            $params[] = '%' . $recherche . '%';
        }

        if ($idTag > 0) {
            $where[]  = "b.id_billet IN (SELECT id_billet FROM billet_tag WHERE id_tag = ?)";
            $params[] = $idTag;
        }

        $clauseWhere = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        // ORDER BY selon le tri demandé
        $orderBy = match ($tri) {
            'populaires' => 'nb_likes DESC, b.date_billet DESC',
            'commentes'  => 'nb_commentaires DESC, b.date_billet DESC',
            default      => 'b.date_billet DESC',  // 'recents'
        };

        // Comptage total (pour la pagination)
        $reqTotal = Db::pdo()->prepare(
            "SELECT COUNT(*) FROM billet b" . $clauseWhere
        );
        $reqTotal->execute($params);
        $total = (int)$reqTotal->fetchColumn();

        // Requête principale : billets + compteurs (likes + commentaires)
        $sql = "SELECT b.*,
                       u.prenom AS auteur_prenom, u.nom AS auteur_nom,
                       u.login AS auteur_login, u.avatar AS auteur_avatar,
                       (SELECT COUNT(*) FROM commentaire WHERE id_billet = b.id_billet) AS nb_commentaires,
                       (SELECT COUNT(*) FROM like_contenu WHERE type = 'billet' AND id_cible = b.id_billet) AS nb_likes
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

            // Liaison des tags
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

            // Remplacement complet des tags
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
     * Supprime un billet (et ses commentaires + likes via CASCADE BDD).
     */
    public static function supprimer(int $idBillet): bool
    {
        $req = Db::pdo()->prepare("DELETE FROM billet WHERE id_billet = ?");
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

    /**
     * Associe des tags à un billet (méthode utilitaire interne).
     */
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
     * Compte total des billets (stats admin).
     */
    public static function compterTous(): int
    {
        return (int)Db::pdo()->query("SELECT COUNT(*) FROM billet")->fetchColumn();
    }
}
