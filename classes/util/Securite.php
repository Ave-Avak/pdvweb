<?php
/**
 * classes/util/Securite.php
 * ---------------------------------------------------------------------
 * Protections de sécurité diverses :
 *  - Anti brute-force sur le login (limitation des tentatives)
 *  - Helpers IP / User-Agent
 *  - Génération de tokens sécurisés
 *
 * Cette classe utilise la table `tentative_connexion` pour tracer
 * les essais ratés.
 * ---------------------------------------------------------------------
 */

class Securite
{
    /**
     * Récupère l'IP du client (en tenant compte des proxys éventuels).
     *
     * @return string
     */
    public static function ip(): string
    {
        // En production derrière un proxy/CDN, on lirait HTTP_X_FORWARDED_FOR.
        // En dev local, REMOTE_ADDR suffit.
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Récupère le User-Agent du navigateur (limité à 255 caractères pour
     * coller à la colonne en BDD).
     *
     * @return string
     */
    public static function userAgent(): string
    {
        return substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
    }

    /**
     * Enregistre une tentative de connexion (réussie ou ratée).
     *
     * @param string $login   Login essayé
     * @param bool   $succes  true si la connexion a abouti
     */
    public static function enregistrerTentative(string $login, bool $succes): void
    {
        $pdo = Db::pdo();
        $req = $pdo->prepare(
            "INSERT INTO tentative_connexion (login_essaye, ip, user_agent, succes)
             VALUES (?, ?, ?, ?)"
        );
        $req->execute([
            substr($login, 0, 50),
            self::ip(),
            self::userAgent(),
            $succes ? 1 : 0,
        ]);
    }

    /**
     * Vérifie si un login est temporairement bloqué pour cause de tentatives ratées.
     *
     * Règle : si plus de MAX_TENTATIVES_LOGIN tentatives ratées dans les
     * DUREE_BLOCAGE_MINUTES dernières minutes, on bloque.
     *
     * @param string $login Le login à vérifier
     * @return bool true si bloqué
     */
    public static function estBloque(string $login): bool
    {
        $pdo = Db::pdo();

        // On compte les tentatives RATÉES (succes = 0) récentes
        $req = $pdo->prepare(
            "SELECT COUNT(*) AS nb
             FROM tentative_connexion
             WHERE login_essaye = ?
               AND succes = 0
               AND date_tent > NOW() - INTERVAL ? MINUTE"
        );
        $req->execute([$login, DUREE_BLOCAGE_MINUTES]);
        $nb = (int)$req->fetchColumn();

        return $nb >= MAX_TENTATIVES_LOGIN;
    }

    /**
     * Renvoie le nombre de minutes restantes avant déblocage.
     * Utile pour afficher à l'utilisateur "réessayez dans X minutes".
     *
     * @param string $login
     * @return int Nombre de minutes (0 si non bloqué)
     */
    public static function minutesAvantDeblocage(string $login): int
    {
        if (!self::estBloque($login)) {
            return 0;
        }

        $pdo = Db::pdo();
        $req = $pdo->prepare(
            "SELECT TIMESTAMPDIFF(MINUTE, MIN(date_tent), NOW()) AS ecoule
             FROM tentative_connexion
             WHERE login_essaye = ?
               AND succes = 0
               AND date_tent > NOW() - INTERVAL ? MINUTE"
        );
        $req->execute([$login, DUREE_BLOCAGE_MINUTES]);
        $ecoule = (int)$req->fetchColumn();

        // Temps restant = durée totale - temps écoulé (minimum 1 min affichée)
        return max(1, DUREE_BLOCAGE_MINUTES - $ecoule);
    }

    /**
     * Génère un token aléatoire sécurisé (pour reset password, etc.).
     *
     * @param int $longueurOctets Nombre d'octets (résultat = 2x cette valeur en hexa)
     * @return string
     */
    public static function genererToken(int $longueurOctets = 32): string
    {
        return bin2hex(random_bytes($longueurOctets));
    }

    /**
     * Hash un token avant stockage en BDD (équivalent password_hash pour les tokens).
     *
     * @param string $token
     * @return string
     */
    public static function hasherToken(string $token): string
    {
        return hash('sha256', $token);
    }


    // =================================================================
    //  HEADERS HTTP DE SÉCURITÉ (étape 8)
    // =================================================================

    /**
     * Envoie les headers HTTP de sécurité recommandés par OWASP.
     * À appeler depuis bootstrap.php AVANT tout output.
     *
     * Chaque header est commenté pour expliquer son rôle.
     */
    public static function envoyerHeadersSecurite(): void
    {
        // Ne fonctionne que si on n'a pas déjà envoyé du contenu
        if (headers_sent()) {
            return;
        }

        // ---- Content-Security-Policy ----
        // Limite drastiquement les sources autorisées pour images, scripts, CSS, etc.
        // Réduit fortement la surface d'attaque XSS : même si du JS malveillant
        // arrivait à s'injecter, le navigateur refuserait de l'exécuter.
        //
        // 'self'  = même origine
        // 'unsafe-inline' nécessaire pour Tailwind CDN + nos quelques scripts inline
        // cdn.tailwindcss.com = le CDN Tailwind utilisé
        // fonts.googleapis.com + fonts.gstatic.com = police Inter via Google Fonts
        //
        // Pour un projet de production sans CDN, on pourrait retirer 'unsafe-inline'.
        $csp = "default-src 'self'; "
             . "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com; "
             . "style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://fonts.googleapis.com; "
             . "img-src 'self' data:; "
             . "font-src 'self' data: https://fonts.gstatic.com; "
             . "connect-src 'self'; "
             . "frame-ancestors 'none'; "
             . "base-uri 'self'; "
             . "form-action 'self'";
        header("Content-Security-Policy: $csp");

        // ---- X-Frame-Options ----
        // Empêche le site d'être intégré dans une <iframe> sur un autre domaine.
        // Protège contre les attaques de clickjacking (overlay invisible).
        header('X-Frame-Options: DENY');

        // ---- X-Content-Type-Options ----
        // Force le navigateur à respecter le Content-Type renvoyé.
        // Empêche le "MIME sniffing" qui pourrait exécuter un .txt comme du JS.
        header('X-Content-Type-Options: nosniff');

        // ---- Referrer-Policy ----
        // Quand l'utilisateur clique vers un autre site, on n'envoie que l'origine
        // (pas l'URL complète qui pourrait contenir des paramètres sensibles).
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // ---- Permissions-Policy ----
        // Désactive les API navigateur qu'on n'utilise pas (caméra, micro, etc.)
        // Réduit les vecteurs d'attaque possibles.
        header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');

        // ---- Strict-Transport-Security (HSTS) ----
        // Force le navigateur à utiliser HTTPS pour les futures requêtes.
        // En dev (XAMPP en HTTP), on ne l'active pas. En prod sous HTTPS, à activer :
        // header('Strict-Transport-Security: max-age=31536000; includeSubDomains');

        // ---- Retire le header X-Powered-By ----
        // Cache l'info "PHP/8.2.12" qui aide les attaquants à cibler des CVE.
        header_remove('X-Powered-By');
    }


    // =================================================================
    //  RATE LIMITING (étape 8)
    // =================================================================

    /**
     * Vérifie si une IP est rate-limited pour une action donnée.
     * Retourne true si l'IP a dépassé le quota → bloquer la requête.
     *
     * Utilise la table tentative_connexion (étape 1) en y stockant
     * une "action" arbitraire dans le champ login.
     *
     * @param string $action    Identifiant de l'action (ex: 'login_global')
     * @param int    $maxParMinutes Quota max
     * @param int    $minutes   Fenêtre de temps
     */
    public static function estRateLimited(string $action, int $maxParMinutes = 10, int $minutes = 15): bool
    {
        $ip = self::ip();
        $req = Db::pdo()->prepare(
            "SELECT COUNT(*) FROM tentative_connexion
             WHERE ip = ?
               AND login_essaye = ?
               AND date_tent >= DATE_SUB(NOW(), INTERVAL ? MINUTE)"
        );
        $req->execute([$ip, $action, $minutes]);
        return (int)$req->fetchColumn() >= $maxParMinutes;
    }

    /**
     * Enregistre une action rate-limitée.
     * @param string $action Identifiant de l'action
     */
    public static function enregistrerActionRateLimit(string $action): void
    {
        try {
            $req = Db::pdo()->prepare(
                "INSERT INTO tentative_connexion (login_essaye, ip, succes, date_tent)
                 VALUES (?, ?, 0, NOW())"
            );
            $req->execute([$action, self::ip()]);
        } catch (Throwable $e) {
            // Best-effort
        }
    }


    // =================================================================
    //  MAINTENANCE (étape 8)
    // =================================================================

    /**
     * Purge les tokens expirés (anciens session_id, password_reset, etc.).
     * À appeler depuis une page admin ou un cron.
     *
     * @return array Compteurs par table
     */
    public static function purgerTokensExpires(): array
    {
        $pdo = Db::pdo();
        $resultats = [];

        // Tokens expirés (table token : password_reset, email_verification, etc.)
        $req = $pdo->prepare(
            "DELETE FROM token WHERE date_expiration < NOW()"
        );
        $req->execute();
        $resultats['tokens_expires'] = $req->rowCount();

        // Vieilles tentatives de connexion (> 30 jours)
        $req = $pdo->prepare(
            "DELETE FROM tentative_connexion
             WHERE date_tent < DATE_SUB(NOW(), INTERVAL 30 DAY)"
        );
        $req->execute();
        $resultats['tentatives_anciennes'] = $req->rowCount();

        // Vieux logs de connexion (> 1 an = conservation RGPD raisonnable)
        $req = $pdo->prepare(
            "DELETE FROM log_connexion
             WHERE date_log < DATE_SUB(NOW(), INTERVAL 1 YEAR)"
        );
        $req->execute();
        $resultats['log_connexion_anciens'] = $req->rowCount();

        // Vieilles vues d'article (> 90 jours = stats détaillées suffisantes)
        $req = $pdo->prepare(
            "DELETE FROM vue_article
             WHERE date_vue < DATE_SUB(NOW(), INTERVAL 90 DAY)"
        );
        $req->execute();
        $resultats['vues_articles_anciennes'] = $req->rowCount();

        // Vieux logs de recherche (> 6 mois)
        $req = $pdo->prepare(
            "DELETE FROM recherche_log
             WHERE date_recherche < DATE_SUB(NOW(), INTERVAL 6 MONTH)"
        );
        $req->execute();
        $resultats['recherches_anciennes'] = $req->rowCount();

        return $resultats;
    }


    /**
     * Compte les éléments purgeables (pour affichage avant purge).
     */
    public static function compterPurgeables(): array
    {
        $pdo = Db::pdo();
        return [
            'tokens_expires' => (int)$pdo->query(
                "SELECT COUNT(*) FROM token WHERE date_expiration < NOW()"
            )->fetchColumn(),
            'tentatives_anciennes' => (int)$pdo->query(
                "SELECT COUNT(*) FROM tentative_connexion
                 WHERE date_tent < DATE_SUB(NOW(), INTERVAL 30 DAY)"
            )->fetchColumn(),
            'log_connexion_anciens' => (int)$pdo->query(
                "SELECT COUNT(*) FROM log_connexion
                 WHERE date_log < DATE_SUB(NOW(), INTERVAL 1 YEAR)"
            )->fetchColumn(),
            'vues_articles_anciennes' => (int)$pdo->query(
                "SELECT COUNT(*) FROM vue_article
                 WHERE date_vue < DATE_SUB(NOW(), INTERVAL 90 DAY)"
            )->fetchColumn(),
            'recherches_anciennes' => (int)$pdo->query(
                "SELECT COUNT(*) FROM recherche_log
                 WHERE date_recherche < DATE_SUB(NOW(), INTERVAL 6 MONTH)"
            )->fetchColumn(),
        ];
    }
}
