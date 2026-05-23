<?php
/**
 * views/auth/adresse_form.php
 * ---------------------------------------------------------------------
 * Vue : formulaire de création/édition d'adresse.
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-2xl mx-auto">

    <a href="<?= url('/adresses.php') ?>" class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-primary-600 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Mes adresses
    </a>

    <h1 class="text-3xl font-bold text-gray-900 mb-6">
        <?= $modeEdition ? 'Modifier l\'adresse' : 'Nouvelle adresse' ?>
    </h1>

    <?php if (!empty($erreurs['general'])): ?>
        <div class="bg-red-50 border-l-4 border-red-400 text-red-800 p-4 mb-6 rounded-r-md text-sm"><?= h($erreurs['general']) ?></div>
    <?php endif; ?>

    <?php if ($retour === 'commande'): ?>
        <div class="bg-blue-50 border-l-4 border-blue-400 text-blue-800 p-3 mb-6 rounded-r-md text-sm">
            💡 Ajoutez une adresse de livraison pour pouvoir passer commande.
        </div>
    <?php endif; ?>

    <form method="post" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8 space-y-5">
        <?= Csrf::champ() ?>

        <div>
            <label for="libelle" class="block text-sm font-medium text-gray-700 mb-1">Libellé <span class="text-red-500">*</span></label>
            <input type="text" id="libelle" name="libelle" required maxlength="60"
                   value="<?= h($donnees['libelle']) ?>"
                   placeholder="ex: Domicile, Bureau, Maison parents"
                   class="w-full px-4 py-2 border <?= isset($erreurs['libelle']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
            <?php if (isset($erreurs['libelle'])): ?><p class="text-xs text-red-600 mt-1"><?= h($erreurs['libelle']) ?></p><?php endif; ?>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">Prénom <span class="text-red-500">*</span></label>
                <input type="text" id="prenom" name="prenom" required maxlength="60"
                       value="<?= h($donnees['prenom']) ?>"
                       class="w-full px-4 py-2 border <?= isset($erreurs['prenom']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
            </div>
            <div>
                <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom <span class="text-red-500">*</span></label>
                <input type="text" id="nom" name="nom" required maxlength="60"
                       value="<?= h($donnees['nom']) ?>"
                       class="w-full px-4 py-2 border <?= isset($erreurs['nom']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div class="col-span-2">
                <label for="rue" class="block text-sm font-medium text-gray-700 mb-1">Rue <span class="text-red-500">*</span></label>
                <input type="text" id="rue" name="rue" required maxlength="150"
                       value="<?= h($donnees['rue']) ?>"
                       class="w-full px-4 py-2 border <?= isset($erreurs['rue']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
            </div>
            <div>
                <label for="numero" class="block text-sm font-medium text-gray-700 mb-1">N° <span class="text-red-500">*</span></label>
                <input type="text" id="numero" name="numero" required maxlength="20"
                       value="<?= h($donnees['numero']) ?>"
                       class="w-full px-4 py-2 border <?= isset($erreurs['numero']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
            </div>
        </div>

        <div>
            <label for="complement" class="block text-sm font-medium text-gray-700 mb-1">Complément</label>
            <input type="text" id="complement" name="complement" maxlength="100"
                   value="<?= h($donnees['complement']) ?>"
                   placeholder="Étage, code interphone, boîte aux lettres..."
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 transition">
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div>
                <label for="cp" class="block text-sm font-medium text-gray-700 mb-1">Code postal <span class="text-red-500">*</span></label>
                <input type="text" id="cp" name="cp" required maxlength="10"
                       value="<?= h($donnees['cp']) ?>"
                       class="w-full px-4 py-2 border <?= isset($erreurs['cp']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
            </div>
            <div class="col-span-2">
                <label for="ville" class="block text-sm font-medium text-gray-700 mb-1">Ville <span class="text-red-500">*</span></label>
                <input type="text" id="ville" name="ville" required maxlength="80"
                       value="<?= h($donnees['ville']) ?>"
                       class="w-full px-4 py-2 border <?= isset($erreurs['ville']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 transition">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label for="pays" class="block text-sm font-medium text-gray-700 mb-1">Pays <span class="text-red-500">*</span></label>
                <select id="pays" name="pays" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 transition">
                    <?php foreach (['Belgique', 'France', 'Luxembourg', 'Pays-Bas', 'Allemagne', 'Suisse'] as $p): ?>
                        <option value="<?= h($p) ?>" <?= $donnees['pays'] === $p ? 'selected' : '' ?>><?= h($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                <input type="tel" id="telephone" name="telephone" maxlength="30"
                       value="<?= h($donnees['telephone']) ?>"
                       placeholder="+32 4XX XX XX XX"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 transition">
            </div>
        </div>

        <div>
            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Utilisation</label>
            <select id="type" name="type"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 transition">
                <option value="les_deux"    <?= $donnees['type'] === 'les_deux'    ? 'selected' : '' ?>>Livraison et facturation</option>
                <option value="livraison"   <?= $donnees['type'] === 'livraison'   ? 'selected' : '' ?>>Livraison uniquement</option>
                <option value="facturation" <?= $donnees['type'] === 'facturation' ? 'selected' : '' ?>>Facturation uniquement</option>
            </select>
        </div>

        <div>
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="est_defaut" value="1" <?= (int)$donnees['est_defaut'] === 1 ? 'checked' : '' ?>
                       class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                <span class="text-sm">Définir comme adresse par défaut</span>
            </label>
        </div>

        <div class="pt-4 border-t border-gray-100 flex flex-wrap gap-3">
            <button type="submit"
                    class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                <?= $modeEdition ? 'Enregistrer' : 'Ajouter l\'adresse' ?>
            </button>
            <a href="<?= url('/adresses.php') ?>"
               class="px-5 py-2.5 bg-white text-gray-700 font-semibold rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                Annuler
            </a>
        </div>
    </form>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
