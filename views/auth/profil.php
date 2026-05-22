<?php
/**
 * views/auth/profil.php
 * ---------------------------------------------------------------------
 * Vue : page de profil avec 3 cartes (infos perso, mot de passe, avatar).
 *
 * Variables :
 *   - $titre   string
 *   - $membre  array  (données BDD du membre connecté)
 *   - $erreurs array
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-3xl mx-auto">

    <!-- En-tête de page -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Mon profil</h1>
        <p class="text-gray-600">Gérez vos informations personnelles et votre sécurité</p>
    </div>

    <!-- ==============================================================
         CARTE 1 — Avatar
    =============================================================== -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Photo de profil</h2>

        <div class="flex items-start gap-6 flex-wrap">

            <img src="<?= h(asset_avatar($membre['avatar'])) ?>"
                 alt="Avatar actuel"
                 class="w-24 h-24 rounded-full object-cover border-2 border-gray-200 flex-shrink-0">

            <div class="flex-1 min-w-[240px]">
                <form method="post" action="<?= url('/profil.php') ?>"
                      enctype="multipart/form-data" class="space-y-3">
                    <?= Csrf::champ() ?>
                    <input type="hidden" name="action" value="avatar">

                    <div>
                        <label for="avatar" class="block text-sm font-medium text-gray-700 mb-1">
                            Nouvelle photo
                        </label>
                        <input type="file" id="avatar" name="avatar" required
                               accept=".gif,.jpg,.jpeg,image/gif,image/jpeg"
                               class="block w-full text-sm text-gray-700
                                      file:mr-4 file:py-2 file:px-4
                                      file:rounded-md file:border-0
                                      file:text-sm file:font-semibold
                                      file:bg-primary-50 file:text-primary-700
                                      hover:file:bg-primary-100 cursor-pointer">
                        <p class="text-xs text-gray-500 mt-1">.gif ou .jpeg, max 2 Mo</p>
                        <?php if (isset($erreurs['avatar'])): ?>
                            <p class="text-sm text-red-600 mt-1"><?= h($erreurs['avatar']) ?></p>
                        <?php endif; ?>
                    </div>

                    <div class="flex gap-2 flex-wrap">
                        <button type="submit"
                                class="px-4 py-2 bg-primary-600 text-white text-sm font-semibold rounded-md hover:bg-primary-700 transition">
                            Téléverser
                        </button>
                    </div>
                </form>

                <?php if (!empty($membre['avatar'])): ?>
                    <!-- Formulaire séparé pour la suppression -->
                    <form method="post" action="<?= url('/profil.php') ?>" class="mt-2">
                        <?= Csrf::champ() ?>
                        <input type="hidden" name="action" value="avatar">
                        <input type="hidden" name="supprimer" value="1">
                        <button type="submit"
                                data-confirm="Supprimer votre avatar actuel ?"
                                class="px-4 py-2 bg-red-50 text-red-700 text-sm font-semibold rounded-md hover:bg-red-100 transition">
                            Supprimer l'avatar actuel
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <!-- ==============================================================
         CARTE 2 — Informations personnelles
    =============================================================== -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Informations personnelles</h2>

        <form method="post" action="<?= url('/profil.php') ?>" class="space-y-5">
            <?= Csrf::champ() ?>
            <input type="hidden" name="action" value="profil">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="prenom" class="block text-sm font-medium text-gray-700 mb-1">Prénom</label>
                    <input type="text" id="prenom" name="prenom" required maxlength="60"
                           value="<?= h($membre['prenom']) ?>"
                           class="w-full px-4 py-2 border <?= isset($erreurs['prenom']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                    <?php if (isset($erreurs['prenom'])): ?>
                        <p class="text-sm text-red-600 mt-1"><?= h($erreurs['prenom']) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="nom" class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                    <input type="text" id="nom" name="nom" required maxlength="60"
                           value="<?= h($membre['nom']) ?>"
                           class="w-full px-4 py-2 border <?= isset($erreurs['nom']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                    <?php if (isset($erreurs['nom'])): ?>
                        <p class="text-sm text-red-600 mt-1"><?= h($erreurs['nom']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <label for="date_naissance" class="block text-sm font-medium text-gray-700 mb-1">Date de naissance</label>
                <input type="date" id="date_naissance" name="date_naissance" required
                       value="<?= h($membre['date_naissance']) ?>"
                       max="<?= date('Y-m-d') ?>"
                       class="w-full px-4 py-2 border <?= isset($erreurs['date_naissance']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                <?php if (isset($erreurs['date_naissance'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?= h($erreurs['date_naissance']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="email" name="email" required maxlength="150"
                       value="<?= h($membre['email']) ?>"
                       class="w-full px-4 py-2 border <?= isset($erreurs['email']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                <?php if (isset($erreurs['email'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?= h($erreurs['email']) ?></p>
                <?php endif; ?>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Login</label>
                <input type="text" disabled value="<?= h($membre['login']) ?>"
                       class="w-full px-4 py-2 border border-gray-200 rounded-lg bg-gray-50 text-gray-500">
                <p class="text-xs text-gray-500 mt-1">Le login ne peut pas être modifié.</p>
            </div>

            <button type="submit"
                    class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                Enregistrer les modifications
            </button>
        </form>
    </div>


    <!-- ==============================================================
         CARTE 3 — Sécurité (changement de mot de passe)
    =============================================================== -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 md:p-8 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Sécurité</h2>

        <form method="post" action="<?= url('/profil.php') ?>" class="space-y-5">
            <?= Csrf::champ() ?>
            <input type="hidden" name="action" value="mot_passe">

            <div>
                <label for="mot_actuel" class="block text-sm font-medium text-gray-700 mb-1">
                    Mot de passe actuel
                </label>
                <input type="password" id="mot_actuel" name="mot_actuel" required
                       autocomplete="current-password"
                       class="w-full px-4 py-2 border <?= isset($erreurs['mot_actuel']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                <?php if (isset($erreurs['mot_actuel'])): ?>
                    <p class="text-sm text-red-600 mt-1"><?= h($erreurs['mot_actuel']) ?></p>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="mot_nouveau" class="block text-sm font-medium text-gray-700 mb-1">
                        Nouveau mot de passe
                    </label>
                    <input type="password" id="mot_nouveau" name="mot_nouveau" required minlength="8"
                           autocomplete="new-password"
                           class="w-full px-4 py-2 border <?= isset($erreurs['mot_nouveau']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                    <p class="text-xs text-gray-500 mt-1">8 caractères minimum</p>
                    <?php if (isset($erreurs['mot_nouveau'])): ?>
                        <p class="text-sm text-red-600 mt-1"><?= h($erreurs['mot_nouveau']) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label for="mot_confirm" class="block text-sm font-medium text-gray-700 mb-1">
                        Confirmer le nouveau
                    </label>
                    <input type="password" id="mot_confirm" name="mot_confirm" required minlength="8"
                           autocomplete="new-password"
                           class="w-full px-4 py-2 border <?= isset($erreurs['mot_confirm']) ? 'border-red-400' : 'border-gray-300' ?> rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition">
                    <?php if (isset($erreurs['mot_confirm'])): ?>
                        <p class="text-sm text-red-600 mt-1"><?= h($erreurs['mot_confirm']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit"
                    class="px-5 py-2.5 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-700 transition shadow-sm">
                Modifier le mot de passe
            </button>
        </form>
    </div>


    <!-- ==============================================================
         CARTE 4 — Infos du compte (lecture seule)
    =============================================================== -->
    <div class="bg-gray-50 rounded-xl border border-gray-200 p-6 md:p-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-3">Détails du compte</h2>
        <dl class="text-sm space-y-2">
            <div class="flex justify-between">
                <dt class="text-gray-600">Inscrit depuis le</dt>
                <dd class="text-gray-900 font-medium"><?= h(format_date_courte($membre['date_inscription'])) ?></dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-600">Dernière connexion</dt>
                <dd class="text-gray-900 font-medium">
                    <?= $membre['derniere_connexion']
                        ? h(format_date($membre['derniere_connexion']))
                        : 'Première visite' ?>
                </dd>
            </div>
            <div class="flex justify-between">
                <dt class="text-gray-600">Statut</dt>
                <dd>
                    <span class="inline-block px-2 py-0.5 rounded text-xs font-semibold <?= $membre['statut'] === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800' ?>">
                        <?= h(ucfirst($membre['statut'])) ?>
                    </span>
                </dd>
            </div>
        </dl>
    </div>


    <!-- ==============================================================
         CARTE 5 — Zone dangereuse (RGPD - droit à l'oubli)
         Affichée uniquement pour les comptes non-admin
    =============================================================== -->
    <?php if ($membre['statut'] !== 'admin'): ?>
        <div class="bg-white rounded-xl border-2 border-red-200 p-6 md:p-8 mt-6">
            <h2 class="text-lg font-semibold text-red-700 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                </svg>
                Zone sensible
            </h2>
            <p class="text-sm text-gray-700 mb-4">
                Si vous souhaitez exercer votre droit à l'oubli (RGPD),
                vous pouvez supprimer votre compte. Vos données personnelles
                seront anonymisées et vous ne pourrez plus vous reconnecter.
            </p>
            <a href="<?= url('/compte_supprimer.php') ?>"
               class="inline-block px-4 py-2 bg-red-50 text-red-700 text-sm font-semibold rounded-md hover:bg-red-100 transition border border-red-200">
                Supprimer mon compte
            </a>
        </div>
    <?php endif; ?>

</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
