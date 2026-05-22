<?php
/**
 * views/auth/compte_supprimer.php
 * ---------------------------------------------------------------------
 * Vue : page de confirmation de suppression du compte (anonymisation RGPD).
 *
 * Variables :
 *   - $titre, $membre, $erreur
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-2xl mx-auto">

    <!-- Lien retour -->
    <a href="<?= url('/profil.php') ?>" class="inline-flex items-center gap-1 text-sm text-gray-600 hover:text-primary-600 mb-4">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Retour au profil
    </a>

    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Supprimer mon compte</h1>
        <p class="text-gray-600">Conformément au droit à l'oubli (RGPD article 17)</p>
    </div>

    <!-- Avertissement -->
    <div class="bg-red-50 border-l-4 border-red-400 text-red-800 p-4 mb-6 rounded-r-md">
        <h2 class="font-bold mb-2 flex items-center gap-2">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
            </svg>
            Action irréversible
        </h2>
        <p class="text-sm">
            Une fois votre compte supprimé, vous ne pourrez plus vous reconnecter avec
            le login <strong><?= h($membre['login']) ?></strong>.
        </p>
    </div>


    <!-- Détails de ce qui va se passer -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-bold text-gray-900 mb-4">Ce qui va se passer</h2>

        <div class="space-y-4 text-sm">
            <!-- Données supprimées -->
            <div>
                <p class="font-semibold text-gray-900 mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    Données qui seront effacées
                </p>
                <ul class="list-disc list-inside text-gray-700 space-y-1 pl-2">
                    <li>Nom, prénom, date de naissance</li>
                    <li>Adresse email et de livraison</li>
                    <li>Login et mot de passe</li>
                    <li>Photo de profil (avatar)</li>
                    <li>Abonnement à la newsletter</li>
                </ul>
            </div>

            <!-- Données conservées -->
            <div>
                <p class="font-semibold text-gray-900 mb-2 flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Données conservées (obligations légales)
                </p>
                <ul class="list-disc list-inside text-gray-700 space-y-1 pl-2">
                    <li>Factures (loi comptable belge : 10 ans)</li>
                    <li>Vos commentaires et messages, mais affichés sous le pseudo <em>"Utilisateur supprimé"</em></li>
                </ul>
            </div>
        </div>
    </div>

    <?php if ($erreur): ?>
        <div class="bg-red-50 border-l-4 border-red-400 text-red-800 p-4 mb-6 rounded-r-md text-sm">
            <?= h($erreur) ?>
        </div>
    <?php endif; ?>


    <!-- Formulaire de confirmation -->
    <form method="post" action="<?= url('/compte_supprimer.php') ?>"
          class="bg-white rounded-xl shadow-sm border-2 border-red-200 p-6 space-y-5">
        <?= Csrf::champ() ?>

        <h2 class="text-lg font-bold text-red-700">Confirmation de suppression</h2>

        <!-- Mot de passe -->
        <div>
            <label for="mot_passe" class="block text-sm font-medium text-gray-700 mb-1">
                Votre mot de passe actuel
            </label>
            <input type="password" id="mot_passe" name="mot_passe" required
                   autocomplete="current-password"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition">
        </div>

        <!-- Confirmation textuelle -->
        <div>
            <label for="confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                Tapez exactement le mot <code class="px-2 py-0.5 bg-gray-100 rounded">SUPPRIMER</code> en majuscules
            </label>
            <input type="text" id="confirmation" name="confirmation" required
                   placeholder="SUPPRIMER"
                   autocomplete="off"
                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition font-mono">
        </div>

        <!-- Boutons -->
        <div class="pt-4 border-t border-gray-100 flex gap-3 flex-wrap">
            <button type="submit"
                    data-confirm="Êtes-vous absolument certain de vouloir supprimer votre compte ? Cette action est IRRÉVERSIBLE."
                    class="px-5 py-2.5 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition shadow-sm">
                Supprimer définitivement mon compte
            </button>
            <a href="<?= url('/profil.php') ?>"
               class="px-5 py-2.5 bg-white text-gray-700 font-semibold rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                Annuler
            </a>
        </div>
    </form>

</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
