<?php
/**
 * classes/NoteArticle.php
 * ---------------------------------------------------------------------
 * Modèle "NoteArticle" : avis et notes des membres sur les articles.
 *
 * Un membre peut noter de 1 à 5 étoiles et laisser un commentaire.
 * Clé composite (id_membre, id_article) : un seul avis par paire.
 *
 * IMPORTANT : seul un membre AYANT ACHETÉ l'article peut le noter
 *             (vérification dans le contrôleur, pas dans le modèle).
 * ---------------------------------------------------------------------
 */

class NoteArticle
{
    /**
     * Récupère la note d'un membre sur un article (s'il en a déjà laissée une).
     */
    public static function noteDuMembre(int $idMembre, int $idArticle): ?array
    {
        $req = Db::pdo()->prepare(
            "SELECT * FROM note_article WHERE id_membre = ? AND id_article = ?"
        );
        $req->execute([$idMembre, $idArticle]);
        $row = $req->fetch();
        return $row ?: null;
    }

    /**
     * Liste les notes d'un article, avec infos membre.
     */
    public static function listerParArticle(int $idArticle): array
    {
        $req = Db::pdo()->prepare(
            "SELECT n.*,
                    m.prenom, m.nom, m.avatar, m.date_anonymisation
             FROM note_article n
             INNER JOIN membre m ON m.id_membre = n.id_membre
             WHERE n.id_article = ?
             ORDER BY n.date_note DESC"
        );
        $req->execute([$idArticle]);
        return $req->fetchAll();
    }

    /**
     * Statistiques sur un article : note moyenne + nb_notes.
     */
    public static function statistiques(int $idArticle): array
    {
        $req = Db::pdo()->prepare(
            "SELECT
                ROUND(AVG(note), 1) AS moyenne,
                COUNT(*)            AS nb_notes
             FROM note_article WHERE id_article = ?"
        );
        $req->execute([$idArticle]);
        $row = $req->fetch();
        return [
            'moyenne'  => $row['moyenne'] ? (float)$row['moyenne'] : null,
            'nb_notes' => (int)($row['nb_notes'] ?? 0),
        ];
    }

    /**
     * Crée ou met à jour la note d'un membre.
     */
    public static function enregistrer(int $idMembre, int $idArticle, int $note, ?string $commentaire = null): bool
    {
        if ($note < 1 || $note > 5) {
            return false;
        }

        $existant = self::noteDuMembre($idMembre, $idArticle);

        if ($existant) {
            $req = Db::pdo()->prepare(
                "UPDATE note_article SET note = ?, commentaire = ?, date_note = NOW()
                 WHERE id_membre = ? AND id_article = ?"
            );
            return $req->execute([$note, $commentaire, $idMembre, $idArticle]);
        }

        $req = Db::pdo()->prepare(
            "INSERT INTO note_article (id_membre, id_article, note, commentaire)
             VALUES (?, ?, ?, ?)"
        );
        return $req->execute([$idMembre, $idArticle, $note, $commentaire]);
    }

    /**
     * Le membre a-t-il acheté cet article au moins une fois ?
     * (Pré-requis pour pouvoir noter.)
     */
    public static function aAcheteArticle(int $idMembre, int $idArticle): bool
    {
        $req = Db::pdo()->prepare(
            "SELECT COUNT(*)
             FROM ligne_facture lf
             INNER JOIN achat_facture f ON f.id_facture = lf.id_facture
             WHERE f.id_membre = ? AND lf.id_article = ?"
        );
        $req->execute([$idMembre, $idArticle]);
        return $req->fetchColumn() > 0;
    }
}
