<?php
/**
 * views/comparer.php
 * ---------------------------------------------------------------------
 * Vue : comparateur d'articles côte à côte.
 *
 * Variables :
 *   - $articlesACompar  array  Liste d'articles (max 4)
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-7xl mx-auto">

    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Comparateur</h1>
            <p class="text-gray-600 text-sm">
                <?php if (!empty($articlesACompar)): ?>
                    <?= count($articlesACompar) ?> article<?= count($articlesACompar) > 1 ? 's' : '' ?> à comparer
                    (maximum 4)
                <?php else: ?>
                    Sélectionnez des articles depuis le catalogue
                <?php endif; ?>
            </p>
        </div>
        <?php if (!empty($articlesACompar)): ?>
            <div class="flex items-center gap-2">
                <a href="<?= url('/catalogue.php') ?>"
                   class="px-4 py-2 bg-white border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition text-sm">
                    + Ajouter des articles
                </a>
                <a href="<?= url('/comparer.php?clear=1') ?>"
                   class="px-4 py-2 bg-danger-50 text-danger-700 font-semibold rounded-lg hover:bg-danger-100 transition text-sm">
                    Vider
                </a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (empty($articlesACompar)): ?>
        <!-- État vide -->
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="inline-flex w-20 h-20 bg-primary-50 rounded-full items-center justify-center mb-4">
                <svg class="w-10 h-10 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Aucun article à comparer</h2>
            <p class="text-gray-600 mb-6 max-w-md mx-auto">
                Parcourez le catalogue et cliquez sur le bouton « Comparer »
                sur les articles que vous voulez confronter (jusqu'à 4).
            </p>
            <a href="<?= url('/catalogue.php') ?>"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
                Voir le catalogue
            </a>
        </div>

    <?php else: ?>

        <!-- ============================================================
             TABLEAU COMPARATIF
        ============================================================ -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <!-- En-tête : images + nom -->
                    <thead>
                        <tr class="border-b-2 border-gray-200">
                            <th class="w-40 p-4 bg-gray-50 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                Critère
                            </th>
                            <?php foreach ($articlesACompar as $a): ?>
                                <th class="p-4 min-w-[200px]">
                                    <div class="relative">
                                        <!-- Bouton retirer -->
                                        <a href="<?= url('/comparer.php?remove=' . (int)$a['id_article']) ?>"
                                           title="Retirer du comparateur"
                                           class="absolute -top-1 -right-1 w-7 h-7 bg-danger-100 text-danger-700 rounded-full flex items-center justify-center hover:bg-danger-200 transition z-10">
                                            ×
                                        </a>

                                        <!-- Image -->
                                        <a href="<?= url('/article.php?id=' . (int)$a['id_article']) ?>" class="block">
                                            <div class="aspect-square bg-gray-100 rounded-lg overflow-hidden mb-3">
                                                <img src="<?= h(asset_article($a['image'])) ?>"
                                                     alt="<?= h($a['nom']) ?>"
                                                     class="w-full h-full object-cover hover:scale-105 transition duration-300">
                                            </div>
                                        </a>

                                        <!-- Nom + lien -->
                                        <a href="<?= url('/article.php?id=' . (int)$a['id_article']) ?>"
                                           class="text-sm font-semibold text-gray-900 hover:text-primary-600 transition line-clamp-2 normal-case tracking-normal">
                                            <?= h($a['nom']) ?>
                                        </a>
                                    </div>
                                </th>
                            <?php endforeach; ?>

                            <!-- Cellules vides pour atteindre 4 colonnes -->
                            <?php for ($i = count($articlesACompar); $i < 4; $i++): ?>
                                <th class="p-4 min-w-[200px]">
                                    <a href="<?= url('/catalogue.php') ?>"
                                       class="block aspect-square bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center hover:border-primary-400 hover:bg-primary-50 transition">
                                        <div class="text-center">
                                            <svg class="w-10 h-10 mx-auto text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                            </svg>
                                            <p class="text-xs text-gray-500 mt-2 normal-case font-normal tracking-normal">Ajouter</p>
                                        </div>
                                    </a>
                                </th>
                            <?php endfor; ?>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100">
                        <!-- Prix -->
                        <tr>
                            <td class="p-4 bg-gray-50 font-semibold text-gray-700 text-sm">Prix</td>
                            <?php foreach ($articlesACompar as $a): ?>
                                <td class="p-4 text-center">
                                    <span class="text-2xl font-bold text-gray-900"><?= format_prix($a['prix']) ?></span>
                                </td>
                            <?php endforeach; ?>
                            <?php for ($i = count($articlesACompar); $i < 4; $i++): ?>
                                <td class="p-4 text-center text-gray-300">—</td>
                            <?php endfor; ?>
                        </tr>

                        <!-- Catégorie -->
                        <tr>
                            <td class="p-4 bg-gray-50 font-semibold text-gray-700 text-sm">Catégorie</td>
                            <?php foreach ($articlesACompar as $a): ?>
                                <td class="p-4 text-center text-sm text-gray-700">
                                    <?= h($a['categorie_nom']) ?>
                                </td>
                            <?php endforeach; ?>
                            <?php for ($i = count($articlesACompar); $i < 4; $i++): ?>
                                <td class="p-4 text-center text-gray-300">—</td>
                            <?php endfor; ?>
                        </tr>

                        <!-- Note moyenne -->
                        <tr>
                            <td class="p-4 bg-gray-50 font-semibold text-gray-700 text-sm">Note</td>
                            <?php foreach ($articlesACompar as $a): ?>
                                <td class="p-4 text-center">
                                    <?php if ((int)$a['nb_notes'] > 0): ?>
                                        <div class="text-amber-500 text-sm">
                                            <?= str_repeat('★', (int)round($a['note_moyenne'])) ?><?= str_repeat('☆', 5 - (int)round($a['note_moyenne'])) ?>
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">
                                            <?= number_format((float)$a['note_moyenne'], 1, ',', '') ?>/5
                                            (<?= (int)$a['nb_notes'] ?>)
                                        </div>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">Pas encore noté</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <?php for ($i = count($articlesACompar); $i < 4; $i++): ?>
                                <td class="p-4 text-center text-gray-300">—</td>
                            <?php endfor; ?>
                        </tr>

                        <!-- Stock -->
                        <tr>
                            <td class="p-4 bg-gray-50 font-semibold text-gray-700 text-sm">Disponibilité</td>
                            <?php foreach ($articlesACompar as $a): ?>
                                <td class="p-4 text-center">
                                    <?php if ((int)$a['stock'] > 0): ?>
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-success-50 text-success-700 text-xs font-semibold rounded">
                                            ✓ En stock
                                            <?php if ((int)$a['stock'] < 5): ?>
                                                (<?= (int)$a['stock'] ?>)
                                            <?php endif; ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 bg-danger-50 text-danger-700 text-xs font-semibold rounded">
                                            Rupture
                                        </span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <?php for ($i = count($articlesACompar); $i < 4; $i++): ?>
                                <td class="p-4 text-center text-gray-300">—</td>
                            <?php endfor; ?>
                        </tr>

                        <!-- Poids -->
                        <tr>
                            <td class="p-4 bg-gray-50 font-semibold text-gray-700 text-sm">Poids</td>
                            <?php foreach ($articlesACompar as $a): ?>
                                <td class="p-4 text-center text-sm text-gray-700">
                                    <?php if (!empty($a['poids_grammes'])): ?>
                                        <?= number_format((float)$a['poids_grammes'], 0, ',', ' ') ?> g
                                    <?php else: ?>
                                        <span class="text-gray-400">—</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <?php for ($i = count($articlesACompar); $i < 4; $i++): ?>
                                <td class="p-4 text-center text-gray-300">—</td>
                            <?php endfor; ?>
                        </tr>

                        <!-- Description courte -->
                        <tr>
                            <td class="p-4 bg-gray-50 font-semibold text-gray-700 text-sm">Description</td>
                            <?php foreach ($articlesACompar as $a): ?>
                                <td class="p-4 text-xs text-gray-600 align-top">
                                    <?php if (!empty($a['description'])): ?>
                                        <p class="line-clamp-4">
                                            <?= h(mb_substr(strip_tags($a['description']), 0, 200)) ?>
                                            <?= mb_strlen($a['description']) > 200 ? '…' : '' ?>
                                        </p>
                                    <?php else: ?>
                                        <span class="text-gray-400">—</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <?php for ($i = count($articlesACompar); $i < 4; $i++): ?>
                                <td class="p-4 text-center text-gray-300">—</td>
                            <?php endfor; ?>
                        </tr>

                        <!-- Ventes -->
                        <tr>
                            <td class="p-4 bg-gray-50 font-semibold text-gray-700 text-sm">Popularité</td>
                            <?php foreach ($articlesACompar as $a): ?>
                                <td class="p-4 text-center text-sm text-gray-700">
                                    <?php if ((int)$a['nb_ventes'] > 0): ?>
                                        <span><?= (int)$a['nb_ventes'] ?> vente<?= $a['nb_ventes'] > 1 ? 's' : '' ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-400">Nouveau</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            <?php for ($i = count($articlesACompar); $i < 4; $i++): ?>
                                <td class="p-4 text-center text-gray-300">—</td>
                            <?php endfor; ?>
                        </tr>

                        <!-- Boutons d'action -->
                        <tr>
                            <td class="p-4 bg-gray-50"></td>
                            <?php foreach ($articlesACompar as $a): ?>
                                <td class="p-4 text-center">
                                    <a href="<?= url('/article.php?id=' . (int)$a['id_article']) ?>"
                                       class="inline-flex items-center gap-1 px-4 py-2 bg-primary-600 text-white text-sm font-semibold rounded-lg hover:bg-primary-700 transition w-full justify-center">
                                        Voir la fiche
                                    </a>
                                </td>
                            <?php endforeach; ?>
                            <?php for ($i = count($articlesACompar); $i < 4; $i++): ?>
                                <td class="p-4 text-center"></td>
                            <?php endfor; ?>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- URL partage -->
        <div class="mt-4 bg-info-50 border border-info-200 rounded-lg p-3 text-sm text-info-800 flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z"/>
            </svg>
            <span>
                Lien à partager pour cette comparaison :
                <a href="<?= url('/comparer.php?ids=' . implode(',', array_map(fn($a) => (int)$a['id_article'], $articlesACompar))) ?>"
                   class="font-semibold underline break-all">
                    /comparer.php?ids=<?= h(implode(',', array_map(fn($a) => (int)$a['id_article'], $articlesACompar))) ?>
                </a>
            </span>
        </div>

    <?php endif; ?>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
