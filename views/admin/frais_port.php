<?php
/**
 * views/admin/frais_port.php
 * ---------------------------------------------------------------------
 * Vue admin : grille des frais de port avec activation/désactivation.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-5xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — grille de frais de port.
    </div>

    <h1 class="text-3xl font-bold text-gray-900 mb-6">Frais de port</h1>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Pays</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Libellé</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-700">Min. panier</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-700">Max. panier</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-700">Prix</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-700">État</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-700">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($grilles as $g): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium"><?= h($g['pays']) ?></td>
                            <td class="px-4 py-3 text-gray-700"><?= h($g['nom']) ?></td>
                            <td class="px-4 py-3 text-right text-gray-500">
                                <?= $g['montant_min_panier'] !== null ? format_prix($g['montant_min_panier']) : '—' ?>
                            </td>
                            <td class="px-4 py-3 text-right text-gray-500">
                                <?= $g['montant_max_panier'] !== null ? format_prix($g['montant_max_panier']) : '—' ?>
                            </td>
                            <td class="px-4 py-3 text-right font-medium">
                                <?= (float)$g['prix'] > 0 ? format_prix($g['prix']) : 'Gratuit' ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if ((int)$g['actif'] === 1): ?>
                                    <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded font-semibold">Actif</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-700 text-xs rounded font-semibold">Inactif</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form method="post" class="inline">
                                    <?= Csrf::champ() ?>
                                    <input type="hidden" name="id_frais" value="<?= (int)$g['id_frais'] ?>">
                                    <button type="submit"
                                            class="text-xs px-2 py-1 bg-primary-50 text-primary-700 rounded hover:bg-primary-100">
                                        <?= (int)$g['actif'] === 1 ? 'Désactiver' : 'Activer' ?>
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
