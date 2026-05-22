<?php
/**
 * views/catalogue/liste.php
 * ---------------------------------------------------------------------
 * Vue : liste des articles du catalogue avec filtres et pagination.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';

$articles = $resultat['articles'];
$total    = $resultat['total'];

$construireUrl = function (array $params) {
    return url('/catalogue.php') . '?' . http_build_query(
        array_filter($params, fn($v) => $v !== null && $v !== '')
    );
};
?>

<div class="max-w-6xl mx-auto">

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Catalogue</h1>
        <p class="text-gray-600">
            <?= count($toutesCategories) ?> catégories
            ·
            <?= (int)$total ?> article<?= $total > 1 ? 's' : '' ?>
        </p>
    </div>

    <!-- Barre de filtres -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <form method="get" action="<?= url('/catalogue.php') ?>" class="space-y-4">
            <div class="flex flex-col md:flex-row gap-3">
                <div class="flex-1 relative">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="q" value="<?= h($recherche) ?>"
                           placeholder="Rechercher un produit..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                </div>

                <select name="tri" onchange="this.form.submit()"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                    <option value="recents"    <?= $tri === 'recents'    ? 'selected' : '' ?>>Plus récents</option>
                    <option value="prix_asc"   <?= $tri === 'prix_asc'   ? 'selected' : '' ?>>Prix croissant</option>
                    <option value="prix_desc"  <?= $tri === 'prix_desc'  ? 'selected' : '' ?>>Prix décroissant</option>
                    <option value="populaires" <?= $tri === 'populaires' ? 'selected' : '' ?>>Plus vendus</option>
                </select>

                <?php if ($idCategorie > 0): ?>
                    <input type="hidden" name="categorie" value="<?= (int)$idCategorie ?>">
                <?php endif; ?>

                <button type="submit"
                        class="px-5 py-2 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition">
                    Rechercher
                </button>
            </div>

            <!-- Catégories -->
            <div class="flex flex-wrap gap-2 items-center">
                <span class="text-sm text-gray-500">Catégorie :</span>
                <a href="<?= $construireUrl(['q' => $recherche, 'tri' => $tri, 'categorie' => null]) ?>"
                   class="px-3 py-1 text-xs rounded-full border transition <?= $idCategorie === 0 ? 'bg-primary-600 text-white border-primary-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100' ?>">
                    Toutes
                </a>
                <?php foreach ($toutesCategories as $cat): ?>
                    <a href="<?= $construireUrl(['q' => $recherche, 'tri' => $tri, 'categorie' => $cat['id_categorie']]) ?>"
                       class="px-3 py-1 text-xs rounded-full border transition <?= (int)$cat['id_categorie'] === $idCategorie ? 'bg-primary-600 text-white border-primary-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100' ?>">
                        <?= h($cat['nom']) ?>
                        <?php if (!empty($cat['nb_articles'])): ?>
                            <span class="opacity-60">(<?= (int)$cat['nb_articles'] ?>)</span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </form>
    </div>


    <!-- Grille d'articles -->
    <?php if (empty($articles)): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-500">
            <p>Aucun produit trouvé.</p>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($articles as $a): ?>
                <article class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition group flex flex-col">
                    <a href="<?= url('/article.php?id=' . (int)$a['id_article']) ?>" class="block">
                        <!-- Image -->
                        <div class="aspect-square bg-gray-100 overflow-hidden">
                            <img src="<?= h(asset_article($a['image'])) ?>"
                                 alt="<?= h($a['nom']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        </div>
                    </a>

                    <div class="p-3 flex-1 flex flex-col">
                        <!-- Catégorie -->
                        <p class="text-xs text-gray-500 mb-1"><?= h($a['categorie_nom']) ?></p>

                        <!-- Nom -->
                        <a href="<?= url('/article.php?id=' . (int)$a['id_article']) ?>"
                           class="font-semibold text-gray-900 text-sm mb-1 group-hover:text-primary-600 transition line-clamp-2 flex-1">
                            <?= h($a['nom']) ?>
                        </a>

                        <!-- Étoiles -->
                        <?php if (!empty($a['note_moyenne'])): ?>
                            <div class="flex items-center gap-1 text-xs text-amber-500 mb-2">
                                <span><?= str_repeat('★', (int)round($a['note_moyenne'])) ?><?= str_repeat('☆', 5 - (int)round($a['note_moyenne'])) ?></span>
                                <span class="text-gray-500">(<?= (int)$a['nb_notes'] ?>)</span>
                            </div>
                        <?php endif; ?>

                        <!-- Prix + bouton -->
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-lg font-bold text-gray-900"><?= format_prix($a['prix']) ?></span>
                            <?php if ((int)$a['stock'] > 0): ?>
                                <form method="post" action="<?= url('/panier_add.php') ?>" class="inline">
                                    <?= Csrf::champ() ?>
                                    <input type="hidden" name="id_article" value="<?= (int)$a['id_article'] ?>">
                                    <input type="hidden" name="retour" value="<?= h($_SERVER['REQUEST_URI']) ?>">
                                    <button type="submit"
                                            class="px-3 py-1.5 bg-primary-600 text-white text-xs font-semibold rounded-md hover:bg-primary-700 transition"
                                            title="Ajouter au panier">
                                        +
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-xs text-red-600 font-medium">Épuisé</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($resultat['totalPages'] > 1): ?>
            <div class="mt-8 flex justify-center items-center gap-2">
                <?php if ($page > 1): ?>
                    <a href="<?= $construireUrl(['q' => $recherche, 'categorie' => $idCategorie ?: null, 'tri' => $tri, 'page' => $page - 1]) ?>"
                       class="px-3 py-2 bg-white border border-gray-300 rounded-md text-sm hover:bg-gray-50">←</a>
                <?php endif; ?>
                <?php for ($p = 1; $p <= $resultat['totalPages']; $p++): ?>
                    <?php if ($p === $page): ?>
                        <span class="px-4 py-2 bg-primary-600 text-white rounded-md text-sm font-semibold"><?= $p ?></span>
                    <?php else: ?>
                        <a href="<?= $construireUrl(['q' => $recherche, 'categorie' => $idCategorie ?: null, 'tri' => $tri, 'page' => $p]) ?>"
                           class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm hover:bg-gray-50"><?= $p ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($page < $resultat['totalPages']): ?>
                    <a href="<?= $construireUrl(['q' => $recherche, 'categorie' => $idCategorie ?: null, 'tri' => $tri, 'page' => $page + 1]) ?>"
                       class="px-3 py-2 bg-white border border-gray-300 rounded-md text-sm hover:bg-gray-50">→</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
