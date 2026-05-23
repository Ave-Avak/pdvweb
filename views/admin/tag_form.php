<?php
/**
 * views/admin/tag_form.php
 * ---------------------------------------------------------------------
 * Vue admin : formulaire création/édition d'un tag.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-xl mx-auto">

    <div class="bg-purple-50 border-l-4 border-purple-400 p-3 rounded mb-6 text-sm text-purple-800">
        <strong>Espace administrateur</strong> — <?= $modeEdition ? 'modification' : 'création' ?> d'un tag.
    </div>

    <div class="flex items-center gap-3 mb-6">
        <a href="<?= url('/admin/tags.php') ?>" class="text-gray-500 hover:text-gray-700">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-bold text-gray-900">
                <?= $modeEdition ? 'Modifier le tag' : 'Nouveau tag' ?>
            </h1>
        </div>
    </div>

    <?php if (!empty($erreurs['general'])): ?>
        <div class="bg-red-50 border-l-4 border-red-400 text-red-800 p-4 mb-6 rounded-r-md">
            <?= h($erreurs['general']) ?>
        </div>
    <?php endif; ?>

    <form method="post"
          action="<?= $modeEdition ? url('/admin/tag_form.php?id=' . $idTag) : url('/admin/tag_form.php') ?>"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8 space-y-5">
        <?= Csrf::champ() ?>

        <!-- Nom -->
        <div>
            <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">
                Nom affiché <span class="text-red-500">*</span>
            </label>
            <input type="text" id="nom" name="nom" required maxlength="60"
                   value="<?= h($donnees['nom']) ?>"
                   placeholder="ex: Promotion, Nouveauté, Tutoriel"
                   class="w-full px-4 py-2 border <?= isset($erreurs['nom']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
            <p class="text-xs text-gray-500 mt-1">
                Affiché aux lecteurs avec un # devant (#Promotion).
            </p>
            <?php if (isset($erreurs['nom'])): ?>
                <p class="text-sm text-red-600 mt-1"><?= h($erreurs['nom']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Code -->
        <div>
            <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                Code (slug) <span class="text-red-500">*</span>
            </label>
            <input type="text" id="code" name="code" required maxlength="40"
                   value="<?= h($donnees['code']) ?>"
                   placeholder="ex: promo, nouveaute, tuto"
                   style="text-transform: lowercase"
                   class="w-full px-4 py-2 border <?= isset($erreurs['code']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition font-mono">
            <p class="text-xs text-gray-500 mt-1">
                Identifiant technique : 2 à 40 caractères, minuscules + chiffres + tirets uniquement.
            </p>
            <?php if (isset($erreurs['code'])): ?>
                <p class="text-sm text-red-600 mt-1"><?= h($erreurs['code']) ?></p>
            <?php endif; ?>
        </div>

        <!-- Boutons -->
        <div class="flex flex-wrap items-center justify-between gap-3 pt-4 border-t border-gray-100">
            <a href="<?= url('/admin/tags.php') ?>"
               class="px-4 py-2 text-gray-700 hover:text-gray-900 transition">
                Annuler
            </a>
            <button type="submit"
                    class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                <?= $modeEdition ? 'Enregistrer' : 'Créer le tag' ?>
            </button>
        </div>
    </form>

    <!-- Auto-suggestion du code à partir du nom -->
    <script>
        (function() {
            const inputNom  = document.getElementById('nom');
            const inputCode = document.getElementById('code');
            <?php if (!$modeEdition): ?>
            // En création seulement : pré-remplir le code automatiquement
            let codeModifie = false;
            inputCode.addEventListener('input', () => { codeModifie = true; });
            inputNom.addEventListener('input', () => {
                if (!codeModifie) {
                    inputCode.value = inputNom.value
                        .toLowerCase()
                        .normalize('NFD').replace(/[\u0300-\u036f]/g, '') // retire accents
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/^-+|-+$/g, '')
                        .substring(0, 40);
                }
            });
            <?php endif; ?>
        })();
    </script>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
