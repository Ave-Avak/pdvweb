<?php
/**
 * views/admin/index.php
 * ---------------------------------------------------------------------
 * Vue : tableau de bord admin enrichi en étape 6.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';

// Stats supplémentaires
$nbArticles = Article::compterTous();
$nbCommandes = Facture::compterToutes();
$caTotal = Facture::chiffreAffairesTotal();
?>

<div class="max-w-6xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — bienvenue dans le tableau de bord.
    </div>

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-1">Tableau de bord</h1>
        <p class="text-gray-600">Vue d'ensemble du site PDVWeb</p>
    </div>

    <!-- Statistiques globales -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Membres</p>
            <p class="text-2xl font-bold text-gray-900"><?= (int)$stats['nb_membres'] ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Billets</p>
            <p class="text-2xl font-bold text-gray-900"><?= (int)$stats['nb_billets'] ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Commentaires</p>
            <p class="text-2xl font-bold text-gray-900"><?= (int)$stats['nb_commentaires'] ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Articles</p>
            <p class="text-2xl font-bold text-gray-900"><?= (int)$nbArticles ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Commandes</p>
            <p class="text-2xl font-bold text-gray-900"><?= (int)$nbCommandes ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">CA total</p>
            <p class="text-2xl font-bold text-gray-900"><?= format_prix($caTotal) ?></p>
        </div>
    </div>

    <h2 class="text-xl font-bold text-gray-900 mb-4">Gestion</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

        <a href="<?= url('/admin/billets.php') ?>" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition group">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center group-hover:bg-primary-200 transition">📰</div>
                <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition">Billets de blog</h3>
            </div>
            <p class="text-sm text-gray-600">Créer, modifier, supprimer les billets</p>
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

        <a href="<?= url('/admin/corbeille.php') ?>" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition group">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center group-hover:bg-amber-200 transition">🗑️</div>
                <h3 class="font-semibold text-gray-900 group-hover:text-amber-600 transition">Corbeille</h3>
            </div>
            <p class="text-sm text-gray-600">Restaurer billets et commentaires</p>
        </a>

        <div class="bg-gray-50 rounded-xl border border-dashed border-gray-300 p-5 opacity-60">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">👥</div>
                <h3 class="font-semibold text-gray-600">Membres</h3>
            </div>
            <p class="text-sm text-gray-500">À venir (étape 7) : blocage, stats de connexion</p>
        </div>

        <div class="bg-gray-50 rounded-xl border border-dashed border-gray-300 p-5 opacity-60">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">📊</div>
                <h3 class="font-semibold text-gray-600">Statistiques</h3>
            </div>
            <p class="text-sm text-gray-500">À venir (étape 7) : recherches, vues, audit</p>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
