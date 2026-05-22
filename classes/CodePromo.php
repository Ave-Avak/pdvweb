<?php
/**
 * classes/CodePromo.php
 * ---------------------------------------------------------------------
 * Modèle "CodePromo" : codes de réduction.
 *
 * Vérifie validité (date, actif, montant min) et calcule la remise.
 * Trace les utilisations pour respecter le quota par code/par membre.
 * ---------------------------------------------------------------------
 */

class CodePromo
{
    /**
     * Trouve un code promo par son code, en vérifiant qu'il est actif et valide.
     *
     * @param string $code
     * @return array|null
     */
    public static function trouverActif(string $code): ?array
    {
        $req = Db::pdo()->prepare(
            "SELECT * FROM code_promo
             WHERE code = ?
               AND actif = 1
               AND date_debut <= NOW()
               AND date_fin >= NOW()"
        );
        $req->execute([$code]);
        $row = $req->fetch();
        return $row ?: null;
    }

    /**
     * Vérifie si un code peut être utilisé par ce membre pour ce panier.
     * Retourne le montant de la remise applicable.
     *
     * @return array ['valide' => bool, 'remise' => float, 'message' => string, 'code' => array|null]
     */
    public static function appliquer(string $code, int $idMembre, float $sousTotal): array
    {
        $code = trim($code);
        if ($code === '') {
            return ['valide' => false, 'remise' => 0, 'message' => '', 'code' => null];
        }

        $codePromo = self::trouverActif($code);
        if (!$codePromo) {
            return ['valide' => false, 'remise' => 0,
                    'message' => 'Code promo invalide ou expiré.', 'code' => null];
        }

        // Vérification du montant minimum
        if ($sousTotal < (float)$codePromo['montant_min_panier']) {
            return ['valide' => false, 'remise' => 0,
                    'message' => 'Le panier doit atteindre au moins '
                                . format_prix($codePromo['montant_min_panier'])
                                . ' pour utiliser ce code.',
                    'code' => null];
        }

        // Quota global
        if ($codePromo['utilisations_max'] !== null) {
            $reqGlobal = Db::pdo()->prepare(
                "SELECT COUNT(*) FROM code_promo_utilisation WHERE id_code = ?"
            );
            $reqGlobal->execute([$codePromo['id_code']]);
            if ((int)$reqGlobal->fetchColumn() >= (int)$codePromo['utilisations_max']) {
                return ['valide' => false, 'remise' => 0,
                        'message' => 'Ce code promo a atteint sa limite d\'utilisations.',
                        'code' => null];
            }
        }

        // Quota par membre
        if ($codePromo['utilisations_par_membre'] !== null) {
            $reqMembre = Db::pdo()->prepare(
                "SELECT COUNT(*) FROM code_promo_utilisation
                 WHERE id_code = ? AND id_membre = ?"
            );
            $reqMembre->execute([$codePromo['id_code'], $idMembre]);
            if ((int)$reqMembre->fetchColumn() >= (int)$codePromo['utilisations_par_membre']) {
                return ['valide' => false, 'remise' => 0,
                        'message' => 'Vous avez déjà utilisé ce code.', 'code' => null];
            }
        }

        // Calcul de la remise
        $remise = match ($codePromo['type_remise']) {
            'pourcentage'        => round($sousTotal * (float)$codePromo['valeur'] / 100, 2),
            'montant_fixe'       => min((float)$codePromo['valeur'], $sousTotal),
            'livraison_offerte'  => 0.0,  // Géré séparément (frais de port = 0)
            default              => 0.0,
        };

        return [
            'valide'  => true,
            'remise'  => $remise,
            'message' => 'Code appliqué : ' . format_prix($remise) . ' de réduction.',
            'code'    => $codePromo,
        ];
    }

    /**
     * Enregistre l'utilisation d'un code par un membre dans une commande.
     */
    public static function enregistrerUtilisation(int $idCode, int $idMembre, int $idFacture, float $montantRemise): void
    {
        $req = Db::pdo()->prepare(
            "INSERT INTO code_promo_utilisation
                (id_code, id_membre, id_facture, montant_remise)
             VALUES (?, ?, ?, ?)"
        );
        $req->execute([$idCode, $idMembre, $idFacture, $montantRemise]);
    }

    /**
     * Liste tous les codes promo (admin).
     */
    public static function listerTous(): array
    {
        return Db::pdo()->query(
            "SELECT cp.*,
                    (SELECT COUNT(*) FROM code_promo_utilisation WHERE id_code = cp.id_code) AS nb_utilisations
             FROM code_promo cp
             ORDER BY cp.actif DESC, cp.date_debut DESC"
        )->fetchAll();
    }
}
