<?php
/**
 * classes/util/Flash.php
 * ---------------------------------------------------------------------
 * Messages "flash" : messages courts à afficher sur la PROCHAINE page,
 * puis qui disparaissent.
 *
 * Cas d'usage typique : après un POST réussi, on redirige vers une
 * autre page (pattern POST-REDIRECT-GET) et on veut afficher un
 * "Profil mis à jour avec succès".
 *
 * Utilisation :
 *
 *      // Dans une page qui traite un POST :
 *      Flash::succes('Profil mis à jour avec succès.');
 *      header('Location: profil.php');
 *      exit;
 *
 *      // Dans le header.php ou la page de destination :
 *      Flash::afficher();
 * ---------------------------------------------------------------------
 */

class Flash
{
    /**
     * Clé de stockage en session.
     */
    private const SESSION_KEY = '_flash_messages';

    /**
     * Types de messages supportés (correspond à des classes Tailwind).
     */
    public const SUCCES = 'succes';
    public const ERREUR = 'erreur';
    public const INFO   = 'info';
    public const AVERT  = 'avertissement';

    /**
     * Enregistre un message qui sera affiché à la prochaine page.
     *
     * @param string $type    L'un de : succes, erreur, info, avertissement
     * @param string $message Le texte à afficher
     */
    public static function ajouter(string $type, string $message): void
    {
        $_SESSION[self::SESSION_KEY][] = [
            'type'    => $type,
            'message' => $message,
        ];
    }

    /**
     * Raccourci : message de succès (vert).
     */
    public static function succes(string $message): void
    {
        self::ajouter(self::SUCCES, $message);
    }

    /**
     * Raccourci : message d'erreur (rouge).
     */
    public static function erreur(string $message): void
    {
        self::ajouter(self::ERREUR, $message);
    }

    /**
     * Raccourci : message d'information (bleu).
     */
    public static function info(string $message): void
    {
        self::ajouter(self::INFO, $message);
    }

    /**
     * Raccourci : message d'avertissement (orange).
     */
    public static function avertissement(string $message): void
    {
        self::ajouter(self::AVERT, $message);
    }

    /**
     * Récupère tous les messages enregistrés et les efface de la session.
     * À appeler depuis le header pour les afficher.
     *
     * @return array Tableau de messages ['type' => ..., 'message' => ...]
     */
    public static function consommerTout(): array
    {
        $messages = $_SESSION[self::SESSION_KEY] ?? [];
        unset($_SESSION[self::SESSION_KEY]);
        return $messages;
    }

    /**
     * Affiche directement les messages au format HTML (Tailwind).
     * Cette méthode est appelée depuis includes/header.php.
     */
    public static function afficher(): void
    {
        $messages = self::consommerTout();
        if (empty($messages)) {
            return;
        }

        // Correspondance type -> classes Tailwind
        $classes = [
            self::SUCCES => 'bg-green-50 border-green-400 text-green-800',
            self::ERREUR => 'bg-red-50 border-red-400 text-red-800',
            self::INFO   => 'bg-blue-50 border-blue-400 text-blue-800',
            self::AVERT  => 'bg-orange-50 border-orange-400 text-orange-800',
        ];

        // Icône SVG par type (heroicons mini)
        $icones = [
            self::SUCCES => '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>',
            self::ERREUR => '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>',
            self::INFO   => '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>',
            self::AVERT  => '<svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>',
        ];

        echo '<div class="container mx-auto px-4 mt-4 space-y-2">';
        foreach ($messages as $msg) {
            $type = $msg['type'];
            $classe = $classes[$type] ?? $classes[self::INFO];
            $icone  = $icones[$type] ?? $icones[self::INFO];
            $texte  = htmlspecialchars($msg['message'], ENT_QUOTES, 'UTF-8');

            echo '<div class="flex items-center gap-3 border-l-4 ' . $classe
               . ' px-4 py-3 rounded-r-md shadow-sm" role="alert">'
               . $icone
               . '<span class="text-sm font-medium">' . $texte . '</span>'
               . '</div>';
        }
        echo '</div>';
    }
}
