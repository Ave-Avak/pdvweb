<?php
/**
 * views/accueil.php
 * ---------------------------------------------------------------------
 * Vue de la page d'accueil — refonte UI/UX.
 *
 * Sections :
 *   1. Hero animé (différent selon connecté/non connecté)
 *   2. Chiffres clés (4 KPI animés)
 *   3. Trois rayons (catalogue par catégorie avec icônes)
 *   4. Articles vedettes (3 articles les mieux notés)
 *   5. Derniers articles du blog
 *   6. Services membres vs visiteurs (comparaison)
 *   7. Login rapide (uniquement si non connecté)
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<!-- ============================================================
     1. HERO — gradient + animation
============================================================ -->
<section class="relative bg-gradient-to-br from-primary-600 via-primary-700 to-primary-900 text-white rounded-2xl shadow-soft-lg overflow-hidden mb-12 animate-fade-in">
    <!-- Motif décoratif en arrière-plan -->
    <div class="absolute inset-0 opacity-10" aria-hidden="true">
        <svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="0.5"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grid)"/>
        </svg>
    </div>

    <div class="relative px-6 md:px-12 py-16 md:py-24 max-w-4xl mx-auto text-center">
        <?php if ($membre): ?>
            <!-- Hero pour utilisateur connecté -->
            <div class="inline-block animate-slide-up">
                <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-sm font-medium mb-4">
                    👋 Bon retour !
                </span>
            </div>
            <h1 class="text-4xl md:text-6xl font-bold mb-4 animate-slide-up">
                Bonjour, <span class="text-primary-200"><?= h($membre['prenom']) ?></span>
            </h1>
            <p class="text-lg md:text-xl text-primary-100 mb-8 max-w-2xl mx-auto animate-slide-up">
                Bienvenue sur votre espace personnel.
                Découvrez les nouveautés et profitez de tous les services membres.
            </p>
            <div class="flex flex-wrap gap-3 justify-center animate-slide-up">
                <a href="<?= url('/catalogue.php') ?>"
                   class="px-6 py-3 bg-white text-primary-700 font-semibold rounded-lg hover:bg-gray-100 transition shadow-md inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    Voir le catalogue
                </a>
                <a href="<?= url('/blog.php') ?>"
                   class="px-6 py-3 bg-white/10 backdrop-blur-sm text-white font-semibold rounded-lg hover:bg-white/20 transition border border-white/30 inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                    </svg>
                    Lire le blog
                </a>
            </div>
        <?php else: ?>
            <!-- Hero pour visiteur non connecté -->
            <div class="inline-block animate-slide-up">
                <span class="inline-block px-3 py-1 bg-white/20 backdrop-blur-sm rounded-full text-sm font-medium mb-4">
                    ✨ Bienvenue sur PDVWeb
                </span>
            </div>
            <h1 class="text-4xl md:text-6xl font-bold mb-4 animate-slide-up">
                <?= h(parametre('site.nom', SITE_NAME)) ?>
            </h1>
            <p class="text-lg md:text-xl text-primary-100 mb-8 max-w-2xl mx-auto animate-slide-up">
                <?= h(parametre('site.slogan', 'Votre boutique multi-rayons pour tout l\'informatique, l\'audio et la culture')) ?>
            </p>
            <div class="flex flex-wrap gap-3 justify-center animate-slide-up">
                <a href="<?= url('/inscription.php') ?>"
                   class="px-6 py-3 bg-white text-primary-700 font-semibold rounded-lg hover:bg-gray-100 transition shadow-md inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                    Créer un compte gratuit
                </a>
                <a href="<?= url('/catalogue.php') ?>"
                   class="px-6 py-3 bg-white/10 backdrop-blur-sm text-white font-semibold rounded-lg hover:bg-white/20 transition border border-white/30 inline-flex items-center gap-2">
                    Découvrir le catalogue
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>


<!-- ============================================================
     2. CHIFFRES CLÉS — KPIs visibles
============================================================ -->
<section class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-12">
    <?php
    $kpis = [
        ['valeur' => $statsAccueil['articles'], 'label' => 'Articles', 'icone' => 'box', 'couleur' => 'primary'],
        ['valeur' => $statsAccueil['categories'], 'label' => 'Catégories', 'icone' => 'grid', 'couleur' => 'info'],
        ['valeur' => $statsAccueil['membres'], 'label' => 'Membres', 'icone' => 'users', 'couleur' => 'success'],
        ['valeur' => $statsAccueil['billets'], 'label' => 'Articles de blog', 'icone' => 'book', 'couleur' => 'warning'],
    ];
    $icones = [
        'box' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>',
        'grid' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>',
        'users' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>',
        'book' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>',
    ];
    ?>
    <?php foreach ($kpis as $kpi): ?>
        <div class="bg-white rounded-xl shadow-soft border border-gray-100 p-5 card-hover">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 bg-<?= $kpi['couleur'] ?>-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-<?= $kpi['couleur'] ?>-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <?= $icones[$kpi['icone']] ?>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-gray-900"><?= (int)$kpi['valeur'] ?></p>
            <p class="text-sm text-gray-600 mt-1"><?= h($kpi['label']) ?></p>
        </div>
    <?php endforeach; ?>
</section>


<!-- ============================================================
     3. NOS TROIS RAYONS — accès rapide au catalogue
============================================================ -->
<section class="mb-12">
    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-2">Nos trois rayons</h2>
        <p class="text-gray-600">Explorez le catalogue par catégorie</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php $catInfo = $categoriesParCode['informatique'] ?? null; ?>
        <a href="<?= url('/catalogue.php' . ($catInfo ? '?categorie=' . (int)$catInfo['id_categorie'] : '')) ?>"
           class="block group bg-white rounded-xl shadow-soft border border-gray-100 p-6 card-hover">
            <div class="w-14 h-14 bg-gradient-to-br from-primary-500 to-primary-700 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Informatique</h3>
            <p class="text-gray-600 text-sm">Ordinateurs, périphériques, accessoires high-tech.</p>
            <span class="inline-flex items-center gap-1 text-primary-600 font-medium text-sm mt-3 group-hover:gap-2 transition-all">
                Explorer <span aria-hidden="true">→</span>
            </span>
        </a>

        <?php $catLivre = $categoriesParCode['livre'] ?? null; ?>
        <a href="<?= url('/catalogue.php' . ($catLivre ? '?categorie=' . (int)$catLivre['id_categorie'] : '')) ?>"
           class="block group bg-white rounded-xl shadow-soft border border-gray-100 p-6 card-hover">
            <div class="w-14 h-14 bg-gradient-to-br from-warning-500 to-warning-700 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Livres</h3>
            <p class="text-gray-600 text-sm">Romans, BD, essais, ouvrages techniques.</p>
            <span class="inline-flex items-center gap-1 text-warning-600 font-medium text-sm mt-3 group-hover:gap-2 transition-all">
                Explorer <span aria-hidden="true">→</span>
            </span>
        </a>

        <?php $catHifi = $categoriesParCode['hifi'] ?? null; ?>
        <a href="<?= url('/catalogue.php' . ($catHifi ? '?categorie=' . (int)$catHifi['id_categorie'] : '')) ?>"
           class="block group bg-white rounded-xl shadow-soft border border-gray-100 p-6 card-hover">
            <div class="w-14 h-14 bg-gradient-to-br from-success-500 to-success-700 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Hi-Fi & Audio</h3>
            <p class="text-gray-600 text-sm">Casques, enceintes, platines, équipements audio.</p>
            <span class="inline-flex items-center gap-1 text-success-600 font-medium text-sm mt-3 group-hover:gap-2 transition-all">
                Explorer <span aria-hidden="true">→</span>
            </span>
        </a>
    </div>
</section>


<!-- ============================================================
     4. ARTICLES VEDETTES — 3 articles les mieux notés
============================================================ -->
<?php if (!empty($articlesVedettes)): ?>
<section class="mb-12">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Coups de cœur</h2>
            <p class="text-gray-600 text-sm">Les articles préférés de nos membres</p>
        </div>
        <a href="<?= url('/catalogue.php') ?>" class="text-primary-600 hover:text-primary-700 font-medium text-sm">
            Tout voir →
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ($articlesVedettes as $a): ?>
            <a href="<?= url('/article.php?id=' . (int)$a['id_article']) ?>"
               class="block bg-white rounded-xl shadow-soft border border-gray-100 overflow-hidden card-hover group">
                <div class="aspect-video bg-gray-100 overflow-hidden">
                    <img src="<?= h(asset_article($a['image'])) ?>"
                         alt="<?= h($a['nom']) ?>"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                         loading="lazy">
                </div>
                <div class="p-4">
                    <p class="text-xs text-primary-600 font-medium uppercase tracking-wider mb-1">
                        <?= h($a['categorie_nom']) ?>
                    </p>
                    <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2"><?= h($a['nom']) ?></h3>
                    <div class="flex items-center justify-between">
                        <p class="text-xl font-bold text-primary-700"><?= format_prix($a['prix']) ?></p>
                        <?php if ($a['nb_notes'] > 0): ?>
                            <div class="flex items-center gap-1 text-sm text-warning-500">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118L10 13.187l-2.799 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L3.567 7.66c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                <span class="font-medium"><?= number_format($a['note_moyenne'], 1, ',', '') ?></span>
                                <span class="text-gray-500">(<?= (int)$a['nb_notes'] ?>)</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>


<!-- ============================================================
     5. DERNIERS BILLETS DU BLOG
============================================================ -->
<?php if (!empty($derniersBillets)): ?>
<section class="mb-12">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Actualités</h2>
            <p class="text-gray-600 text-sm">Dernières nouvelles du blog</p>
        </div>
        <a href="<?= url('/blog.php') ?>" class="text-primary-600 hover:text-primary-700 font-medium text-sm">
            Tous les articles →
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <?php foreach ($derniersBillets as $b): ?>
            <a href="<?= url('/billet.php?id=' . (int)$b['id_billet']) ?>"
               class="block bg-white rounded-xl shadow-soft border border-gray-100 overflow-hidden card-hover group">
                <?php if (!empty($b['image'])): ?>
                    <!-- Image illustrative du billet -->
                    <div class="aspect-video bg-gray-100 overflow-hidden">
                        <img src="<?= h(asset_article($b['image'])) ?>"
                             alt="<?= h($b['titre']) ?>"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                             loading="lazy">
                    </div>
                <?php else: ?>
                    <!-- Bandeau coloré décoratif (si pas d'image) -->
                    <div class="h-2 bg-gradient-to-r from-primary-500 to-primary-700"></div>
                <?php endif; ?>
                <div class="p-5">
                    <div class="flex items-center gap-2 mb-2">
                        <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <time class="text-xs text-gray-500"><?= h(format_date_courte($b['date_billet'])) ?></time>
                        <?php if (!empty($b['nb_commentaires'])): ?>
                            <span class="text-xs text-gray-500">
                                · <?= (int)$b['nb_commentaires'] ?> 💬
                            </span>
                        <?php endif; ?>
                    </div>
                    <h3 class="font-bold text-gray-900 mb-2 line-clamp-2 group-hover:text-primary-600 transition">
                        <?= h($b['titre']) ?>
                    </h3>
                    <?php
                    // Affiche le résumé si défini, sinon un extrait du corps
                    $apercu = !empty($b['resume'])
                        ? $b['resume']
                        : tronquer(strip_tags($b['corps']), 120);
                    ?>
                    <?php if ($apercu): ?>
                        <p class="text-sm text-gray-600 line-clamp-3"><?= h($apercu) ?></p>
                    <?php endif; ?>
                    <p class="text-xs text-primary-600 font-medium mt-3 inline-flex items-center gap-1 group-hover:gap-2 transition-all">
                        Lire la suite <span aria-hidden="true">→</span>
                    </p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>


<!-- ============================================================
     6. SERVICES — COMPARAISON VISITEUR vs MEMBRE
============================================================ -->
<?php if (!$membre): ?>
<section class="mb-12">
    <div class="text-center mb-8">
        <h2 class="text-3xl font-bold text-gray-900 mb-2">Pourquoi devenir membre ?</h2>
        <p class="text-gray-600">Comparez ce que vous pouvez faire</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
        <!-- Visiteur -->
        <div class="bg-white rounded-xl shadow-soft border border-gray-200 p-6">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900">En tant que visiteur</h3>
                    <p class="text-xs text-gray-500">Sans inscription</p>
                </div>
            </div>
            <ul class="space-y-2.5 text-sm">
                <li class="flex items-start gap-2"><span class="text-success-500">✓</span> Parcourir le catalogue</li>
                <li class="flex items-start gap-2"><span class="text-success-500">✓</span> Lire les billets de blog</li>
                <li class="flex items-start gap-2"><span class="text-success-500">✓</span> Rechercher par titre</li>
                <li class="flex items-start gap-2 text-gray-400"><span>✗</span> Acheter des articles</li>
                <li class="flex items-start gap-2 text-gray-400"><span>✗</span> Participer au mini-chat</li>
                <li class="flex items-start gap-2 text-gray-400"><span>✗</span> Commenter les billets</li>
                <li class="flex items-start gap-2 text-gray-400"><span>✗</span> Mettre en favoris</li>
            </ul>
        </div>

        <!-- Membre -->
        <div class="bg-gradient-to-br from-primary-600 to-primary-800 text-white rounded-xl shadow-soft-lg p-6 relative overflow-hidden">
            <div class="absolute top-3 right-3 bg-warning-400 text-warning-900 px-2 py-0.5 rounded-full text-xs font-bold">
                ⭐ Recommandé
            </div>
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold">En tant que membre</h3>
                    <p class="text-xs text-primary-200">Inscription gratuite</p>
                </div>
            </div>
            <ul class="space-y-2.5 text-sm">
                <li class="flex items-start gap-2"><span class="text-success-300">✓</span> Tout ce que fait un visiteur</li>
                <li class="flex items-start gap-2"><span class="text-success-300">✓</span> <strong>Acheter</strong> en quelques clics</li>
                <li class="flex items-start gap-2"><span class="text-success-300">✓</span> <strong>Mini-chat</strong> avec la communauté</li>
                <li class="flex items-start gap-2"><span class="text-success-300">✓</span> <strong>Commenter</strong> les billets</li>
                <li class="flex items-start gap-2"><span class="text-success-300">✓</span> <strong>Favoris</strong> et notes</li>
                <li class="flex items-start gap-2"><span class="text-success-300">✓</span> <strong>Historique</strong> d'achats</li>
                <li class="flex items-start gap-2"><span class="text-success-300">✓</span> <strong>Messages privés</strong> entre membres</li>
            </ul>
            <a href="<?= url('/inscription.php') ?>"
               class="mt-5 block w-full text-center px-4 py-2.5 bg-white text-primary-700 font-semibold rounded-lg hover:bg-gray-100 transition">
                Créer mon compte gratuit
            </a>
        </div>
    </div>
</section>
<?php endif; ?>


<!-- ============================================================
     7. LOGIN RAPIDE (uniquement si non connecté)
============================================================ -->
<?php if ($afficherLogin): ?>
<section class="bg-white rounded-2xl shadow-soft border border-gray-100 p-6 md:p-10 max-w-md mx-auto" aria-labelledby="login-title">
    <div class="text-center mb-6">
        <div class="inline-flex w-14 h-14 bg-primary-100 rounded-full items-center justify-center mb-3">
            <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
        </div>
        <h2 id="login-title" class="text-2xl font-bold text-gray-900 mb-1">Connexion</h2>
        <p class="text-sm text-gray-600">Accédez à votre espace membre</p>
    </div>

    <form method="post" action="<?= url('/login.php') ?>" class="space-y-4">
        <?= Csrf::champ() ?>

        <div>
            <label for="login" class="block text-sm font-medium text-gray-700 mb-1.5">Login</label>
            <input type="text" id="login" name="login" required autocomplete="username"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
        </div>

        <div>
            <div class="flex justify-between mb-1.5">
                <label for="mot_passe" class="block text-sm font-medium text-gray-700">Mot de passe</label>
                <a href="<?= url('/mdp_oublie.php') ?>" class="text-xs text-primary-600 hover:underline">Oublié ?</a>
            </div>
            <input type="password" id="mot_passe" name="mot_passe" required autocomplete="current-password"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
        </div>

        <button type="submit"
                class="w-full px-4 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm flex items-center justify-center gap-2">
            <span>Se connecter</span>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
        </button>

        <p class="text-center text-sm text-gray-600 pt-2">
            Pas encore membre ?
            <a href="<?= url('/inscription.php') ?>" class="text-primary-600 hover:underline font-semibold">
                Inscrivez-vous gratuitement
            </a>
        </p>
    </form>
</section>
<?php endif; ?>


<?php require_once INCLUDES_PATH . '/footer.php';
