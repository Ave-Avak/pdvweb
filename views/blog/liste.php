<?php
/**
 * views/blog/liste.php
 * ---------------------------------------------------------------------
 * Vue : liste des billets de blog avec recherche, tags, tri, pagination.
 *
 * Variables reçues :
 *   - $titre, $recherche, $idTag, $tri, $page
 *   - $resultat (array)  ['billets', 'total', 'totalPages', 'page']
 *   - $tagActif (array|null)
 *   - $tousLesTags (array)
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';

$billets = $resultat['billets'];
$total   = $resultat['total'];

/**
 * Construit une URL en gardant les paramètres GET sauf ceux modifiés.
 */
$construireUrl = function (array $params) {
    return url('/blog.php') . '?' . http_build_query(array_filter($params, fn($v) => $v !== null && $v !== ''));
};
?>

<div class="max-w-5xl mx-auto">

    <!-- En-tête -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Blog &amp; News</h1>
        <p class="text-gray-600">Actualités, conseils et annonces de l'équipe PDVWeb</p>
    </div>

    <!-- ==============================================================
         Barre de recherche + filtres
    =============================================================== -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <form method="get" action="<?= url('/blog.php') ?>" class="space-y-4">
            <div class="flex flex-col md:flex-row gap-3">
                <!-- Recherche par titre -->
                <div class="flex-1 relative">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="q" value="<?= h($recherche) ?>"
                           placeholder="Rechercher dans les titres..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                </div>

                <!-- Tri -->
                <select name="tri"
                        onchange="this.form.submit()"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                    <option value="recents"    <?= $tri === 'recents'    ? 'selected' : '' ?>>Plus récents</option>
                    <option value="populaires" <?= $tri === 'populaires' ? 'selected' : '' ?>>Plus likés</option>
                    <option value="commentes"  <?= $tri === 'commentes'  ? 'selected' : '' ?>>Plus commentés</option>
                </select>

                <!-- Conservation du tag courant -->
                <?php if ($idTag > 0): ?>
                    <input type="hidden" name="tag" value="<?= (int)$idTag ?>">
                <?php endif; ?>

                <button type="submit"
                        class="px-5 py-2 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition">
                    Rechercher
                </button>
            </div>

            <!-- Tags -->
            <?php if (!empty($tousLesTags)): ?>
                <div class="flex flex-wrap gap-2 items-center">
                    <span class="text-sm text-gray-500">Tags :</span>
                    <a href="<?= $construireUrl(['q' => $recherche, 'tri' => $tri, 'tag' => null]) ?>"
                       class="px-3 py-1 text-xs rounded-full border transition <?= $idTag === 0 ? 'bg-primary-600 text-white border-primary-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100' ?>">
                        Tous
                    </a>
                    <?php foreach ($tousLesTags as $t): ?>
                        <a href="<?= $construireUrl(['q' => $recherche, 'tri' => $tri, 'tag' => $t['id_tag']]) ?>"
                           class="px-3 py-1 text-xs rounded-full border transition <?= (int)$t['id_tag'] === $idTag ? 'bg-primary-600 text-white border-primary-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-100' ?>">
                            #<?= h($t['nom']) ?>
                            <?php if (!empty($t['nb_billets'])): ?>
                                <span class="opacity-60">(<?= (int)$t['nb_billets'] ?>)</span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </form>

        <!-- Filtre actif -->
        <?php if ($recherche !== '' || $tagActif): ?>
            <div class="mt-3 pt-3 border-t border-gray-100 text-sm">
                <span class="text-gray-600">
                    <?= (int)$total ?> résultat<?= $total > 1 ? 's' : '' ?>
                    <?php if ($recherche !== ''): ?>
                        pour « <strong><?= h($recherche) ?></strong> »
                    <?php endif; ?>
                    <?php if ($tagActif): ?>
                        dans le tag <strong>#<?= h($tagActif['nom']) ?></strong>
                    <?php endif; ?>
                </span>
                <a href="<?= url('/blog.php') ?>" class="text-primary-600 hover:underline ml-2">Réinitialiser</a>
            </div>
        <?php endif; ?>
    </div>


    <!-- ==============================================================
         Liste des billets
    =============================================================== -->
    <?php if (empty($billets)): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                      d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
            </svg>
            <p>Aucun billet ne correspond à votre recherche.</p>
        </div>
    <?php else: ?>
        <div class="space-y-5">
            <?php foreach ($billets as $b): ?>
                <?php $tagsBillet = Billet::tagsDe((int)$b['id_billet']); ?>
                <article class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
                    <a href="<?= url('/billet.php?id=' . (int)$b['id_billet']) ?>" class="block group">
                        <h2 class="text-xl font-bold text-gray-900 mb-2 group-hover:text-primary-600 transition">
                            <?= h($b['titre']) ?>
                        </h2>
                    </a>

                    <!-- Méta : auteur + date -->
                    <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500 mb-3">
                        <div class="flex items-center gap-2">
                            <img src="<?= h(asset_avatar($b['auteur_avatar'])) ?>"
                                 alt="<?= h($b['auteur_prenom']) ?>"
                                 class="w-6 h-6 rounded-full object-cover border border-gray-200">
                            <span><?= h($b['auteur_prenom']) ?> <?= h($b['auteur_nom']) ?></span>
                        </div>
                        <span>·</span>
                        <time title="<?= h(format_date($b['date_billet'])) ?>">
                            <?= h(format_date_relative($b['date_billet'])) ?>
                        </time>
                        <span>·</span>
                        <span>💬 <?= (int)$b['nb_commentaires'] ?> commentaire<?= $b['nb_commentaires'] > 1 ? 's' : '' ?></span>
                        <span>·</span>
                        <span>👍 <?= (int)$b['nb_likes'] ?> like<?= $b['nb_likes'] > 1 ? 's' : '' ?></span>
                    </div>

                    <!-- Aperçu du corps -->
                    <p class="text-gray-700 mb-4">
                        <?= h(tronquer(strip_tags($b['corps']), 250)) ?>
                    </p>

                    <!-- Tags -->
                    <?php if (!empty($tagsBillet)): ?>
                        <div class="flex flex-wrap gap-2 mb-3">
                            <?php foreach ($tagsBillet as $t): ?>
                                <a href="<?= url('/blog.php?tag=' . (int)$t['id_tag']) ?>"
                                   class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 hover:bg-primary-50 hover:text-primary-700 transition">
                                    #<?= h($t['nom']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <a href="<?= url('/billet.php?id=' . (int)$b['id_billet']) ?>"
                       class="text-primary-600 hover:text-primary-700 text-sm font-semibold inline-flex items-center gap-1">
                        Lire la suite
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </article>
            <?php endforeach; ?>
        </div>


        <!-- ==========================================================
             Pagination
        =========================================================== -->
        <?php if ($resultat['totalPages'] > 1): ?>
            <div class="mt-8 flex justify-center items-center gap-2">
                <?php
                $totalPages = $resultat['totalPages'];
                // Page précédente
                if ($page > 1):
                ?>
                    <a href="<?= $construireUrl(['q' => $recherche, 'tag' => $idTag ?: null, 'tri' => $tri, 'page' => $page - 1]) ?>"
                       class="px-3 py-2 bg-white border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                        ← Précédent
                    </a>
                <?php endif; ?>

                <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                    <?php if ($p === $page): ?>
                        <span class="px-4 py-2 bg-primary-600 text-white rounded-md text-sm font-semibold"><?= $p ?></span>
                    <?php else: ?>
                        <a href="<?= $construireUrl(['q' => $recherche, 'tag' => $idTag ?: null, 'tri' => $tri, 'page' => $p]) ?>"
                           class="px-4 py-2 bg-white border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                            <?= $p ?>
                        </a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="<?= $construireUrl(['q' => $recherche, 'tag' => $idTag ?: null, 'tri' => $tri, 'page' => $page + 1]) ?>"
                       class="px-3 py-2 bg-white border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                        Suivant →
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
