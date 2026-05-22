<?php
/**
 * views/admin/membres.php
 * ---------------------------------------------------------------------
 * Vue admin : liste paginée des membres avec filtres et recherche.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';

$membres = $resultat['membres'];

$construireUrl = function (array $params) use ($recherche, $statut, $filtre) {
    $base = [
        'q'      => $recherche,
        'statut' => $statut,
        'filtre' => $filtre,
    ];
    return url('/admin/membres.php') . '?' . http_build_query(
        array_filter(array_merge($base, $params), fn($v) => $v !== null && $v !== '')
    );
};
?>

<div class="max-w-6xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — gestion des membres.
    </div>

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-1">Membres</h1>
        <p class="text-gray-600"><?= (int)$resultat['total'] ?> membre<?= $resultat['total'] > 1 ? 's' : '' ?></p>
    </div>

    <!-- Filtres -->
    <form method="get" action="<?= url('/admin/membres.php') ?>"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6 space-y-3">

        <div class="flex flex-col md:flex-row gap-3">
            <div class="flex-1 relative">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="q" value="<?= h($recherche) ?>"
                       placeholder="Rechercher login, email, nom, prénom..."
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
            </div>

            <select name="statut" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 transition">
                <option value="">Tous les rôles</option>
                <option value="membre" <?= $statut === 'membre' ? 'selected' : '' ?>>Membres</option>
                <option value="admin"  <?= $statut === 'admin'  ? 'selected' : '' ?>>Administrateurs</option>
            </select>

            <button type="submit"
                    class="px-5 py-2 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition">
                Filtrer
            </button>
        </div>

        <!-- Filtres rapides -->
        <div class="flex flex-wrap gap-2 items-center">
            <span class="text-sm text-gray-500">État :</span>
            <?php foreach (['tous' => 'Tous', 'actifs' => 'Actifs', 'bloques' => 'Bloqués', 'anonymises' => 'Anonymisés'] as $code => $label): ?>
                <a href="<?= $construireUrl(['filtre' => $code]) ?>"
                   class="px-3 py-1 text-xs rounded-full border transition <?= $filtre === $code ? 'bg-primary-600 text-white border-primary-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100' ?>">
                    <?= $label ?>
                </a>
            <?php endforeach; ?>
        </div>
    </form>


    <!-- Tableau -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Membre</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Rôle</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-700">État</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-700">Cnx 30j</th>
                        <th class="text-center px-4 py-3 font-semibold text-gray-700">Commandes</th>
                        <th class="text-left px-4 py-3 font-semibold text-gray-700">Inscription</th>
                        <th class="text-right px-4 py-3 font-semibold text-gray-700">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($membres as $m):
                        $anonymise = !empty($m['date_anonymisation']);
                        $bloque    = !$anonymise && (int)$m['indesirable'] === 1;
                        $estMoi    = (int)$m['id_membre'] === Auth::id();
                    ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="<?= h(asset_avatar($m['avatar'])) ?>"
                                         alt=""
                                         class="w-8 h-8 rounded-full object-cover border border-gray-200">
                                    <div class="min-w-0">
                                        <a href="<?= url('/admin/membre_detail.php?id=' . (int)$m['id_membre']) ?>"
                                           class="font-semibold text-gray-900 hover:text-primary-600">
                                            <?= h($m['login']) ?>
                                        </a>
                                        <p class="text-xs text-gray-500 truncate">
                                            <?php if ($anonymise): ?>
                                                <em>Compte anonymisé</em>
                                            <?php else: ?>
                                                <?= h($m['prenom']) ?> <?= h($m['nom']) ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <?php if ($m['statut'] === 'admin'): ?>
                                    <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs rounded font-semibold uppercase">Admin</span>
                                <?php else: ?>
                                    <span class="text-gray-500">Membre</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if ($anonymise): ?>
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded font-semibold">Anonymisé</span>
                                <?php elseif ($bloque): ?>
                                    <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs rounded font-semibold">Bloqué</span>
                                <?php else: ?>
                                    <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded font-semibold">Actif</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center text-gray-700"><?= (int)$m['nb_connexions_30j'] ?></td>
                            <td class="px-4 py-3 text-center text-gray-700"><?= (int)$m['nb_commandes'] ?></td>
                            <td class="px-4 py-3 text-xs text-gray-500"><?= h(format_date_courte($m['date_inscription'])) ?></td>
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                <a href="<?= url('/admin/membre_detail.php?id=' . (int)$m['id_membre']) ?>"
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
    <?php if ($resultat['totalPages'] > 1): ?>
        <div class="mt-6 flex justify-center gap-2">
            <?php for ($p = 1; $p <= $resultat['totalPages']; $p++): ?>
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
