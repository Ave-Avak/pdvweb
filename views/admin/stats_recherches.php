<?php
/**
 * views/admin/stats_recherches.php
 * ---------------------------------------------------------------------
 * Vue admin : analyse des recherches utilisateurs.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-5xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — recherches.
    </div>

    <div class="mb-6 flex justify-between items-center flex-wrap gap-3">
        <h1 class="text-3xl font-bold text-gray-900">Recherches utilisateurs</h1>

        <!-- Sélecteur de période -->
        <form method="get" action="<?= url('/admin/stats_recherches.php') ?>" class="flex items-center gap-2">
            <label class="text-sm text-gray-700">Période :</label>
            <select name="jours" onchange="this.form.submit()" class="px-3 py-1.5 border border-gray-300 rounded text-sm">
                <option value="7"   <?= $jours === 7   ? 'selected' : '' ?>>7 jours</option>
                <option value="30"  <?= $jours === 30  ? 'selected' : '' ?>>30 jours</option>
                <option value="90"  <?= $jours === 90  ? 'selected' : '' ?>>90 jours</option>
                <option value="365" <?= $jours === 365 ? 'selected' : '' ?>>1 an</option>
            </select>
        </form>
    </div>

    <!-- KPI -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <p class="text-xs text-gray-500 mb-1">Total recherches sur <?= $jours ?> jours</p>
        <p class="text-3xl font-bold text-gray-900"><?= (int)$nbTotal ?></p>
    </div>


    <!-- Top recherches -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Top 25 termes recherchés</h2>

        <?php if (empty($topRech)): ?>
            <p class="text-gray-500 text-sm">Aucune recherche sur cette période.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-3 py-2 font-semibold">Terme</th>
                            <th class="text-left px-3 py-2 font-semibold">Contexte</th>
                            <th class="text-center px-3 py-2 font-semibold">Nb</th>
                            <th class="text-center px-3 py-2 font-semibold">Résultats moy.</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($topRech as $r): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 font-medium text-gray-900"><?= h($r['terme']) ?></td>
                                <td class="px-3 py-2">
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-700 text-xs rounded">
                                        <?= h($r['contexte']) ?>
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-center font-semibold"><?= (int)$r['nb'] ?></td>
                                <td class="px-3 py-2 text-center text-gray-500">
                                    <?= $r['moy_resultats'] !== null ? round((float)$r['moy_resultats'], 1) : '-' ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>


    <!-- Recherches sans résultat -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h2 class="text-lg font-bold text-gray-900 mb-2">Recherches sans résultat</h2>
        <p class="text-sm text-gray-600 mb-4">
            🔍 Opportunités : termes que les utilisateurs cherchent mais qui ne retournent rien.
        </p>

        <?php if (empty($sansResult)): ?>
            <p class="text-gray-500 text-sm">Aucune recherche infructueuse — toutes les recherches retournent des résultats. 🎉</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-3 py-2 font-semibold">Terme</th>
                            <th class="text-left px-3 py-2 font-semibold">Contexte</th>
                            <th class="text-center px-3 py-2 font-semibold">Nb</th>
                            <th class="text-left px-3 py-2 font-semibold">Dernière</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($sansResult as $r): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 font-medium text-gray-900"><?= h($r['terme']) ?></td>
                                <td class="px-3 py-2">
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-700 text-xs rounded">
                                        <?= h($r['contexte']) ?>
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-center font-semibold text-amber-600"><?= (int)$r['nb'] ?></td>
                                <td class="px-3 py-2 text-xs text-gray-500"><?= h(format_date_relative($r['derniere'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
