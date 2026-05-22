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
}
