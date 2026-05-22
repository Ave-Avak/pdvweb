<?php
/**
 * includes/header.php
 * ---------------------------------------------------------------------
 * Haut de page commun à toutes les vues.
 *
 * Variables attendues (à définir dans la page appelante AVANT l'include) :
 *   - $titre  (string)  Titre de la page (affiché dans l'onglet)
 *
 * Utilisation :
 *      $titre = 'Mon Profil';
 *      require_once INCLUDES_PATH . '/header.php';
 * ---------------------------------------------------------------------
 */

// Variable de titre avec fallback sur le nom du site
$titrePage = isset($titre) ? $titre . ' — ' . SITE_NAME : SITE_NAME;

// Récupération de l'utilisateur connecté (si présent)
$membreConnecte = Auth::membre();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= h(parametre('site.slogan', 'Boutique en ligne multi-rayons')) ?>">

    <title><?= h($titrePage) ?></title>

    <!-- Tailwind CSS via CDN (couvre la grande majorité des classes) -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Configuration Tailwind personnalisée -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        // Palette personnalisée du site
                        primary: {
                            50:  '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif']
                    }
                }
            }
        }
    </script>

    <!-- Police Inter (look moderne) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS perso -->
    <link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>">

    <!-- Icône onglet -->
    <link rel="icon" type="image/svg+xml" href="<?= asset('assets/img/favicon.svg') ?>">
</head>

<body class="font-sans bg-gray-50 text-gray-900 min-h-screen flex flex-col">

<!-- ===================================================================
     EN-TÊTE / NAVBAR
=================================================================== -->
<header class="bg-white shadow-sm sticky top-0 z-50">
    <nav class="container mx-auto px-4 py-3">
        <div class="flex items-center justify-between">

            <!-- Logo + nom du site -->
            <a href="<?= url('/index.php') ?>" class="flex items-center gap-2 group">
                <div class="w-9 h-9 bg-primary-600 rounded-lg flex items-center justify-center text-white font-bold group-hover:bg-primary-700 transition">
                    P
                </div>
                <span class="text-xl font-bold text-gray-900"><?= h(parametre('site.nom', SITE_NAME)) ?></span>
            </a>

            <!-- Liens principaux (visibles à partir de md) -->
            <div class="hidden md:flex items-center gap-1">
                <a href="<?= url('/index.php') ?>"
                   class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-100 transition <?= actif('/index.php') ?>">
                    Accueil
                </a>
                <a href="<?= url('/catalogue.php') ?>"
                   class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-100 transition <?= actif('/catalogue.php') ?>">
                    Catalogue
                </a>
                <a href="<?= url('/blog.php') ?>"
                   class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-100 transition <?= actif('/blog.php') ?>">
                    Blog
                </a>
                <?php if ($membreConnecte): ?>
                    <a href="<?= url('/minichat.php') ?>"
                       class="px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:text-primary-600 hover:bg-gray-100 transition <?= actif('/minichat.php') ?>">
                        Mini-chat
                    </a>
                <?php endif; ?>
            </div>

            <!-- Zone droite : connecté ou non -->
            <div class="flex items-center gap-2">
                <?php if ($membreConnecte): ?>
                    <!-- Panier (membres) -->
                    <a href="<?= url('/panier.php') ?>"
                       class="relative p-2 text-gray-700 hover:text-primary-600 transition" title="Mon panier">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <?php
                        // Nombre d'articles dans le panier ($_SESSION['panier'])
                        $nbPanier = 0;
                        if (!empty($_SESSION['panier']) && is_array($_SESSION['panier'])) {
                            $nbPanier = array_sum($_SESSION['panier']);
                        }
                        if ($nbPanier > 0):
                        ?>
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold">
                                <?= $nbPanier ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- Notifications (cloche) -->
                    <a href="<?= url('/notifications.php') ?>"
                       class="relative p-2 text-gray-700 hover:text-primary-600 transition" title="Notifications">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </a>

                    <!-- Menu utilisateur -->
                    <div class="relative group">
                        <button class="flex items-center gap-2 p-1 rounded-full hover:bg-gray-100 transition">
                            <img src="<?= h(asset_avatar($membreConnecte['avatar'])) ?>"
                                 alt="Avatar"
                                 class="w-8 h-8 rounded-full object-cover border-2 border-gray-200"
                                 onerror="this.src='<?= asset('assets/img/avatar-defaut.svg') ?>'">
                            <span class="hidden md:inline text-sm font-medium text-gray-700">
                                <?= h($membreConnecte['prenom']) ?>
                            </span>
                        </button>

                        <!-- Menu déroulant (hover) -->
                        <div class="absolute right-0 top-full mt-1 w-56 bg-white rounded-lg shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-900">
                                    <?= h($membreConnecte['prenom'] . ' ' . $membreConnecte['nom']) ?>
                                </p>
                                <p class="text-xs text-gray-500 truncate"><?= h($membreConnecte['email']) ?></p>
                            </div>
                            <div class="py-1">
                                <a href="<?= url('/profil.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Mon profil
                                </a>
                                <a href="<?= url('/historique.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Mes achats
                                </a>
                                <a href="<?= url('/favoris.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Mes favoris
                                </a>
                                <?php if (Auth::estAdmin()): ?>
                                    <div class="border-t border-gray-100 my-1"></div>
                                    <a href="<?= url('/admin/index.php') ?>"
                                       class="block px-4 py-2 text-sm text-primary-600 hover:bg-primary-50 font-semibold">
                                        Administration
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="border-t border-gray-100">
                                <a href="<?= url('/logout.php') ?>"
                                   class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    Déconnexion
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Non connecté -->
                    <a href="<?= url('/login.php') ?>"
                       class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-primary-600 transition">
                        Connexion
                    </a>
                    <a href="<?= url('/inscription.php') ?>"
                       class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 transition">
                        Inscription
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>

<?php
// Affichage automatique des messages flash (succès / erreur / info)
Flash::afficher();
?>

<!-- ===================================================================
     CONTENU PRINCIPAL (la page qui include ce header continue ici)
=================================================================== -->
<main class="flex-grow container mx-auto px-4 py-6">
