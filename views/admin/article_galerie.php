<?php
/**
 * views/admin/article_galerie.php
 * Variables : $article, $images
 */
require_once INCLUDES_PATH . '/header.php';
require_once INCLUDES_PATH . '/admin_header.php';
?>

<div class="max-w-5xl mx-auto">

    <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Galerie d'images</h1>
            <p class="text-gray-600 text-sm">
                Article :
                <a href="<?= url('/admin/article_form.php?id=' . (int)$article['id_article']) ?>"
                   class="font-semibold text-primary-600 hover:underline">
                    <?= h($article['nom']) ?>
                </a>
            </p>
        </div>
        <a href="<?= url('/admin/articles.php') ?>"
           class="text-sm text-gray-600 hover:text-gray-900">
            ← Retour aux articles
        </a>
    </div>

    <!-- Image principale -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">
            Image principale
        </h2>
        <div class="flex items-center gap-4">
            <div class="w-32 h-32 bg-gray-100 rounded-lg overflow-hidden flex-shrink-0">
                <?php if (!empty($article['image'])): ?>
                    <img src="<?= h(asset_article($article['image'])) ?>"
                         alt="<?= h($article['nom']) ?>" loading="lazy"
                         class="w-full h-full object-cover">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center text-gray-400 text-xs">
                        Aucune image
                    </div>
                <?php endif; ?>
            </div>
            <div class="flex-1 text-sm text-gray-600">
                L'image principale est définie dans le formulaire d'édition de l'article.
                Elle apparaît en premier dans le catalogue et la fiche article.
                <div class="mt-2">
                    <a href="<?= url('/admin/article_form.php?id=' . (int)$article['id_article']) ?>"
                       class="text-primary-600 hover:underline font-semibold">
                        Modifier l'image principale →
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Galerie -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5 mb-6">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">
            Galerie supplémentaire (<?= count($images) ?>)
        </h2>

        <?php if (empty($images)): ?>
            <p class="text-center text-gray-500 py-6">
                Aucune image supplémentaire. Ajoutez-en ci-dessous pour créer un carrousel.
            </p>
        <?php else: ?>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <?php foreach ($images as $img): ?>
                    <div class="relative group bg-gray-50 rounded-lg overflow-hidden border border-gray-200">
                        <img src="<?= h(asset_article($img['fichier'])) ?>"
                             alt="Image #<?= (int)$img['id_image'] ?>" loading="lazy"
                             class="w-full aspect-square object-cover">

                        <!-- Bouton supprimer -->
                        <form method="post" action="" class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition">
                            <?= Csrf::champ() ?>
                            <input type="hidden" name="supprimer_image" value="<?= (int)$img['id_image'] ?>">
                            <button type="submit"
                                    data-confirm="Supprimer cette image ?"
                                    class="w-8 h-8 bg-danger-600 text-white rounded-full flex items-center justify-center hover:bg-danger-700 transition shadow-lg"
                                    title="Supprimer">
                                ×
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Upload multiple -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">
            Ajouter des images
        </h2>

        <form method="post" action="" enctype="multipart/form-data" class="space-y-4">
            <?= Csrf::champ() ?>

            <div>
                <label for="images" class="block text-sm font-medium text-gray-700 mb-2">
                    Sélectionnez une ou plusieurs images
                </label>
                <input type="file" id="images" name="images[]"
                       multiple
                       accept=".jpg,.jpeg,.gif,.png,.webp"
                       class="block w-full text-sm border border-gray-300 rounded-lg p-2">
                <p class="text-xs text-gray-500 mt-1">
                    Formats acceptés : JPG, JPEG, GIF, PNG, WebP. Maximum 2 Mo par image.
                </p>
            </div>

            <button type="submit"
                    class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                Téléverser les images
            </button>
        </form>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
