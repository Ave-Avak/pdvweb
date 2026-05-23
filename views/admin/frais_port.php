<?php
/**
 * views/admin/frais_port.php
 * ---------------------------------------------------------------------
 * Vue admin : grille des frais de port avec CRUD complet.
 *   - Bouton "Nouvelle grille"
 *   - Toggle actif/inactif
 *   - Lien "Modifier"
 *   - Bouton "Supprimer"
 *
 * Variables : $grilles (array)
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-6xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — grille de frais de port.
    </div>

    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Frais de port</h1>
            <p class="text-gray-600 text-sm">
                <?= count($grilles) ?> grille<?= count($grilles) > 1 ? 's' : '' ?> au total.
            </p>
        </div>
        <a href="<?= url('/admin/frais_port_form.php') ?>"
           class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouvelle grille
        </a>
    </div>

    <?php if (empty($grilles)): ?>
        <!-- Empty state -->
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="inline-flex w-20 h-20 bg-primary-50 rounded-full items-center justify-center mb-4">
                <svg class="w-10 h-10 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Aucune grille tarifaire</h2>
            <p class="text-gray-600 mb-6">Créez votre première grille pour permettre les commandes.</p>
            <a href="<?= url('/admin/frais_port_form.php') ?>"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                Créer une grille
            </a>
        </div>
    <?php else: ?>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-semibold text-gray-700">Pays</th>
                            <th class="text-left px-4 py-3 font-semibold text-gray-700">Nom</th>
                            <th class="text-right px-4 py-3 font-semibold text-gray-700">Min. panier</th>
                            <th class="text-right px-4 py-3 font-semibold text-gray-700">Max. panier</th>
                            <th class="text-right px-4 py-3 font-semibold text-gray-700">Prix</th>
                            <th class="text-center px-4 py-3 font-semibold text-gray-700">Délai</th>
                            <th class="text-center px-4 py-3 font-semibold text-gray-700">État</th>
                            <th class="text-right px-4 py-3 font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($grilles as $g): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-900 font-medium">
                                    🌍 <?= h($g['pays']) ?>
                                </td>
                                <td class="px-4 py-3 text-gray-900"><?= h($g['nom']) ?></td>
                                <td class="px-4 py-3 text-right text-gray-700">
                                    <?= $g['montant_min_panier'] !== null ? format_prix($g['montant_min_panier']) : '<span class="text-gray-400">—</span>' ?>
                                </td>
                                <td class="px-4 py-3 text-right text-gray-700">
                                    <?= $g['montant_max_panier'] !== null ? format_prix($g['montant_max_panier']) : '<span class="text-gray-400">—</span>' ?>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold">
                                    <?php if ((float)$g['prix'] === 0.0): ?>
                                        <span class="text-success-700">Gratuit</span>
                                    <?php else: ?>
                                        <?= format_prix($g['prix']) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center text-gray-700">
                                    <?php if ($g['delai_jours'] !== null): ?>
                                        <?= (int)$g['delai_jours'] ?> j
                                    <?php else: ?>
                                        <span class="text-gray-400">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if ((int)$g['actif'] === 1): ?>
                                        <span class="px-2 py-0.5 bg-success-100 text-success-700 text-xs rounded font-semibold">Actif</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-700 text-xs rounded font-semibold">Inactif</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <!-- Toggle -->
                                        <form method="post" action="<?= url('/admin/frais_port.php') ?>" class="inline">
                                            <?= Csrf::champ() ?>
                                            <input type="hidden" name="id_frais" value="<?= (int)$g['id_frais'] ?>">
                                            <button type="submit"
                                                    title="<?= (int)$g['actif'] === 1 ? 'Désactiver' : 'Activer' ?>"
                                                    class="text-xs px-2 py-1 bg-gray-50 text-gray-700 rounded hover:bg-gray-200 transition">
                                                <?= (int)$g['actif'] === 1 ? '⏸️' : '▶️' ?>
                                            </button>
                                        </form>

                                        <!-- Modifier -->
                                        <a href="<?= url('/admin/frais_port_form.php?id=' . (int)$g['id_frais']) ?>"
                                           title="Modifier"
                                           class="text-xs px-2 py-1 bg-primary-50 text-primary-700 rounded hover:bg-primary-100 transition">
                                            ✏️
                                        </a>

                                        <!-- Supprimer -->
                                        <form method="post"
                                              action="<?= url('/admin/frais_port_supprimer.php') ?>"
                                              class="inline">
                                            <?= Csrf::champ() ?>
                                            <input type="hidden" name="id_frais" value="<?= (int)$g['id_frais'] ?>">
                                            <button type="submit"
                                                    title="Supprimer"
                                                    data-confirm="Supprimer définitivement la grille « <?= h($g['nom']) ?> » ?"
                                                    class="text-xs px-2 py-1 bg-danger-50 text-danger-700 rounded hover:bg-danger-100 transition">
                                                🗑️
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
