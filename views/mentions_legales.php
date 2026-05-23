<?php
/**
 * views/mentions_legales.php
 * ---------------------------------------------------------------------
 * Vue : mentions légales + RGPD (conforme loi belge).
 * ---------------------------------------------------------------------
 */
require_once INCLUDES_PATH . '/header.php';
?>

<div class="max-w-3xl mx-auto prose-content">

    <div class="mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">
            Mentions légales et confidentialité
        </h1>
        <p class="text-gray-600">
            Dernière mise à jour : <?= h(date('d/m/Y')) ?>
        </p>
    </div>


    <!-- Sommaire -->
    <nav class="bg-primary-50 border border-primary-100 rounded-xl p-5 mb-8" aria-label="Sommaire">
        <h2 class="text-sm font-semibold text-primary-900 uppercase tracking-wider mb-3">Sommaire</h2>
        <ul class="space-y-1 text-sm">
            <li><a href="#editeur" class="text-primary-700 hover:underline">1. Éditeur du site</a></li>
            <li><a href="#hebergement" class="text-primary-700 hover:underline">2. Hébergement</a></li>
            <li><a href="#donnees" class="text-primary-700 hover:underline">3. Données personnelles collectées</a></li>
            <li><a href="#droits" class="text-primary-700 hover:underline">4. Vos droits (RGPD)</a></li>
            <li><a href="#cookies" class="text-primary-700 hover:underline">5. Cookies</a></li>
            <li><a href="#securite" class="text-primary-700 hover:underline">6. Sécurité des données</a></li>
            <li><a href="#propriete" class="text-primary-700 hover:underline">7. Propriété intellectuelle</a></li>
            <li><a href="#contact" class="text-primary-700 hover:underline">8. Contact</a></li>
        </ul>
    </nav>


    <!-- 1. Éditeur -->
    <section id="editeur" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-3">1. Éditeur du site</h2>
        <p class="text-gray-700">
            Ce site est un projet pédagogique réalisé dans le cadre du
            <strong>Travail de Fin de Module (TFM)</strong> du Bachelier en Informatique de Gestion,
            année académique 2025–2026, à l'<strong>Institut des Carrières Commerciales</strong>.
        </p>
        <p class="text-gray-700 mt-2">
            Le site n'a aucune finalité commerciale réelle : les articles, prix et fonctionnalités
            sont fictifs et destinés exclusivement à la démonstration technique.
        </p>
    </section>


    <!-- 2. Hébergement -->
    <section id="hebergement" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-3">2. Hébergement</h2>
        <p class="text-gray-700">
            Ce site est exécuté localement sur un serveur de développement Apache (XAMPP).
            Aucune donnée n'est transmise ou stockée sur un serveur distant.
        </p>
    </section>


    <!-- 3. Données collectées -->
    <section id="donnees" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-3">3. Données personnelles collectées</h2>

        <p class="text-gray-700 mb-3">
            Lors de votre inscription, les données suivantes sont collectées et stockées :
        </p>
        <ul class="space-y-1.5 text-gray-700 list-disc pl-5">
            <li>Nom et prénom</li>
            <li>Date de naissance</li>
            <li>Adresse email</li>
            <li>Adresse postale complète (rue, numéro, code postal, ville, pays)</li>
            <li>Pseudo / login choisi</li>
            <li>Mot de passe (stocké sous forme de hash bcrypt, jamais en clair)</li>
            <li>Photo de profil (optionnelle)</li>
        </ul>

        <h3 class="text-lg font-semibold text-gray-900 mt-5 mb-2">Finalités du traitement</h3>
        <p class="text-gray-700 mb-2">Ces données sont utilisées exclusivement pour :</p>
        <ul class="space-y-1.5 text-gray-700 list-disc pl-5">
            <li>La création et la gestion de votre compte membre</li>
            <li>Le traitement de vos commandes et la livraison</li>
            <li>L'établissement des factures (mentions légales obligatoires)</li>
            <li>La participation au mini-chat et aux commentaires du blog</li>
            <li>La modération des contenus en cas d'abus</li>
        </ul>

        <h3 class="text-lg font-semibold text-gray-900 mt-5 mb-2">Données NON collectées</h3>
        <ul class="space-y-1.5 text-gray-700 list-disc pl-5">
            <li>Aucune donnée bancaire (pas de paiement réel)</li>
            <li>Aucun numéro de téléphone obligatoire</li>
            <li>Aucune donnée biométrique</li>
            <li>Aucun cookie publicitaire ou de tracking tiers</li>
        </ul>

        <h3 class="text-lg font-semibold text-gray-900 mt-5 mb-2">Conservation</h3>
        <p class="text-gray-700">
            Vos données sont conservées tant que votre compte est actif. À la suppression de votre
            compte, elles sont <strong>anonymisées de manière irréversible</strong> : nom, prénom,
            email et avatar remplacés par des valeurs aléatoires, mot de passe écrasé. Les commandes
            sont conservées dans un état anonymisé pour des raisons comptables et légales.
        </p>
    </section>


    <!-- 4. Vos droits RGPD -->
    <section id="droits" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-3">4. Vos droits (RGPD)</h2>
        <p class="text-gray-700 mb-3">
            Conformément au Règlement Général sur la Protection des Données (RGPD - UE 2016/679),
            vous disposez à tout moment des droits suivants :
        </p>

        <div class="grid md:grid-cols-2 gap-3 mt-4">
            <div class="p-3 bg-gray-50 rounded-lg">
                <h4 class="font-semibold text-gray-900 text-sm mb-1">🔎 Droit d'accès</h4>
                <p class="text-sm text-gray-700">Consulter toutes vos données via votre page Profil.</p>
            </div>
            <div class="p-3 bg-gray-50 rounded-lg">
                <h4 class="font-semibold text-gray-900 text-sm mb-1">✏️ Droit de rectification</h4>
                <p class="text-sm text-gray-700">Modifier email, adresse et mot de passe depuis le profil.</p>
            </div>
            <div class="p-3 bg-gray-50 rounded-lg">
                <h4 class="font-semibold text-gray-900 text-sm mb-1">🗑️ Droit à l'effacement</h4>
                <p class="text-sm text-gray-700">Supprimer (anonymiser) votre compte définitivement.</p>
            </div>
            <div class="p-3 bg-gray-50 rounded-lg">
                <h4 class="font-semibold text-gray-900 text-sm mb-1">📤 Droit à la portabilité</h4>
                <p class="text-sm text-gray-700">Exporter vos données sur demande à l'administrateur.</p>
            </div>
        </div>

        <?php if (Auth::estConnecte()): ?>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="<?= url('/profil.php') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg text-sm font-semibold hover:bg-primary-700 transition">
                    Gérer mes données
                </a>
                <a href="<?= url('/compte_supprimer.php') ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-white text-danger-700 border border-danger-200 rounded-lg text-sm font-semibold hover:bg-danger-50 transition">
                    Supprimer mon compte
                </a>
            </div>
        <?php endif; ?>
    </section>


    <!-- 5. Cookies -->
    <section id="cookies" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-3">5. Cookies</h2>
        <p class="text-gray-700 mb-3">
            Le site utilise uniquement des cookies <strong>techniques strictement nécessaires</strong> :
        </p>
        <ul class="space-y-1.5 text-gray-700 list-disc pl-5">
            <li><code class="bg-gray-100 px-1.5 py-0.5 rounded text-sm">PHPSESSID</code> :
                identifiant de session pour vous garder connecté pendant votre visite</li>
        </ul>
        <p class="text-gray-700 mt-3">
            <strong>Aucun cookie de tracking, de publicité, ou de réseaux sociaux n'est utilisé.</strong>
            Le site ne fait appel à aucun service tiers d'analyse (Google Analytics, etc.).
        </p>
    </section>


    <!-- 6. Sécurité -->
    <section id="securite" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-3">6. Sécurité des données</h2>
        <p class="text-gray-700 mb-3">
            Le site implémente les mesures de sécurité suivantes :
        </p>
        <ul class="space-y-1.5 text-gray-700 list-disc pl-5">
            <li><strong>Mots de passe</strong> hashés avec bcrypt (impossible à retrouver en clair)</li>
            <li><strong>Protection CSRF</strong> sur tous les formulaires</li>
            <li><strong>Protection XSS</strong> par échappement systématique des sorties HTML</li>
            <li><strong>Protection contre l'injection SQL</strong> via requêtes préparées PDO</li>
            <li><strong>Headers HTTP de sécurité</strong> (CSP, X-Frame-Options, X-Content-Type-Options)</li>
            <li><strong>Rate limiting</strong> sur les tentatives de connexion</li>
            <li><strong>Audit log</strong> des actions sensibles</li>
        </ul>
    </section>


    <!-- 7. Propriété intellectuelle -->
    <section id="propriete" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-3">7. Propriété intellectuelle</h2>
        <p class="text-gray-700">
            Le code source de l'application est disponible publiquement à des fins pédagogiques.
            Les marques, noms de produits et descriptifs commerciaux mentionnés appartiennent à
            leurs propriétaires respectifs et ne sont utilisés ici qu'à titre illustratif.
        </p>
    </section>


    <!-- 8. Contact -->
    <section id="contact" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-900 mb-3">8. Contact</h2>
        <p class="text-gray-700 mb-2">
            Pour toute question relative à vos données personnelles ou pour exercer vos droits RGPD,
            vous pouvez contacter l'administrateur du site directement via votre compte membre
            (messagerie privée), ou par l'intermédiaire de votre établissement.
        </p>
        <p class="text-gray-700">
            En cas de litige non résolu, vous pouvez introduire une réclamation auprès de l'
            <strong>Autorité de Protection des Données (APD) Belgique</strong> :
            <a href="https://www.autoriteprotectiondonnees.be/" target="_blank" rel="noopener noreferrer"
               class="text-primary-600 hover:underline">
                autoriteprotectiondonnees.be
            </a>
        </p>
    </section>


    <!-- Retour accueil -->
    <div class="mt-8 text-center">
        <a href="<?= url('/index.php') ?>"
           class="inline-flex items-center gap-2 px-4 py-2 text-primary-600 hover:underline">
            <span aria-hidden="true">←</span>
            Retour à l'accueil
        </a>
    </div>
</div>

<?php require_once INCLUDES_PATH . '/footer.php'; ?>
