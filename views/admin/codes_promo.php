<?php
/**
 * views/admin/codes_promo.php
 * ---------------------------------------------------------------------
 * Vue admin : liste des codes promo avec CRUD complet.
 *   - Bouton "Nouveau code"
 *   - Toggle actif/inactif
 *   - Lien "Modifier"
 *   - Bouton "Supprimer" (avec confirmation)
 *
 * Variables : $codes (array)
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-6xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — codes promo.
    </div>

    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Codes promo</h1>
            <p class="text-gray-600 text-sm">
                <?= count($codes) ?> code<?= count($codes) > 1 ? 's' : '' ?> au total.
            </p>
        </div>
        <a href="<?= url('/admin/code_promo_form.php') ?>"
           class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouveau code
        </a>
    </div>

    <?php if (empty($codes)): ?>
        <!-- Empty state -->
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="inline-flex w-20 h-20 bg-primary-50 rounded-full items-center justify-center mb-4">
                <svg class="w-10 h-10 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Aucun code promo</h2>
            <p class="text-gray-600 mb-6">Créez votre premier code de réduction.</p>
            <a href="<?= url('/admin/code_promo_form.php') ?>"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                Créer un code promo
            </a>
        </div>
    <?php else: ?>

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
                            <th class="text-right px-4 py-3 font-semibold text-gray-700">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($codes as $c):
                            // Dates en clair
                            $maintenant = time();
                            $debut = strtotime($c['date_debut']);
                            $fin = strtotime($c['date_fin']);
                            $expire = $fin < $maintenant;
                            $pasEncore = $debut > $maintenant;
                            $libelleType = match ($c['type_remise']) {
                                'pourcentage' => 'Pourcentage',
                                'montant_fixe' => 'Montant fixe',
                                'livraison_offerte' => 'Livraison offerte',
                                default => $c['type_remise'],
                            };
                        ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-mono font-semibold text-primary-700">
                                    <?= h($c['code']) ?>
                                    <?php if (!empty($c['description'])): ?>
                                        <div class="text-xs text-gray-500 font-sans font-normal mt-0.5">
                                            <?= h($c['description']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    <span class="text-xs px-2 py-0.5 rounded bg-gray-100">
                                        <?= h($libelleType) ?>
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold">
                                    <?php if ($c['type_remise'] === 'pourcentage'): ?>
                                        <?= number_format((float)$c['valeur'], 0) ?> %
                                    <?php elseif ($c['type_remise'] === 'montant_fixe'): ?>
                                        <?= format_prix($c['valeur']) ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">—</span>
                                    <?php endif; ?>
                                    <?php if ((float)$c['montant_min_panier'] > 0): ?>
                                        <div class="text-xs text-gray-500 font-normal">
                                            dès <?= format_prix($c['montant_min_panier']) ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-gray-600 text-xs">
                                    <?= h(format_date_courte($c['date_debut'])) ?><br>
                                    → <?= h(format_date_courte($c['date_fin'])) ?>
                                    <?php if ($expire): ?>
                                        <div class="text-red-600 font-semibold mt-0.5">Expiré</div>
                                    <?php elseif ($pasEncore): ?>
                                        <div class="text-amber-600 font-semibold mt-0.5">Pas encore actif</div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-semibold"><?= (int)$c['nb_utilisations'] ?></span>
                                    <?php if (!empty($c['utilisations_max'])): ?>
                                        <span class="text-gray-400">/ <?= (int)$c['utilisations_max'] ?></span>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">/ ∞</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php if ((int)$c['actif'] === 1 && !$expire && !$pasEncore): ?>
                                        <span class="px-2 py-0.5 bg-success-100 text-success-700 text-xs rounded font-semibold">Actif</span>
                                    <?php elseif ((int)$c['actif'] === 0): ?>
                                        <span class="px-2 py-0.5 bg-gray-100 text-gray-700 text-xs rounded font-semibold">Désactivé</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-xs rounded font-semibold">Inutilisable</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <!-- Toggle actif/inactif -->
                                        <form method="post" action="<?= url('/admin/codes_promo.php') ?>" class="inline">
                                            <?= Csrf::champ() ?>
                                            <input type="hidden" name="action" value="toggle">
                                            <input type="hidden" name="id_code" value="<?= (int)$c['id_code'] ?>">
                                            <button type="submit"
                                                    title="<?= (int)$c['actif'] === 1 ? 'Désactiver' : 'Activer' ?>"
                                                    class="text-xs px-2 py-1 bg-gray-50 text-gray-700 rounded hover:bg-gray-200 transition">
                                                <?= (int)$c['actif'] === 1 ? '⏸️' : '▶️' ?>
                                            </button>
                                        </form>

                                        <!-- Modifier -->
                                        <a href="<?= url('/admin/code_promo_form.php?id=' . (int)$c['id_code']) ?>"
                                           title="Modifier"
                                           class="text-xs px-2 py-1 bg-primary-50 text-primary-700 rounded hover:bg-primary-100 transition">
                                            ✏️
                                        </a>

                                        <!-- Supprimer (avec confirmation) -->
                                        <form method="post"
                                              action="<?= url('/admin/code_promo_supprimer.php') ?>"
                                              class="inline">
                                            <?= Csrf::champ() ?>
                                            <input type="hidden" name="id_code" value="<?= (int)$c['id_code'] ?>">
                                            <button type="submit"
                                                    title="Supprimer"
                                                    data-confirm="Supprimer définitivement le code « <?= h($c['code']) ?> » ?<?= $c['nb_utilisations'] > 0 ? '\nIl a été utilisé ' . (int)$c['nb_utilisations'] . ' fois.' : '' ?>"
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
