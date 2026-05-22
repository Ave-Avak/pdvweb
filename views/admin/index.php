<?php
/**
 * views/admin/index.php
 * ---------------------------------------------------------------------
 * Vue : tableau de bord admin (provisoire étape 5).
 * Sera enrichi à l'étape 7 avec toutes les fonctionnalités admin.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-5xl mx-auto">

    <!-- Bandeau admin -->
    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — bienvenue dans le tableau de bord de gestion.
    </div>

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-1">Tableau de bord</h1>
        <p class="text-gray-600">Vue d'ensemble du site PDVWeb</p>
    </div>

    <!-- Statistiques globales -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-sm text-gray-500 mb-1">Membres inscrits</p>
            <p class="text-3xl font-bold text-gray-900"><?= (int)$stats['nb_membres'] ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-sm text-gray-500 mb-1">Billets publiés</p>
            <p class="text-3xl font-bold text-gray-900"><?= (int)$stats['nb_billets'] ?></p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <p class="text-sm text-gray-500 mb-1">Commentaires</p>
            <p class="text-3xl font-bold text-gray-900"><?= (int)$stats['nb_commentaires'] ?></p>
        </div>
    </div>

    <!-- Accès rapides -->
    <h2 class="text-xl font-bold text-gray-900 mb-4">Gestion du contenu</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

        <a href="<?= url('/admin/billets.php') ?>"
           class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition group">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center group-hover:bg-primary-200 transition">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 group-hover:text-primary-600 transition">Billets de blog</h3>
            </div>
            <p class="text-sm text-gray-600">Créer, modifier ou supprimer les billets</p>
        </a>

        <a href="<?= url('/admin/corbeille.php') ?>"
           class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 hover:shadow-md transition group">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center group-hover:bg-amber-200 transition">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-900 group-hover:text-amber-600 transition">Corbeille</h3>
            </div>
            <p class="text-sm text-gray-600">Restaurer des billets ou commentaires supprimés</p>
        </a>

        <div class="bg-gray-50 rounded-xl border border-dashed border-gray-300 p-5 opacity-60">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-600">Membres</h3>
            </div>
            <p class="text-sm text-gray-500">À venir (étape 7) : gérer les comptes, bloquer/débloquer</p>
        </div>

        <div class="bg-gray-50 rounded-xl border border-dashed border-gray-300 p-5 opacity-60">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-600">Articles &amp; Stock</h3>
            </div>
            <p class="text-sm text-gray-500">À venir (étape 7) : catalogue, stock, prix</p>
        </div>

        <div class="bg-gray-50 rounded-xl border border-dashed border-gray-300 p-5 opacity-60">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-gray-600">Statistiques</h3>
            </div>
            <p class="text-sm text-gray-500">À venir (étape 7) : recherches, vues, audit</p>
        </div>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
