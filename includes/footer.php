<?php
/**
 * includes/footer.php
 * ---------------------------------------------------------------------
 * Pied de page commun à toutes les vues.
 * Ferme également la balise <main> ouverte par header.php.
 * ---------------------------------------------------------------------
 */
?>

</main>
<!-- Fin du contenu principal ouvert dans header.php -->


<!-- ===================================================================
     PIED DE PAGE
=================================================================== -->
<footer class="bg-white border-t border-gray-200 mt-12">
    <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">

            <!-- Colonne 1 : à propos -->
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-8 h-8 bg-primary-600 rounded-lg flex items-center justify-center text-white font-bold">P</div>
                    <span class="text-lg font-bold text-gray-900"><?= h(parametre('site.nom', SITE_NAME)) ?></span>
                </div>
                <p class="text-sm text-gray-600">
                    <?= h(parametre('site.slogan', 'Votre boutique en ligne multi-rayons.')) ?>
                </p>
            </div>

            <!-- Colonne 2 : navigation -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Navigation</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="<?= url('/index.php') ?>" class="text-gray-600 hover:text-primary-600">Accueil</a></li>
                    <li><a href="<?= url('/catalogue.php') ?>" class="text-gray-600 hover:text-primary-600">Catalogue</a></li>
                    <li><a href="<?= url('/blog.php') ?>" class="text-gray-600 hover:text-primary-600">Blog</a></li>
                </ul>
            </div>

            <!-- Colonne 3 : compte -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Mon compte</h3>
                <ul class="space-y-2 text-sm">
                    <?php if (Auth::estConnecte()): ?>
                        <li><a href="<?= url('/profil.php') ?>" class="text-gray-600 hover:text-primary-600">Mon profil</a></li>
                        <li><a href="<?= url('/historique.php') ?>" class="text-gray-600 hover:text-primary-600">Mes achats</a></li>
                        <li><a href="<?= url('/logout.php') ?>" class="text-gray-600 hover:text-primary-600">Déconnexion</a></li>
                    <?php else: ?>
                        <li><a href="<?= url('/login.php') ?>" class="text-gray-600 hover:text-primary-600">Connexion</a></li>
                        <li><a href="<?= url('/inscription.php') ?>" class="text-gray-600 hover:text-primary-600">Créer un compte</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- Colonne 4 : contact + légal -->
            <div>
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Informations</h3>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li>
                        <a href="<?= url('/mentions_legales.php') ?>" class="hover:text-primary-600">
                            Mentions légales & RGPD
                        </a>
                    </li>
                    <li>
                        <a href="mailto:<?= h(parametre('site.email_contact', 'contact@pdvweb.local')) ?>"
                           class="hover:text-primary-600">
                            <?= h(parametre('site.email_contact', 'contact@pdvweb.local')) ?>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Ligne du bas -->
        <div class="border-t border-gray-200 mt-8 pt-6 flex flex-col md:flex-row justify-between items-center gap-3">
            <p class="text-xs text-gray-500">
                &copy; <?= date('Y') ?> <?= h(parametre('site.nom', SITE_NAME)) ?>.
                Travail de Fin de Module — PRDW 2025-2026.
            </p>
            <p class="text-xs text-gray-500">
                Réalisé en PHP <?= PHP_VERSION ?> et MySQL avec
                <a href="https://tailwindcss.com" target="_blank" rel="noopener" class="text-primary-600 hover:underline">Tailwind CSS</a>.
            </p>
        </div>
    </div>
</footer>


<!-- JS perso (chargé en bas pour ne pas bloquer le rendu) -->
<script src="<?= asset('assets/js/main.js') ?>?v=<?= @filemtime(__DIR__ . '/../public/assets/js/main.js') ?: time() ?>"></script>

</body>
</html>
