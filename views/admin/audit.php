<?php
/**
 * views/admin/audit.php
 * ---------------------------------------------------------------------
 * Vue admin : visualiseur d'audit log avec filtres.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';

$logs = $resultat['logs'];

$construireUrl = function (array $params) use ($action, $idMembre, $entite, $jours) {
    $base = [
        'action_filtre' => $action,
        'id_membre'     => $idMembre > 0 ? $idMembre : null,
        'entite'        => $entite,
        'jours'         => $jours,
    ];
    return url('/admin/audit.php') . '?' . http_build_query(
        array_filter(array_merge($base, $params), fn($v) => $v !== null && $v !== '')
    );
};
?>

<div class="max-w-6xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — journal d'audit.
    </div>

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-1">Journal d'audit</h1>
        <p class="text-gray-600"><?= (int)$resultat['total'] ?> événement<?= $resultat['total'] > 1 ? 's' : '' ?></p>
    </div>


    <!-- Filtres -->
    <form method="get" action="<?= url('/admin/audit.php') ?>"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
            <div>
                <label class="block text-xs text-gray-700 mb-1">Action</label>
                <select name="action_filtre" class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm">
                    <option value="">Toutes</option>
                    <?php foreach ($actionsDispo as $a): ?>
                        <option value="<?= h($a) ?>" <?= $action === $a ? 'selected' : '' ?>><?= h($a) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-700 mb-1">Entité</label>
                <input type="text" name="entite" value="<?= h($entite) ?>"
                       placeholder="ex: membre, article..."
                       class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-700 mb-1">ID membre</label>
                <input type="number" name="id_membre" value="<?= $idMembre > 0 ? (int)$idMembre : '' ?>"
                       class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-700 mb-1">Période</label>
                <select name="jours" class="w-full px-3 py-1.5 border border-gray-300 rounded text-sm">
                    <option value="1"   <?= $jours === 1   ? 'selected' : '' ?>>24 heures</option>
                    <option value="7"   <?= $jours === 7   ? 'selected' : '' ?>>7 jours</option>
                    <option value="30"  <?= $jours === 30  ? 'selected' : '' ?>>30 jours</option>
                    <option value="90"  <?= $jours === 90  ? 'selected' : '' ?>>90 jours</option>
                    <option value="365" <?= $jours === 365 ? 'selected' : '' ?>>1 an</option>
                    <option value="0"   <?= $jours === 0   ? 'selected' : '' ?>>Tout</option>
                </select>
            </div>
        </div>
        <div class="mt-3 flex gap-2">
            <button type="submit" class="px-5 py-1.5 bg-primary-600 text-white text-sm font-semibold rounded hover:bg-primary-700 transition">
                Filtrer
            </button>
            <a href="<?= url('/admin/audit.php') ?>" class="px-5 py-1.5 bg-gray-100 text-gray-700 text-sm font-semibold rounded hover:bg-gray-200 transition">
                Réinitialiser
            </a>
        </div>
    </form>


    <!-- Table de logs -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-3 py-2 font-semibold">Date</th>
                        <th class="text-left px-3 py-2 font-semibold">Action</th>
                        <th class="text-left px-3 py-2 font-semibold">Membre</th>
                        <th class="text-left px-3 py-2 font-semibold">Cible</th>
                        <th class="text-left px-3 py-2 font-semibold">IP</th>
                        <th class="text-left px-3 py-2 font-semibold">Détails</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-gray-500 py-8">Aucun événement trouvé.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 text-xs text-gray-500 whitespace-nowrap">
                                    <?= h(format_date($log['date_action'])) ?>
                                </td>
                                <td class="px-3 py-2 font-mono text-xs font-semibold text-primary-700">
                                    <?= h($log['action']) ?>
                                </td>
                                <td class="px-3 py-2">
                                    <?php if ($log['id_membre']): ?>
                                        <a href="<?= url('/admin/membre_detail.php?id=' . (int)$log['id_membre']) ?>"
                                           class="text-primary-600 hover:underline">
                                            <?= h(nom_membre($log) ?: ('#' . (int)$log['id_membre'])) ?>
                                        </a>
                                    <?php else: ?>
                                        <em class="text-gray-400">système</em>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2 text-xs">
                                    <?php if ($log['entite']): ?>
                                        <code class="bg-gray-100 px-1 py-0.5 rounded"><?= h($log['entite']) ?>#<?= (int)$log['id_entite'] ?></code>
                                    <?php else: ?>
                                        <span class="text-gray-400">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-3 py-2 text-xs text-gray-500 font-mono">
                                    <?= h($log['ip'] ?? '-') ?>
                                </td>
                                <td class="px-3 py-2 text-xs">
                                    <?php if (!empty($log['details'])): ?>
                                        <details>
                                            <summary class="cursor-pointer text-primary-600 hover:underline">Voir</summary>
                                            <pre class="mt-1 bg-gray-50 p-2 rounded text-xs overflow-x-auto max-w-md"><?= h(json_encode(json_decode($log['details'], true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
                                        </details>
                                    <?php else: ?>
                                        <span class="text-gray-400">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>


    <!-- Pagination -->
    <?php if ($resultat['totalPages'] > 1): ?>
        <div class="mt-6 flex justify-center gap-2">
            <?php for ($p = 1; $p <= min($resultat['totalPages'], 15); $p++): ?>
                <?php if ($p === $resultat['page']): ?>
                    <span class="px-4 py-2 bg-primary-600 text-white rounded-md text-sm font-semibold"><?= $p ?></span>
                <?php else: ?>
                    <a href="<?= $construireUrl(['page' => $p]) ?>"
                       class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm hover:bg-gray-50"><?= $p ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
    <?php endif; ?>

</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
