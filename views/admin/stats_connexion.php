<?php
/**
 * views/admin/stats_connexion.php
 * ---------------------------------------------------------------------
 * Vue admin : statistiques de connexion + mini-graphique CSS.
 *
 * Le graphique utilise simplement des div en CSS (pas de lib JS).
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';

// Pour le graphique : on calcule l'échelle max
$maxNb = 0;
foreach ($activiteParJour as $j) {
    if ((int)$j['nb'] > $maxNb) $maxNb = (int)$j['nb'];
}
?>

<div class="max-w-6xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — statistiques de connexion.
    </div>

    <h1 class="text-3xl font-bold text-gray-900 mb-6">Statistiques de connexion</h1>


    <!-- KPI -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Connexions 24h</p>
            <p class="text-2xl font-bold text-gray-900"><?= $nbCnxJ1 ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Connexions 7j</p>
            <p class="text-2xl font-bold text-gray-900"><?= $nbCnxJ7 ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Connexions 30j</p>
            <p class="text-2xl font-bold text-gray-900"><?= $nbCnxJ30 ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Actifs 7j</p>
            <p class="text-2xl font-bold text-gray-900"><?= $nbActifs7 ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Actifs 30j</p>
            <p class="text-2xl font-bold text-gray-900"><?= $nbActifs30 ?></p>
        </div>
    </div>


    <!-- Graphique activité -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Activité des 30 derniers jours</h2>

        <?php if (empty($activiteParJour)): ?>
            <p class="text-gray-500 text-sm">Aucune connexion sur cette période.</p>
        <?php else: ?>
            <div class="flex items-end gap-1 h-48 border-b border-gray-200 pb-1">
                <?php foreach ($activiteParJour as $j):
                    $nb = (int)$j['nb'];
                    // Hauteur proportionnelle, mais avec un minimum visuel
                    // pour qu'on voie quand même les jours avec peu d'activité
                    $hauteur = $maxNb > 0 ? max(8, round(($nb / $maxNb) * 100)) : 0;
                    if ($nb === 0) $hauteur = 0;
                ?>
                    <div class="flex-1 h-full flex flex-col items-center justify-end group relative">
                        <span class="text-[10px] font-semibold text-gray-700 mb-1"><?= $nb > 0 ? $nb : '' ?></span>
                        <div class="w-full bg-primary-500 rounded-t hover:bg-primary-700 transition cursor-default"
                             style="height: <?= $hauteur ?>%;"
                             title="<?= h(format_date_courte($j['jour'])) ?> : <?= $nb ?> connexion(s)"></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="flex justify-between text-xs text-gray-500 mt-2">
                <span><?= h(format_date_courte($activiteParJour[0]['jour'])) ?></span>
                <span class="text-gray-400">Pic : <?= $maxNb ?> connexions / jour</span>
                <span><?= h(format_date_courte(end($activiteParJour)['jour'])) ?></span>
            </div>
        <?php endif; ?>
    </div>


    <!-- Top membres -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Top 10 membres connectés (30 derniers jours)</h2>

        <?php if (empty($topMembresCnx)): ?>
            <p class="text-gray-500 text-sm">Aucune connexion sur cette période.</p>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-3 py-2 font-semibold">#</th>
                            <th class="text-left px-3 py-2 font-semibold">Membre</th>
                            <th class="text-center px-3 py-2 font-semibold">Connexions</th>
                            <th class="text-left px-3 py-2 font-semibold">Dernière</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($topMembresCnx as $idx => $m): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-2 font-bold text-gray-500"><?= $idx + 1 ?></td>
                                <td class="px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <img src="<?= h(asset_avatar($m['avatar'])) ?>"
                                             class="w-7 h-7 rounded-full object-cover border border-gray-200">
                                        <a href="<?= url('/admin/membre_detail.php?id=' . (int)$m['id_membre']) ?>"
                                           class="font-medium hover:text-primary-600">
                                            <?= h($m['login']) ?>
                                        </a>
                                        <?php if ($m['statut'] === 'admin'): ?>
                                            <span class="text-[10px] bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded font-bold uppercase">Admin</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-center font-medium"><?= (int)$m['nb_connexions'] ?></td>
                                <td class="px-3 py-2 text-xs text-gray-500"><?= h(format_date_relative($m['derniere_connexion'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
