<?php
/**
 * views/admin/commandes.php
 * ---------------------------------------------------------------------
 * Vue admin : tableau de toutes les commandes avec changement de statut.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-6xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — gestion des commandes.
    </div>

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-1">Commandes</h1>
        <p class="text-gray-600"><?= count($commandes) ?> commande<?= count($commandes) > 1 ? 's' : '' ?></p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Référence</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Date</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Client</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-700">Lignes</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-700">Total</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Statut</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-700">Détail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($commandes as $c): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900"><?= h($c['reference']) ?></td>
                            <td class="px-4 py-3 text-gray-500 text-xs"><?= h(format_date_courte($c['date_achat'])) ?></td>
                            <td class="px-4 py-3 text-gray-700">
                                <?= h(nom_membre($c)) ?>
                            </td>
                            <td class="px-4 py-3 text-center"><?= (int)$c['nb_articles'] ?></td>
                            <td class="px-4 py-3 text-right font-medium"><?= format_prix($c['prix_total']) ?></td>
                            <td class="px-4 py-3">
                                <form method="post" action="<?= url('/admin/commande_statut.php') ?>" class="inline">
                                    <?= Csrf::champ() ?>
                                    <input type="hidden" name="id_facture" value="<?= (int)$c['id_facture'] ?>">
                                    <select name="id_statut" onchange="this.form.submit()"
                                            class="px-2 py-1 text-xs border border-gray-300 rounded">
                                        <?php foreach ($statuts as $s): ?>
                                            <option value="<?= (int)$s['id_statut'] ?>"
                                                    <?= (int)$s['id_statut'] === (int)$c['id_statut'] ? 'selected' : '' ?>>
                                                <?= h($s['nom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="<?= url('/facture.php?id=' . (int)$c['id_facture']) ?>"
                                   class="text-xs px-2 py-1 bg-primary-50 text-primary-700 rounded hover:bg-primary-100">
                                    Voir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
