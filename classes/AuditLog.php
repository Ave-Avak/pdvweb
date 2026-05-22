<?php
/**
 * classes/AuditLog.php
 * ---------------------------------------------------------------------
 * Lecture et écriture de la table audit_log.
 *
 * Trace les actions importantes pour conformité RGPD et sécurité :
 *   - connexions, inscriptions, blocages, suppressions
 *   - modifications de profil, RGPD
 *   - actions admin
 *
 * Le format `details` est en JSON pour conserver une structure flexible
 * (avant/après pour les modifications, par ex).
 * ---------------------------------------------------------------------
 */

class AuditLog
{
    /**
     * Enregistre une action.
     *
     * @param string      $action     ex: 'article.creer', 'membre.bloquer'
     * @param int|null    $idMembre   ID du membre qui a effectué l'action (NULL = système)
     * @param string|null $entite     Table affectée (ex: 'article', 'membre')
     * @param int|null    $idEntite   PK de l'enregistrement affecté
     * @param array|null  $details    Données structurées (avant/après, contexte)
     */
    public static function enregistrer(
        string $action,
        ?int $idMembre = null,
        ?string $entite = null,
        ?int $idEntite = null,
        ?array $details = null
    ): void {
        try {
            $req = Db::pdo()->prepare(
                "INSERT INTO audit_log
                    (id_membre, action, entite, id_entite, details, ip, user_agent)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );
            $req->execute([
                $idMembre,
                $action,
                $entite,
                $idEntite,
                $details !== null ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
                $_SERVER['REMOTE_ADDR']     ?? null,
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
            ]);
        } catch (Throwable $e) {
            // Best-effort : on ne fait pas planter l'app pour un log raté
            error_log('[AuditLog] ' . $e->getMessage());
        }
    }


    /**
     * Liste paginée des logs d'audit avec filtres.
     *
     * @param array $opts
     *   - 'action'    ?string : filtrer par action (LIKE)
     *   - 'id_membre' ?int    : filtrer par membre
     *   - 'entite'    ?string : filtrer par table affectée
     *   - 'jours'     ?int    : limiter aux N derniers jours
     *   - 'page'      int
     *   - 'parPage'   int
     */
    public static function lister(array $opts = []): array
    {
        $page    = max(1, (int)($opts['page']    ?? 1));
        $parPage = max(1, (int)($opts['parPage'] ?? 50));
        $offset  = ($page - 1) * $parPage;

        $where  = [];
        $params = [];

        if (!empty($opts['action'])) {
            $where[]  = "a.action LIKE ?";
            $params[] = '%' . $opts['action'] . '%';
        }

        if (!empty($opts['id_membre'])) {
            $where[]  = "a.id_membre = ?";
            $params[] = (int)$opts['id_membre'];
        }

        if (!empty($opts['entite'])) {
            $where[]  = "a.entite = ?";
            $params[] = $opts['entite'];
        }

        if (!empty($opts['jours'])) {
            $where[]  = "a.date_action >= DATE_SUB(NOW(), INTERVAL ? DAY)";
            $params[] = (int)$opts['jours'];
        }

        $clauseWhere = $where ? ' WHERE ' . implode(' AND ', $where) : '';

        // Total
        $reqTotal = Db::pdo()->prepare("SELECT COUNT(*) FROM audit_log a" . $clauseWhere);
        $reqTotal->execute($params);
        $total = (int)$reqTotal->fetchColumn();

        // Données (avec infos membre, jointure LEFT car peut être NULL)
        $sql = "SELECT a.*,
                       m.login, m.prenom, m.nom, m.date_anonymisation
                FROM audit_log a
                LEFT JOIN membre m ON m.id_membre = a.id_membre
                $clauseWhere
                ORDER BY a.date_action DESC
                LIMIT $parPage OFFSET $offset";

        $req = Db::pdo()->prepare($sql);
        $req->execute($params);
        $logs = $req->fetchAll();

        return [
            'logs'       => $logs,
            'total'      => $total,
            'totalPages' => (int)ceil($total / $parPage),
            'page'       => $page,
        ];
    }


    /**
     * Liste les actions distinctes pour le filtre admin.
     */
    public static function actionsDistinctes(): array
    {
        return Db::pdo()->query(
            "SELECT DISTINCT action FROM audit_log ORDER BY action"
        )->fetchAll(PDO::FETCH_COLUMN);
    }


    /**
     * Compte total d'événements (KPI).
     */
    public static function compterTotal(): int
    {
        return (int)Db::pdo()->query("SELECT COUNT(*) FROM audit_log")->fetchColumn();
    }
}
