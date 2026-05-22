<?php
/**
 * classes/util/Db.php
 * ---------------------------------------------------------------------
 * Classe utilitaire qui gère UNE seule connexion PDO partagée par toute
 * l'application (pattern singleton).
 *
 * Avantages :
 *  - Une seule connexion à la BDD ouverte par requête HTTP (économie)
 *  - Configuration centralisée (mode exception, fetch associatif...)
 *  - Code appelant ultra-simple : Db::pdo() et c'est tout
 *
 * Utilisation depuis n'importe quel fichier :
 *
 *      $pdo = Db::pdo();
 *      $req = $pdo->prepare("SELECT * FROM membre WHERE id_membre = ?");
 *      $req->execute([$id]);
 *      $membre = $req->fetch();
 * ---------------------------------------------------------------------
 */

class Db
{
    /**
     * L'instance PDO unique, partagée pour toute la durée de la requête HTTP.
     * @var PDO|null
     */
    private static ?PDO $instance = null;

    /**
     * Constructeur privé : empêche la création d'instances depuis l'extérieur.
     * On force le passage par la méthode statique pdo().
     */
    private function __construct() {}

    /**
     * Empêche le clonage de l'instance.
     */
    private function __clone() {}

    /**
     * Retourne l'instance PDO unique.
     * La crée à la première demande, la réutilise ensuite.
     *
     * @return PDO
     * @throws PDOException si la connexion échoue
     */
    public static function pdo(): PDO
    {
        // Si la connexion n'existe pas encore, on la crée
        if (self::$instance === null) {

            // Construction de la chaîne DSN (Data Source Name)
            // Format : "mysql:host=...;dbname=...;charset=..."
            $dsn = 'mysql:host=' . DB_HOST
                 . ';dbname=' . DB_NAME
                 . ';charset=' . DB_CHARSET;

            // Options de configuration de PDO
            $options = [
                // Mode d'erreur : lever une exception en cas de problème
                // (vs PDO::ERRMODE_SILENT qui est le défaut et masque les erreurs)
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

                // Format des résultats : tableau associatif par défaut
                // (au lieu de PDO::FETCH_BOTH qui double les colonnes)
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

                // Désactiver l'émulation des requêtes préparées
                // pour avoir de VRAIES requêtes préparées côté serveur MySQL
                // (meilleure sécurité contre les injections SQL)
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            // Tentative de connexion. En cas d'échec, on relance l'exception
            // pour que le code appelant puisse décider de la gérer ou non.
            //
            // Cas d'usage typique : la fonction parametre() encapsule cet
            // appel dans un try/catch pour que la page continue à s'afficher
            // même si la BDD est indisponible.
            //
            // Pour les pages qui NE PEUVENT PAS fonctionner sans BDD
            // (catalogue, blog, etc.), l'exception non rattrapée affichera
            // un message d'erreur clair en mode dev ou un message générique
            // en production grâce au gestionnaire global d'erreurs.
            self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
        }

        return self::$instance;
    }

    /**
     * Ferme la connexion (en réalité PHP s'en occupe à la fin du script,
     * mais cette méthode existe pour les cas particuliers).
     */
    public static function close(): void
    {
        self::$instance = null;
    }
}
