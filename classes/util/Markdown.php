<?php
/**
 * classes/util/Markdown.php
 * ---------------------------------------------------------------------
 * Parser Markdown maison.
 *
 * Couvre les besoins du projet :
 *   - **gras**             ->  <strong>...</strong>
 *   - _italique_ ou *it*   ->  <em>...</em>
 *   - [texte](url)         ->  <a href="url" target="_blank" rel="noopener">texte</a>
 *   - `code`               ->  <code>...</code>
 *   - - item               ->  <ul><li>...</li></ul>
 *   - 1. item              ->  <ol><li>...</li></ol>
 *   - > citation           ->  <blockquote>...</blockquote>
 *   - ligne vide           ->  séparation de paragraphes
 *
 * Sécurité (anti-XSS) :
 *   - htmlspecialchars appliqué AVANT tout traitement Markdown
 *   - Donc tout HTML brut (<script>, <img onerror>...) est échappé
 *   - Les URL javascript: et data: sont rejetées
 *
 * Volontairement plus simple que Parsedown : on couvre 95% des cas
 * sans embarquer 2000 lignes de code externe. Le code est entièrement
 * maîtrisable et expliquable à l'oral.
 * ---------------------------------------------------------------------
 */

class Markdown
{
    /**
     * Convertit un texte Markdown en HTML sécurisé.
     *
     * @param string $texte
     * @return string HTML prêt à être affiché (déjà échappé)
     */
    public static function vers_html(string $texte): string
    {
        // 1. Échappement HTML PRÉALABLE - protection XSS de base
        // Toute balise/caractère HTML dans le texte source devient inoffensif.
        $texte = htmlspecialchars($texte, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        // 2. Normalisation des sauts de ligne
        $texte = str_replace(["\r\n", "\r"], "\n", $texte);

        // 3. Découpage en blocs (séparés par lignes vides)
        $blocs = preg_split('/\n\s*\n/', trim($texte));

        $html = '';
        foreach ($blocs as $bloc) {
            $bloc = trim($bloc, "\n");
            if ($bloc === '') continue;

            // Un bloc peut contenir une intro suivie d'une liste.
            // On découpe en "groupes" : lignes contigües du même type.
            $html .= self::rendreBloc($bloc);
        }

        return $html;
    }

    /**
     * Rend un bloc qui peut contenir plusieurs sous-types (intro + liste, etc.).
     */
    private static function rendreBloc(string $bloc): string
    {
        $lignes = explode("\n", $bloc);
        $html = '';

        $i = 0;
        $n = count($lignes);
        while ($i < $n) {
            $ligne = $lignes[$i];

            // Citation : > ...
            if (preg_match('/^&gt;\s/', $ligne)) {
                $items = [];
                while ($i < $n && preg_match('/^&gt;\s?/', $lignes[$i])) {
                    $items[] = preg_replace('/^&gt;\s?/', '', $lignes[$i]);
                    $i++;
                }
                $contenu = self::transformerInline(implode("<br>\n", $items));
                $html .= "<blockquote>$contenu</blockquote>\n";
                continue;
            }

            // Liste à puces : - ou *
            if (preg_match('/^[-*]\s/', $ligne)) {
                $items = [];
                while ($i < $n && preg_match('/^[-*]\s+(.*)$/', $lignes[$i], $m)) {
                    $items[] = '<li>' . self::transformerInline($m[1]) . '</li>';
                    $i++;
                }
                $html .= "<ul>\n" . implode("\n", $items) . "\n</ul>\n";
                continue;
            }

            // Liste numérotée : 1. ...
            if (preg_match('/^\d+\.\s/', $ligne)) {
                $items = [];
                while ($i < $n && preg_match('/^\d+\.\s+(.*)$/', $lignes[$i], $m)) {
                    $items[] = '<li>' . self::transformerInline($m[1]) . '</li>';
                    $i++;
                }
                $html .= "<ol>\n" . implode("\n", $items) . "\n</ol>\n";
                continue;
            }

            // Sinon : paragraphe constitué des lignes contigües "normales"
            $paragraphe = [];
            while ($i < $n) {
                $l = $lignes[$i];
                // S'arrêter si on rencontre un nouveau type de bloc
                if (preg_match('/^(&gt;\s|[-*]\s|\d+\.\s)/', $l)) break;
                $paragraphe[] = $l;
                $i++;
            }
            if (!empty($paragraphe)) {
                $contenu = self::transformerInline(implode("<br>\n", $paragraphe));
                $html .= "<p>$contenu</p>\n";
            }
        }

        return $html;
    }

    /**
     * Applique les transformations "inline" (gras, italique, lien, code).
     *
     * @param string $texte  (déjà échappé HTML !)
     * @return string
     */
    private static function transformerInline(string $texte): string
    {
        // Code inline : `code`  ->  <code>code</code>
        // Doit être traité EN PREMIER pour ne pas confondre avec d'autres syntaxes
        $texte = preg_replace_callback('/`([^`\n]+)`/', function ($m) {
            return '<code>' . $m[1] . '</code>';
        }, $texte);

        // Liens [texte](url)
        // Filtrage des URL : on accepte http://, https://, mailto:, ou URL relative.
        // On refuse javascript: et data: (vecteurs XSS classiques).
        $texte = preg_replace_callback(
            '/\[([^\]]+)\]\(([^)\s]+)\)/',
            function ($m) {
                $libelle = $m[1];
                $url     = $m[2];

                if (!self::urlEstSure($url)) {
                    // URL douteuse : on désactive le lien et on garde le texte
                    return '[' . $libelle . '](url bloquée)';
                }

                // target="_blank" + rel="noopener noreferrer" : pratique standard
                // pour les liens externes (sécurité + UX).
                return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer">'
                     . $libelle . '</a>';
            },
            $texte
        );

        // Gras **texte**  ->  <strong>
        $texte = preg_replace('/\*\*([^*\n]+?)\*\*/', '<strong>$1</strong>', $texte);

        // Italique _texte_  ou  *texte*  (mais pas si déjà gras consommé au-dessus)
        $texte = preg_replace('/(?<!\w)_([^_\n]+?)_(?!\w)/', '<em>$1</em>', $texte);
        $texte = preg_replace('/(?<!\*)\*([^*\n]+?)\*(?!\*)/', '<em>$1</em>', $texte);

        return $texte;
    }

    /**
     * Une URL est-elle sûre pour un href ?
     */
    private static function urlEstSure(string $url): bool
    {
        $url = trim($url);
        if ($url === '') return false;

        // URL relative (ex: /blog.php) ou ancre (#section) : OK
        if ($url[0] === '/' || $url[0] === '#') return true;

        // URL absolue : doit utiliser un schéma autorisé
        $schema = strtolower(parse_url($url, PHP_URL_SCHEME) ?? '');
        return in_array($schema, ['http', 'https', 'mailto'], true);
    }
}
