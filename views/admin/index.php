<?php
/**
 * views/admin/index.php
 * ---------------------------------------------------------------------
 * Vue : tableau de bord admin enrichi en étape 7.
 *
 * Nouveautés :
 *   - KPI : ventes & commandes 30j, connexions 7j, audit total
 *   - Mini-graphique des ventes 30 derniers jours
 *   - 9 cartes actives (toutes les fonctionnalités admin)
 *   - Alerte stock bas
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';

// Statistiques rapides (étape 6 + 7)
$nbArticles = Article::compterTous();
$nbCommandes30 = Stats::nbCommandesRecentes(30);
$caTotal = Facture::chiffreAffairesTotal();
$caRecent = Stats::caRecent(30);
$nbCnxJ7 = Stats::nbConnexionsRecentes(7);
$nbActifs30 = Stats::nbMembresActifs(30);
$nbAudit = AuditLog::compterTotal();

// Mini-graphique ventes 30 jours
// On remplit les jours manquants à 0 € pour éviter qu'un seul jour
// avec ventes occupe toute la largeur visuelle.
$ventesBrut = Stats::ventesParJour(30);
$ventesIndexees = [];
foreach ($ventesBrut as $v) {
    $ventesIndexees[$v['jour']] = $v;
}
$ventesParJour = [];
for ($i = 29; $i >= 0; $i--) {
    $jour = date('Y-m-d', strtotime("-$i days"));
    if (isset($ventesIndexees[$jour])) {
        $ventesParJour[] = $ventesIndexees[$jour];
    } else {
        $ventesParJour[] = ['jour' => $jour, 'nb' => 0, 'ca' => 0];
    }
}
$maxVente = 0;
foreach ($ventesParJour as $v) {
    if ((float)$v['ca'] > $maxVente) $maxVente = (float)$v['ca'];
}

// Alerte stock bas (≤ 5)
$stockBas = Stats::articlesStockBas(5);
?>

<div class="max-w-6xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — bienvenue dans le tableau de bord.
    </div>

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-1">Tableau de bord</h1>
        <p class="text-gray-600">Vue d'ensemble du site PDVWeb</p>
    </div>


    <!-- Alerte stock bas -->
    <?php if (!empty($stockBas)): ?>
        <div class="bg-amber-50 border-l-4 border-amber-400 rounded-r-md p-3 mb-6 text-sm text-amber-800">
            ⚠️ <strong><?= count($stockBas) ?> article<?= count($stockBas) > 1 ? 's ont' : ' a' ?></strong>
            un stock bas (≤ 5).
            <a href="<?= url('/admin/stats_top.php') ?>" class="underline font-semibold hover:text-amber-900">Voir le détail →</a>
        </div>
    <?php endif; ?>


    <!-- KPI -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Membres</p>
            <p class="text-2xl font-bold text-gray-900"><?= (int)$stats['nb_membres'] ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Actifs 30j</p>
            <p class="text-2xl font-bold text-gray-900"><?= (int)$nbActifs30 ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Articles</p>
            <p class="text-2xl font-bold text-gray-900"><?= (int)$nbArticles ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Cmd 30j</p>
            <p class="text-2xl font-bold text-gray-900"><?= (int)$nbCommandes30 ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">CA 30j</p>
            <p class="text-xl font-bold text-gray-900"><?= format_prix($caRecent) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">CA total</p>
            <p class="text-xl font-bold text-gray-900"><?= format_prix($caTotal) ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Audit</p>
            <p class="text-2xl font-bold text-gray-900"><?= (int)$nbAudit ?></p>
        </div>
    </div>


    <!-- Mini-graphique ventes 30 jours -->
    <?php if (!empty($ventesParJour)): ?>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wider">Chiffre d'affaires sur 30 jours</h2>
                <a href="<?= url('/admin/stats_connexion.php') ?>" class="text-xs text-primary-600 hover:underline">Plus de stats →</a>
            </div>
            <div class="flex items-end gap-1 h-32 border-b border-gray-200 pb-1">
                <?php foreach ($ventesParJour as $v):
                    $ca = (float)$v['ca'];
                    // Barre à 0 si pas de ventes, sinon proportionnelle avec un minimum visible
                    if ($ca === 0.0) {
                        $hauteur = 0;
                    } else {
                        $hauteur = $maxVente > 0 ? max(8, round(($ca / $maxVente) * 100)) : 0;
                    }
                ?>
                    <div class="flex-1 h-full flex flex-col items-center justify-end group relative">
                        <span class="text-[9px] font-semibold text-gray-700 mb-0.5"><?= $ca > 0 ? round($ca) . '€' : '' ?></span>
                        <div class="w-full bg-primary-500 rounded-t hover:bg-primary-700 transition cursor-default"
                             style="height: <?= $hauteur ?>%;"
                             title="<?= h(format_date_courte($v['jour'])) ?> : <?= format_prix($ca) ?>"></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="flex justify-between text-xs text-gray-500 mt-2">
                <span><?= h(format_date_courte($ventesParJour[0]['jour'])) ?></span>
                <?php if ($maxVente > 0): ?>
                    <span class="text-gray-400">Pic : <?= format_prix($maxVente) ?></span>
                <?php endif; ?>
                <span><?= h(format_date_courte(end($ventesParJour)['jour'])) ?></span>
            </div>
        </div>
    <?php endif; ?>


    <!-- Cartes de gestion -->
    <h2 class="text-xl font-bold text-gray-900 mb-4">Gestion</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

        <a href="<?= url('/admin/membres.php') ?>" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition group">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center group-hover:bg-primary-200 transition">👥</div>
                <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition">Membres</h3>
            </div>
            <p class="text-sm text-gray-600">Liste, recherche, blocage, promotion admin</p>
        </a>

        <a href="<?= url('/admin/articles.php') ?>" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition group">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center group-hover:bg-primary-200 transition">📦</div>
                <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition">Articles &amp; Stock</h3>
            </div>
            <p class="text-sm text-gray-600">Catalogue, stock, prix, images</p>
        </a>

        <a href="<?= url('/admin/commandes.php') ?>" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition group">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center group-hover:bg-primary-200 transition">🛒</div>
                <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition">Commandes</h3>
            </div>
            <p class="text-sm text-gray-600">Suivi et changement de statut</p>
        </a>

        <a href="<?= url('/admin/billets.php') ?>" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition group">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center group-hover:bg-primary-200 transition">📰</div>
                <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition">Billets de blog</h3>
            </div>
            <p class="text-sm text-gray-600">Créer, modifier, supprimer les billets</p>
        </a>

        <a href="<?= url('/admin/codes_promo.php') ?>" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition group">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center group-hover:bg-primary-200 transition">🎟️</div>
                <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition">Codes promo</h3>
            </div>
            <p class="text-sm text-gray-600">Activer / désactiver les promotions</p>
        </a>

        <a href="<?= url('/admin/frais_port.php') ?>" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition group">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center group-hover:bg-primary-200 transition">🚚</div>
                <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition">Frais de port</h3>
            </div>
            <p class="text-sm text-gray-600">Grille de livraison par pays</p>
        </a>

        <a href="<?= url('/admin/stats_top.php') ?>" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition group">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center group-hover:bg-primary-200 transition">🏆</div>
                <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition">Tops &amp; classements</h3>
            </div>
            <p class="text-sm text-gray-600">Top articles vendus / vus / notés, top membres</p>
        </a>

        <a href="<?= url('/admin/stats_connexion.php') ?>" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition group">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center group-hover:bg-primary-200 transition">📊</div>
                <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition">Connexions</h3>
            </div>
            <p class="text-sm text-gray-600">Stats par jour, top membres actifs</p>
        </a>

        <a href="<?= url('/admin/stats_recherches.php') ?>" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition group">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center group-hover:bg-primary-200 transition">🔍</div>
                <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition">Recherches</h3>
            </div>
            <p class="text-sm text-gray-600">Top termes, recherches sans résultat</p>
        </a>

        <a href="<?= url('/admin/audit.php') ?>" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition group">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center group-hover:bg-primary-200 transition">📝</div>
                <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition">Audit log</h3>
            </div>
            <p class="text-sm text-gray-600">Journal des actions admin et système</p>
        </a>

        <a href="<?= url('/admin/corbeille.php') ?>" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition group">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center group-hover:bg-amber-200 transition">🗑️</div>
                <h3 class="font-semibold text-gray-900 group-hover:text-amber-600 transition">Corbeille</h3>
            </div>
            <p class="text-sm text-gray-600">Restaurer billets et commentaires</p>
        </a>

    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
