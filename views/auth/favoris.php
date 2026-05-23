<?php
/**
 * views/auth/favoris.php
 * ---------------------------------------------------------------------
 * Vue : liste des articles favoris du membre.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-6xl mx-auto">

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Mes favoris</h1>
        <p class="text-gray-600">
            <?= count($favoris) ?> article<?= count($favoris) > 1 ? 's' : '' ?> dans vos favoris
        </p>
    </div>

    <?php if (empty($favoris)): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="inline-flex w-20 h-20 bg-danger-50 rounded-full items-center justify-center mb-4">
                <svg class="w-10 h-10 text-danger-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
            <h2 class="text-xl font-semibold text-gray-900 mb-2">Aucun favori pour le moment</h2>
            <p class="text-gray-600 mb-2">Cliquez sur le cœur 🤍 sur n'importe quel article pour l'ajouter ici.</p>
            <p class="text-sm text-gray-500 mb-6">Vous pourrez ainsi retrouver facilement vos produits préférés.</p>
            <a href="<?= url('/catalogue.php') ?>"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
                Découvrir le catalogue
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            <?php foreach ($favoris as $a): ?>
                <article class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition group">
                    <a href="<?= url('/article.php?id=' . (int)$a['id_article']) ?>" class="block">
                        <div class="aspect-square bg-gray-100 overflow-hidden">
                            <img src="<?= h(asset_article($a['image'])) ?>"
                                 alt="<?= h($a['nom']) ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition">
                        </div>
                    </a>
                    <div class="p-3">
                        <p class="text-xs text-gray-500"><?= h($a['categorie_nom']) ?></p>
                        <a href="<?= url('/article.php?id=' . (int)$a['id_article']) ?>"
                           class="font-semibold text-gray-900 text-sm group-hover:text-primary-600 line-clamp-2"><?= h($a['nom']) ?></a>
                        <div class="flex items-center justify-between mt-2">
                            <span class="font-bold text-gray-900"><?= format_prix($a['prix']) ?></span>
                            <form method="post" action="<?= url('/favori_toggle.php') ?>" class="inline">
                                <?= Csrf::champ() ?>
                                <input type="hidden" name="id_article" value="<?= (int)$a['id_article'] ?>">
                                <input type="hidden" name="retour" value="<?= h(url('/favoris.php')) ?>">
                                <button type="submit" class="text-red-500 hover:text-red-700 transition" title="Retirer des favoris">❤️</button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
