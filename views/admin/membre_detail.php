<?php
/**
 * views/admin/membre_detail.php
 * ---------------------------------------------------------------------
 * Vue admin : fiche complète d'un membre.
 *
 * Sections : profil, stats, actions, commandes, commentaires, adresses, connexions.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';

$anonymise = !empty($membre['date_anonymisation']);
$bloque    = !$anonymise && (int)$membre['indesirable'] === 1;
$estMoi    = (int)$membre['id_membre'] === Auth::id();
?>

<div class="max-w-5xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — fiche membre.
    </div>

    <a href="<?= url('/admin/membres.php') ?>" class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-primary-600 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Retour à la liste
    </a>


    <!-- En-tête : profil + actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-start gap-5 flex-wrap">
            <img src="<?= h(asset_avatar($membre['avatar'])) ?>"
                 alt=""
                 class="w-20 h-20 rounded-full object-cover border-2 border-gray-200">

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-3 flex-wrap mb-1">
                    <h1 class="text-2xl font-bold text-gray-900"><?= h($membre['login']) ?></h1>
                    <?php if ($membre['statut'] === 'admin'): ?>
                        <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs rounded font-semibold uppercase">Admin</span>
                    <?php endif; ?>
                    <?php if ($anonymise): ?>
                        <span class="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded font-semibold uppercase">Anonymisé</span>
                    <?php elseif ($bloque): ?>
                        <span class="px-2 py-0.5 bg-red-100 text-red-700 text-xs rounded font-semibold uppercase">Bloqué</span>
                    <?php else: ?>
                        <span class="px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded font-semibold uppercase">Actif</span>
                    <?php endif; ?>
                </div>
                <p class="text-gray-700">
                    <?php if ($anonymise): ?>
                        <em>Données personnelles supprimées (RGPD)</em>
                    <?php else: ?>
                        <?= h($membre['prenom']) ?> <?= h($membre['nom']) ?>
                        — <a href="mailto:<?= h($membre['email']) ?>" class="text-primary-600 hover:underline"><?= h($membre['email']) ?></a>
                    <?php endif; ?>
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    Inscrit le <?= h(format_date($membre['date_inscription'])) ?>
                    <?php if (!empty($membre['derniere_connexion'])): ?>
                        · Dernière connexion <?= h(format_date_relative($membre['derniere_connexion'])) ?>
                    <?php endif; ?>
                </p>
            </div>
        </div>

        <!-- Actions admin -->
        <?php if (!$estMoi && !$anonymise): ?>
            <div class="mt-5 pt-5 border-t border-gray-100 flex flex-wrap gap-2">
                <?php if ($bloque): ?>
                    <form method="post" action="<?= url('/admin/membre_action.php') ?>" class="inline">
                        <?= Csrf::champ() ?>
                        <input type="hidden" name="id_membre" value="<?= (int)$membre['id_membre'] ?>">
                        <input type="hidden" name="action" value="debloquer">
                        <input type="hidden" name="retour" value="<?= h($_SERVER['REQUEST_URI']) ?>">
                        <button type="submit" class="px-3 py-1.5 bg-green-600 text-white text-sm font-semibold rounded-md hover:bg-green-700 transition">
                            Débloquer
                        </button>
                    </form>
                <?php else: ?>
                    <form method="post" action="<?= url('/admin/membre_action.php') ?>" class="inline">
                        <?= Csrf::champ() ?>
                        <input type="hidden" name="id_membre" value="<?= (int)$membre['id_membre'] ?>">
                        <input type="hidden" name="action" value="bloquer">
                        <input type="hidden" name="retour" value="<?= h($_SERVER['REQUEST_URI']) ?>">
                        <button type="submit"
                                data-confirm="Bloquer ce membre ? Il ne pourra plus se connecter."
                                class="px-3 py-1.5 bg-red-600 text-white text-sm font-semibold rounded-md hover:bg-red-700 transition">
                            Bloquer
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($membre['statut'] === 'admin'): ?>
                    <form method="post" action="<?= url('/admin/membre_action.php') ?>" class="inline">
                        <?= Csrf::champ() ?>
                        <input type="hidden" name="id_membre" value="<?= (int)$membre['id_membre'] ?>">
                        <input type="hidden" name="action" value="degrader">
                        <input type="hidden" name="retour" value="<?= h($_SERVER['REQUEST_URI']) ?>">
                        <button type="submit"
                                data-confirm="Retirer les droits administrateur ?"
                                class="px-3 py-1.5 bg-gray-600 text-white text-sm font-semibold rounded-md hover:bg-gray-700 transition">
                            Dégrader (membre)
                        </button>
                    </form>
                <?php else: ?>
                    <form method="post" action="<?= url('/admin/membre_action.php') ?>" class="inline">
                        <?= Csrf::champ() ?>
                        <input type="hidden" name="id_membre" value="<?= (int)$membre['id_membre'] ?>">
                        <input type="hidden" name="action" value="promouvoir">
                        <input type="hidden" name="retour" value="<?= h($_SERVER['REQUEST_URI']) ?>">
                        <button type="submit"
                                data-confirm="Promouvoir ce membre au rang d'administrateur ?"
                                class="px-3 py-1.5 bg-purple-600 text-white text-sm font-semibold rounded-md hover:bg-purple-700 transition">
                            Promouvoir admin
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        <?php elseif ($estMoi): ?>
            <p class="mt-4 pt-4 border-t border-gray-100 text-xs text-amber-700 bg-amber-50 p-2 rounded">
                💡 Vous ne pouvez pas effectuer d'actions sur votre propre compte.
            </p>
        <?php endif; ?>
    </div>


    <!-- Statistiques -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
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
            <p class="text-xs text-gray-500 mb-1">Commandes</p>
            <p class="text-2xl font-bold text-gray-900"><?= count($commandes) ?></p>
        </div>
    </div>


    <!-- Commandes -->
    <?php if (!empty($commandes)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-3">Commandes (<?= count($commandes) ?>)</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="text-left px-3 py-2 font-semibold">Référence</th>
                            <th class="text-left px-3 py-2 font-semibold">Date</th>
                            <th class="text-right px-3 py-2 font-semibold">Total</th>
                            <th class="text-left px-3 py-2 font-semibold">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($commandes as $c): ?>
                            <tr>
                                <td class="px-3 py-2">
                                    <a href="<?= url('/facture.php?id=' . (int)$c['id_facture']) ?>"
                                       class="text-primary-600 hover:underline"><?= h($c['reference']) ?></a>
                                </td>
                                <td class="px-3 py-2 text-gray-500"><?= h(format_date_courte($c['date_achat'])) ?></td>
                                <td class="px-3 py-2 text-right font-medium"><?= format_prix($c['prix_total']) ?></td>
                                <td class="px-3 py-2">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                          style="background-color: <?= h($c['statut_couleur'] ?? '#9ca3af') ?>20; color: <?= h($c['statut_couleur'] ?? '#374151') ?>;">
                                        <?= h($c['statut_nom']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>


    <!-- Adresses -->
    <?php if (!empty($adresses)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-3">Adresses</h2>
            <div class="grid md:grid-cols-2 gap-3">
                <?php foreach ($adresses as $a): ?>
                    <div class="border border-gray-200 rounded-lg p-3 text-sm">
                        <p class="font-semibold text-gray-900 mb-1">
                            <?= h($a['libelle']) ?>
                            <?php if ((int)$a['est_defaut'] === 1): ?>
                                <span class="text-[10px] bg-primary-100 text-primary-700 px-1.5 py-0.5 rounded uppercase font-bold">Défaut</span>
                            <?php endif; ?>
                        </p>
                        <p class="text-gray-700">
                            <?= h($a['prenom']) ?> <?= h($a['nom']) ?><br>
                            <?= h($a['rue']) ?> <?= h($a['numero']) ?><br>
                            <?= h($a['cp']) ?> <?= h($a['ville']) ?>, <?= h($a['pays']) ?>
                        </p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>


    <!-- Commentaires récents -->
    <?php if (!empty($commentaires)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-3">Derniers commentaires (<?= count($commentaires) ?>)</h2>
            <div class="space-y-2 text-sm">
                <?php foreach ($commentaires as $c): ?>
                    <div class="border-l-2 border-gray-200 pl-3 py-1">
                        <p class="text-xs text-gray-500">
                            Sur <a href="<?= url('/billet.php?id=' . (int)$c['id_billet']) ?>" class="text-primary-600 hover:underline"><?= h($c['billet_titre']) ?></a>
                            · <?= h(format_date_relative($c['date_comm'])) ?>
                        </p>
                        <p class="text-gray-700 mt-1 line-clamp-2"><?= h($c['contenu']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>


    <!-- Dernières connexions -->
    <?php if (!empty($dernieresConnexions)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <h2 class="text-lg font-bold text-gray-900 mb-3">10 dernières connexions</h2>
            <div class="text-sm space-y-1">
                <?php foreach ($dernieresConnexions as $cnx): ?>
                    <div class="flex justify-between border-b border-gray-100 py-1 last:border-0">
                        <span class="text-gray-700"><?= h(format_date($cnx['date_log'])) ?></span>
                        <span class="text-gray-500 text-xs font-mono"><?= h($cnx['ip'] ?? '-') ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>


    <!-- Lien audit -->
    <div class="text-center">
        <a href="<?= url('/admin/audit.php?id_membre=' . (int)$membre['id_membre']) ?>"
           class="text-sm text-primary-600 hover:underline">
            Voir l'historique d'audit pour ce membre →
        </a>
    </div>

</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
