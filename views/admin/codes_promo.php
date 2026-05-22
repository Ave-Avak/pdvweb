<?php
/**
 * views/admin/codes_promo.php
 * ---------------------------------------------------------------------
 * Vue admin : liste des codes promo avec activation/désactivation.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-5xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — codes promo.
    </div>

    <h1 class="text-3xl font-bold text-gray-900 mb-6">Codes promo</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Code</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Type</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-700">Valeur</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Validité</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-700">Utilisations</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-700">État</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-700">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($codes as $c): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono font-semibold"><?= h($c['code']) ?></td>
                            <td class="px-4 py-3 text-gray-700"><?= h($c['type_remise']) ?></td>
                            <td class="px-4 py-3 text-right">
                                <?= $c['type_remise'] === 'pourcentage'
                                    ? ((int)$c['valeur']) . ' %'
                                    : format_prix($c['valeur']) ?>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                <?= h(format_date_courte($c['date_debut'])) ?>
                                → <?= h(format_date_courte($c['date_fin'])) ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?= (int)$c['nb_utilisations'] ?>
                                <?= $c['utilisations_max'] ? ' / ' . (int)$c['utilisations_max'] : '' ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if ((int)$c['actif'] === 1): ?>
                                    <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded font-semibold">Actif</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-700 text-xs rounded font-semibold">Inactif</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form method="post" class="inline">
                                    <?= Csrf::champ() ?>
                                    <input type="hidden" name="action" value="toggle">
                                    <input type="hidden" name="id_code" value="<?= (int)$c['id_code'] ?>">
                                    <button type="submit"
                                            class="text-xs px-2 py-1 bg-primary-50 text-primary-700 rounded hover:bg-primary-100">
                                        <?= (int)$c['actif'] === 1 ? 'Désactiver' : 'Activer' ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
