<?php
/**
 * views/auth/facture.php
 * ---------------------------------------------------------------------
 * Vue : détail d'une facture (achat).
 *
 * Variables :
 *   - $facture, $lignes, $paiements, $adresseLiv, $adresseFact, $confirmation
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-3xl mx-auto">

    <?php if ($confirmation): ?>
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 rounded-r-md" data-confetti>
            <div class="flex items-center gap-3">
                <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="font-bold text-green-900">🎉 Commande confirmée !</p>
                    <p class="text-sm text-green-800">
                        Votre commande a été enregistrée. Référence : <strong><?= h($facture['reference']) ?></strong>.
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <a href="<?= url('/historique.php') ?>" class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-primary-600 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Mes commandes
    </a>


    <!-- En-tête facture -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex justify-between items-start mb-4 flex-wrap gap-3">
            <div>
                <p class="text-sm text-gray-500">Facture</p>
                <h1 class="text-2xl font-bold text-gray-900"><?= h($facture['reference']) ?></h1>
                <p class="text-sm text-gray-600 mt-1">
                    Commande passée le <?= h(format_date($facture['date_achat'])) ?>
                </p>
            </div>
            <div>
                <?php
                $couleur = $facture['statut_couleur'] ?: '#6b7280';
                ?>
                <span class="px-3 py-1.5 rounded-full text-xs font-semibold uppercase"
                      style="background-color: <?= h($couleur) ?>20; color: <?= h($couleur) ?>; border: 1px solid <?= h($couleur) ?>40;">
                    <?= h($facture['statut_nom']) ?>
                </span>
            </div>
        </div>

        <!-- Bouton imprimer -->
        <button onclick="window.print()"
                class="text-xs px-3 py-1.5 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition print:hidden">
            🖨️ Imprimer
        </button>
    </div>


    <!-- Adresses -->
    <div class="grid md:grid-cols-2 gap-4 mb-6">
        <?php if ($adresseLiv): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="text-xs font-bold text-gray-500 uppercase mb-2">Livraison</h3>
                <p class="text-sm">
                    <?= h($adresseLiv['prenom']) ?> <?= h($adresseLiv['nom']) ?><br>
                    <?= h($adresseLiv['rue']) ?> <?= h($adresseLiv['numero']) ?><br>
                    <?= h($adresseLiv['cp']) ?> <?= h($adresseLiv['ville']) ?><br>
                    <?= h($adresseLiv['pays']) ?>
                </p>
            </div>
        <?php endif; ?>
        <?php if ($adresseFact && (int)$adresseFact['id_adresse'] !== (int)$adresseLiv['id_adresse']): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="text-xs font-bold text-gray-500 uppercase mb-2">Facturation</h3>
                <p class="text-sm">
                    <?= h($adresseFact['prenom']) ?> <?= h($adresseFact['nom']) ?><br>
                    <?= h($adresseFact['rue']) ?> <?= h($adresseFact['numero']) ?><br>
                    <?= h($adresseFact['cp']) ?> <?= h($adresseFact['ville']) ?><br>
                    <?= h($adresseFact['pays']) ?>
                </p>
            </div>
        <?php endif; ?>
    </div>


    <!-- Articles -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-semibold text-gray-700">Article</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-700">Qté</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-700">Prix unit.</th>
                    <th class="text-right px-4 py-3 font-semibold text-gray-700">Sous-total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($lignes as $l): ?>
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900"><?= h($l['article_nom']) ?></p>
                            <p class="text-xs text-gray-500"><?= h($l['categorie_nom']) ?></p>
                        </td>
                        <td class="px-4 py-3 text-center"><?= (int)$l['quantite'] ?></td>
                        <td class="px-4 py-3 text-right"><?= format_prix($l['prix_unitaire']) ?></td>
                        <td class="px-4 py-3 text-right font-medium"><?= format_prix($l['quantite'] * $l['prix_unitaire']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot class="bg-gray-50 border-t border-gray-200">
                <tr>
                    <td colspan="3" class="px-4 py-2 text-right text-sm text-gray-600">Sous-total</td>
                    <td class="px-4 py-2 text-right font-medium"><?= format_prix($facture['sous_total']) ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="px-4 py-2 text-right text-sm text-gray-600">
                        Frais de port
                        <?php if (!empty($facture['mode_livraison'])): ?>
                            <span class="text-xs">(<?= h($facture['mode_livraison']) ?>)</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-2 text-right font-medium"><?= format_prix($facture['montant_frais_port']) ?></td>
                </tr>
                <?php if ((float)$facture['montant_remise'] > 0): ?>
                    <tr class="text-green-700">
                        <td colspan="3" class="px-4 py-2 text-right text-sm">Remise</td>
                        <td class="px-4 py-2 text-right font-medium">- <?= format_prix($facture['montant_remise']) ?></td>
                    </tr>
                <?php endif; ?>
                <tr class="text-base">
                    <td colspan="3" class="px-4 py-3 text-right font-bold">TOTAL</td>
                    <td class="px-4 py-3 text-right font-bold text-xl"><?= format_prix($facture['prix_total']) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>


    <!-- Paiement -->
    <?php if (!empty($paiements)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <h3 class="text-xs font-bold text-gray-500 uppercase mb-3">Paiement</h3>
            <?php foreach ($paiements as $p): ?>
                <div class="flex justify-between items-center text-sm">
                    <span>
                        <?= h(ucfirst($p['methode'])) ?>
                        <?php if (!empty($p['reference_ext'])): ?>
                            <span class="text-xs text-gray-500">— Réf. <?= h($p['reference_ext']) ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="font-medium"><?= format_prix($p['montant']) ?></span>
                </div>
                <p class="text-xs text-gray-500 mt-1"><?= h(format_date($p['date_paiement'])) ?> — <?= h(ucfirst($p['statut'])) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
