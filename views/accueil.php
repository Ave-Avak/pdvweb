<?php
/**
 * views/accueil.php
 * ---------------------------------------------------------------------
 * Vue de la page d'accueil.
 * Données reçues du contrôleur (public/index.php) :
 *   - $titre           string
 *   - $afficherLogin   bool   (afficher ou pas le formulaire de login)
 *   - $membre          array|null
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<!-- ===================================================================
     SECTION HERO — Présentation
=================================================================== -->
<section class="bg-gradient-to-br from-primary-600 to-primary-900 text-white rounded-2xl shadow-lg overflow-hidden mb-10">
    <div class="px-6 md:px-12 py-12 md:py-20 max-w-4xl mx-auto text-center">
        <?php if ($membre): ?>
            <h1 class="text-3xl md:text-5xl font-bold mb-4">
                Bienvenue, <?= h($membre['prenom']) ?> !
            </h1>
            <p class="text-lg md:text-xl text-primary-100 mb-8">
                Heureux de vous revoir. Vous avez accès à tous les services membres.
            </p>
            <div class="flex flex-wrap gap-3 justify-center">
                <a href="<?= url('/catalogue.php') ?>"
                   class="px-6 py-3 bg-white text-primary-700 font-semibold rounded-lg hover:bg-gray-100 transition shadow-md">
                    Voir le catalogue
                </a>
                <a href="<?= url('/blog.php') ?>"
                   class="px-6 py-3 bg-primary-700 text-white font-semibold rounded-lg hover:bg-primary-800 transition border-2 border-white/30">
                    Lire le blog
                </a>
            </div>
        <?php else: ?>
            <h1 class="text-3xl md:text-5xl font-bold mb-4">
                <?= h(parametre('site.nom', SITE_NAME)) ?>
            </h1>
            <p class="text-lg md:text-xl text-primary-100 mb-8">
                <?= h(parametre('site.slogan', 'Votre boutique en ligne multi-rayons')) ?>
            </p>
            <div class="flex flex-wrap gap-3 justify-center">
                <a href="<?= url('/inscription.php') ?>"
                   class="px-6 py-3 bg-white text-primary-700 font-semibold rounded-lg hover:bg-gray-100 transition shadow-md">
                    Créer un compte
                </a>
                <a href="<?= url('/catalogue.php') ?>"
                   class="px-6 py-3 bg-primary-700 text-white font-semibold rounded-lg hover:bg-primary-800 transition border-2 border-white/30">
                    Découvrir le catalogue
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>


<!-- ===================================================================
     SECTION FONCTIONNALITÉS
=================================================================== -->
<section class="mb-12">
    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 text-center mb-2">Nos services</h2>
    <p class="text-gray-600 text-center mb-10">Découvrez tout ce que PDVWeb vous propose</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Carte : services accessibles à tous -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Accessible à tous</h3>
            </div>
            <ul class="space-y-3 text-gray-700">
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span><strong>Catalogue</strong> : 18 articles répartis en 3 rayons (informatique, livres, hi-fi)</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span><strong>Blog / News</strong> : actualités et conseils, avec recherche par titre</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-green-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span><strong>Inscription</strong> : créez votre compte gratuitement</span>
                </li>
            </ul>
        </div>

        <!-- Carte : services réservés aux membres -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-primary-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900">Réservés aux membres</h3>
            </div>
            <ul class="space-y-3 text-gray-700">
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-primary-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span><strong>Achats</strong> : panier persistant, codes promo, historique complet</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-primary-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span><strong>Mini-chat</strong> : discutez avec la communauté</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-primary-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span><strong>Commentaires</strong> sur les billets de blog</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-primary-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span><strong>Favoris, notes & avis</strong> sur les articles</span>
                </li>
                <li class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-primary-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span><strong>Profil personnalisé</strong> avec avatar</span>
                </li>
            </ul>
        </div>
    </div>
</section>


<?php if ($afficherLogin): ?>
<!-- ===================================================================
     SECTION LOGIN (uniquement si non connecté)
=================================================================== -->
<section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8 max-w-md mx-auto">
    <h2 class="text-2xl font-bold text-gray-900 mb-2 text-center">Connexion rapide</h2>
    <p class="text-sm text-gray-600 mb-6 text-center">
        Déjà membre ? Connectez-vous pour accéder à tous les services.
    </p>

    <form method="post" action="<?= url('/login.php') ?>" class="space-y-4">
        <?= Csrf::champ() ?>

        <div>
            <label for="login" class="block text-sm font-medium text-gray-700 mb-1">Login</label>
            <input type="text" id="login" name="login" required autocomplete="username"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
        </div>

        <div>
            <label for="mot_passe" class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
            <input type="password" id="mot_passe" name="mot_passe" required autocomplete="current-password"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
        </div>

        <button type="submit"
                class="w-full px-4 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
            Se connecter
        </button>

        <p class="text-center text-sm text-gray-600">
            Pas encore membre ?
            <a href="<?= url('/inscription.php') ?>" class="text-primary-600 hover:underline font-medium">
                Inscrivez-vous
            </a>
        </p>
    </form>
</section>
<?php endif; ?>


<?php
require_once INCLUDES_PATH . '/footer.php';
