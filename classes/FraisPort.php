<?php
/**
 * classes/FraisPort.php
 * ---------------------------------------------------------------------
 * Modèle "FraisPort" : grille des frais de livraison.
 *
 * Calcule automatiquement les frais de port selon le pays et le montant
 * du panier (ex: livraison offerte dès 50€).
 * ---------------------------------------------------------------------
 */

class FraisPort
{
    /**
     * Trouve les frais de port applicables à une commande.
     *
     * @param string $pays
     * @param float  $montantPanier
     * @return array|null Frais de port le moins cher applicable
     */
    public static function calculer(string $pays, float $montantPanier): ?array
    {
        $req = Db::pdo()->prepare(
            "SELECT * FROM frais_port
             WHERE actif = 1
               AND pays = ?
               AND (montant_min_panier IS NULL OR ? >= montant_min_panier)
               AND (montant_max_panier IS NULL OR ? <= montant_max_panier)
             ORDER BY prix ASC
             LIMIT 1"
        );
        $req->execute([$pays, $montantPanier, $montantPanier]);
        $row = $req->fetch();
        return $row ?: null;
    }

    /**
     * Liste TOUS les modes de livraison disponibles pour ce pays et ce panier.
     * Utilisé lors de la commande pour laisser le client choisir.
     *
     * @param string $pays
     * @param float  $montantPanier
     * @return array Liste des grilles applicables (peut être vide)
     */
    public static function listerDisponibles(string $pays, float $montantPanier): array
    {
        $req = Db::pdo()->prepare(
            "SELECT * FROM frais_port
             WHERE actif = 1
               AND pays = ?
               AND (montant_min_panier IS NULL OR ? >= montant_min_panier)
               AND (montant_max_panier IS NULL OR ? <= montant_max_panier)
             ORDER BY prix ASC"
        );
        $req->execute([$pays, $montantPanier, $montantPanier]);
        return $req->fetchAll();
    }

    /**
     * Trouve une grille de frais de port par son ID.
     * Sécurité : vérifie aussi qu'elle est active et que les contraintes
     * pays + panier sont respectées (anti-tampering).
     */
    public static function trouverApplicable(int $idFrais, string $pays, float $montantPanier): ?array
    {
        $req = Db::pdo()->prepare(
            "SELECT * FROM frais_port
             WHERE id_frais = ?
               AND actif = 1
               AND pays = ?
               AND (montant_min_panier IS NULL OR ? >= montant_min_panier)
               AND (montant_max_panier IS NULL OR ? <= montant_max_panier)
             LIMIT 1"
        );
        $req->execute([$idFrais, $pays, $montantPanier, $montantPanier]);
        $row = $req->fetch();
        return $row ?: null;
    }

    /**
     * Liste tous les frais de port (admin).
     */
    public static function listerTous(): array
    {
        return Db::pdo()->query(
            "SELECT * FROM frais_port ORDER BY pays, prix"
        )->fetchAll();
    }
}
