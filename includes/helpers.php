<?php
/**
 * includes/helpers.php
 * ---------------------------------------------------------------------
 * Petites fonctions globales utilitaires utilisées partout.
 *
 * Volontairement en fonctions (pas en classe statique) parce que :
 *  - elles sont appelées extrêmement souvent (h() peut apparaître 50 fois
 *    dans une vue)
 *  - elles n'ont pas d'état, juste de la transformation pure
 * ---------------------------------------------------------------------
 */


/**
 * h() — "html escape"
 * --------------------------------------------------------------------
 * Échappe une chaîne pour l'affichage HTML, anti-XSS.
 * À UTILISER SYSTÉMATIQUEMENT à l'affichage de toute donnée
 * provenant de l'utilisateur ou de la BDD.
 *
 * Exemple dans une vue :
 *      <h1><?= h($billet['titre']) ?></h1>
 *
 * Sans cette fonction, un titre comme <script>alert('XSS')</script>
 * exécuterait du JS dans le navigateur des visiteurs.
 *
 * @param mixed $valeur  Chaîne (ou null) à échapper
 * @return string        Chaîne échappée pour HTML
 */
function h($valeur): string
{
    if ($valeur === null) return '';
    return htmlspecialchars((string)$valeur, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}


/**
 * url() — construit une URL absolue à partir d'un chemin relatif au site.
 *
 * Exemple :
 *      url('/blog.php')        -> http://localhost/pdvweb/public/blog.php
 *      url('/admin/index.php') -> http://localhost/pdvweb/public/admin/index.php
 *
 * @param string $chemin  Chemin commençant par /
 * @return string
 */
function url(string $chemin = ''): string
{
    return SITE_URL . '/' . ltrim($chemin, '/');
}


/**
 * asset() — URL d'un fichier statique (CSS, JS, image, avatar).
 *
 * Exemples :
 *      asset('css/style.css')           -> .../public/assets/css/style.css
 *      asset('uploads/avatars/x.jpg')   -> .../public/uploads/avatars/x.jpg
 *      asset_avatar($membre['avatar']) -> raccourci ci-dessous
 *
 * @param string $chemin  Chemin relatif à /public/
 * @return string
 */
function asset(string $chemin): string
{
    return SITE_URL . '/' . ltrim($chemin, '/');
}


/**
 * asset_avatar() — URL de l'avatar d'un membre, avec fallback sur un avatar par défaut.
 *
 * @param string|null $nomFichier  Contenu de membre.avatar (ou null)
 * @return string
 */
function asset_avatar(?string $nomFichier): string
{
    if (empty($nomFichier)) {
        // Avatar par défaut (image générique placée dans assets/img/)
        return asset('assets/img/avatar-defaut.svg');
    }
    return asset('uploads/avatars/' . $nomFichier);
}


/**
 * asset_article() — URL de l'image d'un article.
 *
 * @param string|null $nomFichier  Contenu de article.image
 * @return string
 */
function asset_article(?string $nomFichier): string
{
    // Pas de fichier renseigné → image par défaut
    if (empty($nomFichier)) {
        return asset('assets/img/article-defaut.svg');
    }

    // Sécurité : empêche les chemins traversaux (../, ..\)
    $nomFichier = basename($nomFichier);

    // Vérifie que le fichier existe vraiment sur disque, sinon défaut
    $cheminAbsolu = PUBLIC_PATH . '/uploads/articles/' . $nomFichier;
    if (!is_file($cheminAbsolu)) {
        return asset('assets/img/article-defaut.svg');
    }

    return asset('uploads/articles/' . $nomFichier);
}


/**
 * format_date() — formate une date BDD au format français.
 *
 * @param string|null $dateSql  Date SQL ('2026-06-13 14:30:00') ou null
 * @param string      $format   Format de sortie (voir date())
 * @return string
 */
function format_date(?string $dateSql, string $format = 'd/m/Y à H:i'): string
{
    if (empty($dateSql)) return '';

    try {
        $dt = new DateTime($dateSql);
        return $dt->format($format);
    } catch (Exception $e) {
        return '';
    }
}


/**
 * format_date_courte() — version courte (sans l'heure).
 */
function format_date_courte(?string $dateSql): string
{
    return format_date($dateSql, 'd/m/Y');
}


/**
 * format_date_relative() — affichage "il y a 3 minutes", "hier", etc.
 * Utile pour le mini-chat et les commentaires.
 *
 * @param string|null $dateSql
 * @return string
 */
function format_date_relative(?string $dateSql): string
{
    if (empty($dateSql)) return '';

    try {
        $dt = new DateTime($dateSql);
        $maintenant = new DateTime();
        $diff = $maintenant->getTimestamp() - $dt->getTimestamp();

        if ($diff < 60)        return 'à l\'instant';
        if ($diff < 3600)      return 'il y a ' . floor($diff / 60) . ' min';
        if ($diff < 86400)     return 'il y a ' . floor($diff / 3600) . ' h';
        if ($diff < 172800)    return 'hier à ' . $dt->format('H:i');
        if ($diff < 604800)    return 'il y a ' . floor($diff / 86400) . ' jours';

        // Plus d'une semaine : date complète
        return $dt->format('d/m/Y');
    } catch (Exception $e) {
        return '';
    }
}


/**
 * format_prix() — formate un prix en euros à la française.
 *
 *      format_prix(1299)        -> "1 299,00 €"
 *      format_prix(8.5)         -> "8,50 €"
 *
 * @param float|string $montant
 * @return string
 */
function format_prix($montant): string
{
    return number_format((float)$montant, 2, ',', ' ') . ' €';
}


/**
 * tronquer() — coupe une chaîne à N caractères en ajoutant "..." si besoin.
 *
 * @param string $texte
 * @param int    $longueurMax
 * @return string
 */
function tronquer(string $texte, int $longueurMax = 100): string
{
    if (mb_strlen($texte) <= $longueurMax) return $texte;
    return mb_substr($texte, 0, $longueurMax) . '…';
}


/**
 * actif() — renvoie une classe CSS si l'URL courante correspond au lien.
 * Utilisée dans la navbar pour mettre en surbrillance la page active.
 *
 *      <a href="..." class="<?= actif('/blog.php') ?>">Blog</a>
 *
 * @param string $chemin  Chemin à comparer (ex: '/blog.php')
 * @param string $classe  Classe à renvoyer si actif
 * @return string
 */
function actif(string $chemin, string $classe = 'text-blue-600 font-semibold'): string
{
    $courant = $_SERVER['PHP_SELF'] ?? '';
    return (str_ends_with($courant, $chemin)) ? $classe : '';
}


/**
 * nom_membre() — affiche le nom complet d'un membre, ou "Utilisateur supprimé"
 * si le compte a été anonymisé (RGPD).
 *
 * @param array|null $row  Ligne contenant prenom, nom et optionnellement date_anonymisation
 * @return string
 */
function nom_membre(?array $row): string
{
    if (!$row) return 'Inconnu';
    if (!empty($row['date_anonymisation'])) {
        return 'Utilisateur supprimé';
    }
    return trim(($row['prenom'] ?? '') . ' ' . ($row['nom'] ?? ''));
}


/**
 * Récupère un paramètre applicatif (depuis la table `parametre`).
 * Mis en cache statique pour éviter de relancer la requête à chaque appel.
 *
 *      $nomSite = parametre('site.nom', 'PDVWeb');
 *
 * @param string $cle             Clé du paramètre
 * @param mixed  $valeurParDefaut Valeur si la clé n'existe pas
 * @return mixed
 */
function parametre(string $cle, $valeurParDefaut = null)
{
    static $cache = null;

    // Premier appel : on charge tous les paramètres en mémoire
    if ($cache === null) {
        $cache = [];
        // On attrape TOUTE exception (pas que PDOException) :
        // - Throwable couvre les erreurs de connexion BDD, driver manquant, etc.
        // - Cela permet à la page de continuer à s'afficher même si la BDD
        //   est temporairement indisponible (on prendra les valeurs par défaut).
        try {
            $req = Db::pdo()->query("SELECT cle, valeur FROM parametre");
            foreach ($req->fetchAll() as $row) {
                $cache[$row['cle']] = $row['valeur'];
            }
        } catch (Throwable $e) {
            // BDD non disponible : on continue avec un cache vide,
            // les appels retourneront les valeurs par défaut.
            $cache = [];
        }
    }

    return $cache[$cle] ?? $valeurParDefaut;
}
