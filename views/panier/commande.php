<?php
/**
 * views/panier/commande.php
 * ---------------------------------------------------------------------
 * Vue : récapitulatif de la commande + choix d'adresse + paiement.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-4xl mx-auto">

    <a href="<?= url('/panier.php') ?>" class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-primary-600 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Retour au panier
    </a>

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Validation de votre commande</h1>
        <p class="text-gray-600">Vérifiez les informations puis procédez au paiement.</p>
    </div>

    <form method="post" action="<?= url('/paiement.php') ?>" class="space-y-6">
        <?= Csrf::champ() ?>

        <!-- ============= ADRESSE LIVRAISON ============= -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Adresse de livraison
            </h2>

            <div class="space-y-2">
                <?php foreach ($adresses as $a): ?>
                    <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg hover:border-primary-300 transition cursor-pointer <?= (int)$a['id_adresse'] === $idAdresseSel ? 'border-primary-500 bg-primary-50' : '' ?>">
                        <input type="radio" name="id_adresse_livraison" value="<?= (int)$a['id_adresse'] ?>"
                               <?= (int)$a['id_adresse'] === $idAdresseSel ? 'checked' : '' ?>
                               onchange="this.form.method='get'; this.form.action='<?= url('/commande.php') ?>'; this.form.submit()"
                               class="mt-1">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900">
                                <?= h($a['libelle']) ?>
                                <?php if ((int)$a['est_defaut'] === 1): ?>
                                    <span class="text-[10px] bg-primary-100 text-primary-700 px-1.5 py-0.5 rounded uppercase font-bold">Défaut</span>
                                <?php endif; ?>
                            </p>
                            <p class="text-sm text-gray-700">
                                <?= h($a['prenom']) ?> <?= h($a['nom']) ?><br>
                                <?= h($a['rue']) ?> <?= h($a['numero']) ?>
                                <?= $a['complement'] ? ' — ' . h($a['complement']) : '' ?><br>
                                <?= h($a['cp']) ?> <?= h($a['ville']) ?>, <?= h($a['pays']) ?>
                                <?= $a['telephone'] ? ' — ' . h($a['telephone']) : '' ?>
                            </p>
                        </div>
                    </label>
                <?php endforeach; ?>

                <a href="<?= url('/adresse_form.php?retour=commande') ?>"
                   class="block text-center px-3 py-2 border border-dashed border-gray-300 rounded-lg text-sm text-gray-600 hover:bg-gray-50 transition">
                    + Ajouter une nouvelle adresse
                </a>
            </div>
        </div>


        <!-- ============= MODE DE LIVRAISON ============= -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                </svg>
                Mode de livraison
            </h2>

            <?php if (empty($modesLivraison)): ?>
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-800">
                    Aucun mode de livraison disponible pour ce pays / panier. Contactez le support.
                </div>
            <?php else: ?>
                <div class="space-y-2">
                    <?php foreach ($modesLivraison as $m): ?>
                        <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg hover:border-primary-300 transition cursor-pointer <?= (int)$m['id_frais'] === $idFraisSel ? 'border-primary-500 bg-primary-50' : '' ?>">
                            <input type="radio" name="id_frais_port" value="<?= (int)$m['id_frais'] ?>"
                                   <?= (int)$m['id_frais'] === $idFraisSel ? 'checked' : '' ?>
                                   onchange="this.form.method='get'; this.form.action='<?= url('/commande.php') ?>'; this.form.submit()"
                                   class="mt-1">
                            <div class="flex-1">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="font-semibold text-gray-900"><?= h($m['nom']) ?></p>
                                    <p class="font-bold text-gray-900">
                                        <?= (float)$m['prix'] > 0 ? format_prix($m['prix']) : 'Gratuit' ?>
                                    </p>
                                </div>
                                <?php if (!empty($m['delai_jours'])): ?>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Livraison estimée sous <?= (int)$m['delai_jours'] ?> jour<?= (int)$m['delai_jours'] > 1 ? 's' : '' ?> ouvré<?= (int)$m['delai_jours'] > 1 ? 's' : '' ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>


        <!-- ============= CODE PROMO ============= -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Code promo</h2>

            <?php if ($resultatPromo['valide']): ?>
                <div class="flex items-center gap-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-green-800">
                        Code « <strong><?= h($codePromoInput) ?></strong> » appliqué
                        — <strong>-<?= format_prix($resultatPromo['remise']) ?></strong>
                    </p>
                    <input type="hidden" name="code_promo" value="<?= h($codePromoInput) ?>">
                </div>
            <?php else: ?>
                <div class="flex gap-2">
                    <input type="text" name="code_promo" value="<?= h($codePromoInput) ?>"
                           placeholder="Entrez votre code promo"
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 transition uppercase">
                    <button type="submit" formmethod="post" formaction="<?= url('/commande.php') ?>"
                            class="px-5 py-2 bg-gray-800 text-white font-semibold rounded-lg hover:bg-gray-900 transition text-sm">
                        Appliquer
                    </button>
                </div>
                <?php if ($codePromoInput !== '' && !empty($resultatPromo['message'])): ?>
                    <p class="text-xs text-red-600 mt-2"><?= h($resultatPromo['message']) ?></p>
                <?php endif; ?>
            <?php endif; ?>
        </div>


        <!-- ============= MÉTHODE PAIEMENT ============= -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-2">Mode de paiement</h2>
            <p class="text-xs text-amber-700 mb-4 bg-amber-50 p-2 rounded">
                💡 Paiement simulé pour les besoins du projet. Aucune vraie carte n'est requise.
            </p>

            <div class="space-y-2">
                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:border-primary-300 cursor-pointer">
                    <input type="radio" name="methode_paiement" value="carte" checked>
                    <span class="text-sm font-medium">💳 Carte bancaire</span>
                </label>
                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:border-primary-300 cursor-pointer">
                    <input type="radio" name="methode_paiement" value="paypal">
                    <span class="text-sm font-medium">🅿️ PayPal</span>
                </label>
                <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:border-primary-300 cursor-pointer">
                    <input type="radio" name="methode_paiement" value="virement">
                    <span class="text-sm font-medium">🏦 Virement bancaire</span>
                </label>
            </div>
        </div>


        <!-- ============= RÉCAPITULATIF ============= -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Récapitulatif</h2>

            <div class="space-y-3 mb-4">
                <?php foreach ($panier['lignes'] as $ligne): ?>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-700">
                            <?= h($ligne['article']['nom']) ?>
                            <span class="text-gray-500">× <?= $ligne['quantite'] ?></span>
                        </span>
                        <span class="text-gray-900 font-medium"><?= format_prix($ligne['sous_total']) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="border-t border-gray-200 pt-4 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">Sous-total</span>
                    <span class="font-medium"><?= format_prix($panier['sous_total']) ?></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">
                        Frais de port
                        <?php if ($modeChoisi): ?>(<?= h($modeChoisi['nom']) ?>)<?php endif; ?>
                    </span>
                    <span class="font-medium">
                        <?= $montantFraisPort > 0 ? format_prix($montantFraisPort) : 'Offerts' ?>
                    </span>
                </div>
                <?php if ($resultatPromo['valide'] && $resultatPromo['remise'] > 0): ?>
                    <div class="flex justify-between text-green-700">
                        <span>Remise code promo</span>
                        <span class="font-medium">- <?= format_prix($resultatPromo['remise']) ?></span>
                    </div>
                <?php endif; ?>
                <div class="border-t border-gray-200 pt-2 flex justify-between text-base">
                    <span class="font-bold">Total à payer</span>
                    <span class="font-bold text-xl"><?= format_prix($totalFinal) ?></span>
                </div>
            </div>

            <button type="submit"
                    class="mt-5 w-full px-5 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                Payer <?= format_prix($totalFinal) ?>
            </button>
        </div>
    </form>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
