<?php
/**
 * classes/util/Upload.php
 * ---------------------------------------------------------------------
 * Upload sécurisé d'images.
 *
 * Vérifie successivement :
 *  1. Pas d'erreur d'upload PHP
 *  2. Taille acceptable
 *  3. Extension dans la whitelist (.gif, .jpg, .jpeg)
 *  4. Type MIME réel (pas juste l'extension renommée !)
 *  5. Image valide (getimagesize la décode réellement)
 *
 * Puis renomme avec un nom aléatoire pour éviter les collisions
 * et les attaques par chemin (path traversal).
 *
 * Utilisation :
 *
 *      $resultat = Upload::image($_FILES['avatar'], UPLOADS_PATH . '/avatars');
 *      if ($resultat['succes']) {
 *          $nomFichier = $resultat['fichier'];   // ex: 'a1b2c3d4.jpg'
 *      } else {
 *          $erreur = $resultat['erreur'];
 *      }
 * ---------------------------------------------------------------------
 */

class Upload
{
    /**
     * Types MIME autorisés (mapping extension -> MIME).
     * Liste étendue, utilisée comme référence interne.
     */
    private const MIMES_AUTORISES = [
        'gif'  => ['image/gif'],
        'jpg'  => ['image/jpeg'],
        'jpeg' => ['image/jpeg'],
        'png'  => ['image/png'],
        'webp' => ['image/webp'],
    ];

    /**
     * Tente d'uploader une image.
     *
     * @param array      $fichier        Élément de $_FILES (ex: $_FILES['avatar'])
     * @param string     $dossierCible   Chemin absolu où stocker le fichier
     * @param array|null $extensionsAutorisees Liste d'extensions personnalisée
     *                                          (par défaut : UPLOAD_EXTENSIONS du config)
     * @return array ['succes' => bool, 'fichier' => string|null, 'erreur' => string|null]
     */
    public static function image(array $fichier, string $dossierCible, ?array $extensionsAutorisees = null): array
    {
        // Par défaut, utilise la whitelist globale du config
        $extensions = $extensionsAutorisees ?? UPLOAD_EXTENSIONS;

        // -----------------------------------------------------------------
        // 1. Aucun fichier envoyé ?
        // -----------------------------------------------------------------
        if (!isset($fichier['error']) || $fichier['error'] === UPLOAD_ERR_NO_FILE) {
            return ['succes' => false, 'fichier' => null,
                    'erreur' => 'Aucun fichier sélectionné.'];
        }

        // -----------------------------------------------------------------
        // 2. Erreur d'upload PHP ?
        // -----------------------------------------------------------------
        if ($fichier['error'] !== UPLOAD_ERR_OK) {
            return ['succes' => false, 'fichier' => null,
                    'erreur' => self::messageErreurPHP($fichier['error'])];
        }

        // -----------------------------------------------------------------
        // 3. Taille trop grosse ?
        // -----------------------------------------------------------------
        if ($fichier['size'] > UPLOAD_TAILLE_MAX) {
            $mo = round(UPLOAD_TAILLE_MAX / 1024 / 1024, 1);
            return ['succes' => false, 'fichier' => null,
                    'erreur' => "Le fichier dépasse la taille maximale autorisée ($mo Mo)."];
        }

        // -----------------------------------------------------------------
        // 4. Extension dans la whitelist ?
        // -----------------------------------------------------------------
        // Note : on lit l'extension du nom envoyé par l'utilisateur, mais
        // c'est juste une PRÉ-vérif. La vraie vérif sera le MIME (étape 5).
        $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $extensions, true)) {
            $list = implode(', ', $extensions);
            return ['succes' => false, 'fichier' => null,
                    'erreur' => "Format non autorisé. Formats acceptés : $list."];
        }

        // -----------------------------------------------------------------
        // 5. Type MIME réel ?
        // -----------------------------------------------------------------
        // finfo_file lit les premiers octets du fichier pour identifier
        // son vrai type (vs juste se fier à l'extension qui peut être falsifiée).
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeReel = finfo_file($finfo, $fichier['tmp_name']);
        finfo_close($finfo);

        $mimesAttendus = self::MIMES_AUTORISES[$extension] ?? [];
        if (!in_array($mimeReel, $mimesAttendus, true)) {
            return ['succes' => false, 'fichier' => null,
                    'erreur' => "Le contenu du fichier ne correspond pas à son extension."];
        }

        // -----------------------------------------------------------------
        // 6. Image réellement valide ?
        // -----------------------------------------------------------------
        // getimagesize() tente de décoder l'image et renvoie false si elle
        // est corrompue ou maquillée (par exemple un PHP avec une signature image).
        if (@getimagesize($fichier['tmp_name']) === false) {
            return ['succes' => false, 'fichier' => null,
                    'erreur' => "Le fichier n'est pas une image valide."];
        }

        // -----------------------------------------------------------------
        // 7. Dossier cible existe et est inscriptible ?
        // -----------------------------------------------------------------
        if (!is_dir($dossierCible)) {
            // On essaie de le créer
            if (!mkdir($dossierCible, 0755, true)) {
                return ['succes' => false, 'fichier' => null,
                        'erreur' => "Dossier de destination inaccessible."];
            }
        }

        if (!is_writable($dossierCible)) {
            return ['succes' => false, 'fichier' => null,
                    'erreur' => "Le dossier de destination n'est pas inscriptible."];
        }

        // -----------------------------------------------------------------
        // 8. Génération d'un nom unique aléatoire et déplacement
        // -----------------------------------------------------------------
        // Format : <hash>.<extension>
        // bin2hex(random_bytes(16)) -> 32 caractères hexa imprévisibles
        $nouveauNom = bin2hex(random_bytes(16)) . '.' . $extension;
        $cheminCible = rtrim($dossierCible, '/') . '/' . $nouveauNom;

        if (!move_uploaded_file($fichier['tmp_name'], $cheminCible)) {
            return ['succes' => false, 'fichier' => null,
                    'erreur' => "Échec du déplacement du fichier."];
        }

        // Permissions strictes (lecture seule pour les autres)
        chmod($cheminCible, 0644);

        return [
            'succes'  => true,
            'fichier' => $nouveauNom,
            'erreur'  => null,
        ];
    }

    /**
     * Supprime un fichier uploadé (utile lors d'un changement d'avatar).
     *
     * @param string $nomFichier  Juste le nom (pas le chemin)
     * @param string $dossier     Chemin absolu du dossier
     * @return bool
     */
    public static function supprimer(string $nomFichier, string $dossier): bool
    {
        // Protection contre les chemins traversaux ('../../../etc/passwd')
        $nomFichier = basename($nomFichier);

        $chemin = rtrim($dossier, '/') . '/' . $nomFichier;

        if (is_file($chemin)) {
            return unlink($chemin);
        }

        return false;
    }

    /**
     * Convertit un code d'erreur PHP en message lisible.
     */
    private static function messageErreurPHP(int $code): string
    {
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'Le fichier est trop volumineux.';
            case UPLOAD_ERR_PARTIAL:
                return 'Le fichier n\'a été que partiellement téléchargé.';
            case UPLOAD_ERR_NO_FILE:
                return 'Aucun fichier sélectionné.';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Dossier temporaire manquant côté serveur.';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Échec d\'écriture sur le disque.';
            case UPLOAD_ERR_EXTENSION:
                return 'Extension PHP bloquant l\'upload.';
            default:
                return 'Erreur d\'upload inconnue.';
        }
    }
}
