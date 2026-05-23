<?php
/**
 * views/admin/commandes.php
 * ---------------------------------------------------------------------
 * Vue admin : tableau des commandes avec filtres + pagination + export.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';

// Construit l'URL avec les filtres en cours (pour pagination)
$construireUrl = function (array $remplacements = []) use ($filtres) {
    $params = array_merge($filtres, $remplacements);
    // On enlève les valeurs vides ou défaut
    foreach (['statut', 'page'] as $k) {
        if (empty($params[$k])) unset($params[$k]);
    }
    foreach (['recherche', 'date_min', 'date_max'] as $k) {
        if (($params[$k] ?? '') === '') unset($params[$k]);
    }
    unset($params['parPage']);
    // 'recherche' devient 'q' dans l'URL
    if (isset($params['recherche'])) {
        $params['q'] = $params['recherche'];
        unset($params['recherche']);
    }
    return url('/admin/commandes.php') . ($params ? '?' . http_build_query($params) : '');
};

// URL pour l'export CSV (mêmes filtres mais sans pagination)
$urlExport = (function () use ($filtres) {
    $p = $filtres;
    unset($p['page'], $p['parPage']);
    if (($p['recherche'] ?? '') === '') unset($p['recherche']);
    else { $p['q'] = $p['recherche']; unset($p['recherche']); }
    foreach (['statut', 'date_min', 'date_max'] as $k) {
        if (empty($p[$k])) unset($p[$k]);
    }
    return url('/admin/commandes_export.php') . ($p ? '?' . http_build_query($p) : '');
})();
?>

<div class="max-w-7xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — gestion des commandes.
    </div>

    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Commandes</h1>
            <p class="text-gray-600 text-sm">
                <?= (int)$total ?> commande<?= $total > 1 ? 's' : '' ?> au total
                <?php if (count($commandes) < $total): ?>
                    (affichage <?= count($commandes) ?>)
                <?php endif; ?>
            </p>
        </div>
        <a href="<?= h($urlExport) ?>"
           class="inline-flex items-center gap-2 px-4 py-2 bg-success-600 text-white font-semibold rounded-lg hover:bg-success-700 transition shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Export CSV
        </a>
    </div>

    <!-- ============================================================
         FILTRES
    ============================================================ -->
    <form method="get" action="<?= url('/admin/commandes.php') ?>"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-3">
            <!-- Recherche -->
            <div class="lg:col-span-2">
                <label for="q" class="block text-xs font-medium text-gray-600 mb-1">Recherche</label>
                <input type="text" id="q" name="q"
                       value="<?= h($filtres['recherche']) ?>"
                       placeholder="login, nom, prénom, ou ID commande..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 transition">
            </div>

            <!-- Statut -->
            <div>
                <label for="statut" class="block text-xs font-medium text-gray-600 mb-1">Statut</label>
                <select id="statut" name="statut"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 transition">
                    <option value="0">Tous</option>
                    <?php foreach ($statuts as $s): ?>
                        <option value="<?= (int)$s['id_statut'] ?>"
                                <?= (int)$s['id_statut'] === (int)$filtres['statut'] ? 'selected' : '' ?>>
                            <?= h($s['nom']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date min -->
            <div>
                <label for="date_min" class="block text-xs font-medium text-gray-600 mb-1">Depuis</label>
                <input type="date" id="date_min" name="date_min"
                       value="<?= h($filtres['date_min']) ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 transition">
            </div>

            <!-- Date max -->
            <div>
                <label for="date_max" class="block text-xs font-medium text-gray-600 mb-1">Jusqu'au</label>
                <input type="date" id="date_max" name="date_max"
                       value="<?= h($filtres['date_max']) ?>"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 transition">
            </div>
        </div>

        <div class="flex items-center gap-2 mt-3">
            <button type="submit"
                    class="px-4 py-2 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition text-sm">
                Filtrer
            </button>
            <?php if ($filtres['statut'] || $filtres['recherche'] !== '' || $filtres['date_min'] !== '' || $filtres['date_max'] !== ''): ?>
                <a href="<?= url('/admin/commandes.php') ?>"
                   class="px-4 py-2 text-gray-600 hover:text-gray-900 text-sm">
                    Réinitialiser
                </a>
            <?php endif; ?>
        </div>
    </form>

    <!-- ============================================================
         RÉSULTATS
    ============================================================ -->
    <?php if (empty($commandes)): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="inline-flex w-20 h-20 bg-gray-50 rounded-full items-center justify-center mb-4">
                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Aucune commande</h2>
            <p class="text-gray-600">
                <?php if ($filtres['statut'] || $filtres['recherche'] !== ''): ?>
                    Aucun résultat ne correspond à vos filtres.
                <?php else: ?>
                    Aucune commande n'a encore été passée.
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>

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
                                <td class="px-4 py-3 text-gray-700"><?= h(nom_membre($c)) ?></td>
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

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav class="flex items-center justify-between mt-4" aria-label="Pagination">
                <div class="text-sm text-gray-600">
                    Page <?= $page ?> sur <?= $totalPages ?>
                    (<?= $total ?> commande<?= $total > 1 ? 's' : '' ?>)
                </div>
                <div class="flex items-center gap-1">
                    <?php if ($page > 1): ?>
                        <a href="<?= h($construireUrl(['page' => $page - 1])) ?>"
                           class="px-3 py-1.5 bg-white border border-gray-300 rounded text-sm hover:bg-gray-50">
                            ← Précédent
                        </a>
                    <?php endif; ?>

                    <?php
                    // Affiche jusqu'à 5 numéros centrés autour de la page courante
                    $debut = max(1, $page - 2);
                    $fin = min($totalPages, $debut + 4);
                    $debut = max(1, $fin - 4);
                    for ($i = $debut; $i <= $fin; $i++):
                    ?>
                        <a href="<?= h($construireUrl(['page' => $i])) ?>"
                           class="px-3 py-1.5 border rounded text-sm <?= $i === $page ? 'bg-primary-600 text-white border-primary-600 font-semibold' : 'bg-white border-gray-300 hover:bg-gray-50' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?= h($construireUrl(['page' => $page + 1])) ?>"
                           class="px-3 py-1.5 bg-white border border-gray-300 rounded text-sm hover:bg-gray-50">
                            Suivant →
                        </a>
                    <?php endif; ?>
                </div>
            </nav>
        <?php endif; ?>

    <?php endif; ?>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
