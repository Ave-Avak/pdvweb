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
                        // === Palette principale ===
                        // Bleu profond plus chaleureux que le bleu pur Tailwind
                        primary: {
                            50:  '#eef2ff',
                            100: '#e0e7ff',
                            200: '#c7d2fe',
                            300: '#a5b4fc',
                            400: '#818cf8',
                            500: '#6366f1',  // indigo principal
                            600: '#4f46e5',
                            700: '#4338ca',
                            800: '#3730a3',
                            900: '#312e81',
                            950: '#1e1b4b'
                        },
                        // === Couleurs sémantiques ===
                        success: {
                            50:  '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d'
                        },
                        warning: {
                            50:  '#fffbeb',
                            100: '#fef3c7',
                            500: '#f59e0b',
                            600: '#d97706',
                            700: '#b45309'
                        },
                        danger: {
                            50:  '#fef2f2',
                            100: '#fee2e2',
                            500: '#ef4444',
                            600: '#dc2626',
                            700: '#b91c1c'
                        },
                        info: {
                            50:  '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8'
                        }
                    },
                    fontFamily: {
                        sans: ['Inter', 'system-ui', '-apple-system', 'sans-serif'],
                        display: ['Inter', 'system-ui', 'sans-serif']
                    },
                    boxShadow: {
                        // Ombres douces et modernes
                        'soft': '0 2px 8px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.06)',
                        'soft-lg': '0 10px 30px rgba(0, 0, 0, 0.05), 0 1px 4px rgba(0, 0, 0, 0.06)'
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.3s ease-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'pulse-soft': 'pulseSoft 2s ease-in-out infinite'
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        },
                        pulseSoft: {
                            '0%, 100%': { opacity: '1' },
                            '50%': { opacity: '0.7' }
                        }
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

<!-- Skip link : accessibilité clavier (ARIA / WCAG 2.4.1) -->
<a href="#main-content" class="skip-link">Aller au contenu principal</a>

<!-- ===================================================================
     EN-TÊTE / NAVBAR
=================================================================== -->
<header class="bg-white shadow-sm sticky top-0 z-50" role="banner">
    <nav class="container mx-auto px-4 py-3" aria-label="Navigation principale">
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

                <!-- Comparateur (Phase 3.3) — accessible à tous -->
                <?php
                $nbCompar = !empty($_SESSION['comparateur']) ? count($_SESSION['comparateur']) : 0;
                ?>
                <?php if ($nbCompar > 0): ?>
                    <a href="<?= url('/comparer.php') ?>"
                       class="relative p-2 text-gray-700 hover:text-primary-600 transition" title="Comparateur (<?= $nbCompar ?>)">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>
                        </svg>
                        <span class="absolute -top-1 -right-1 bg-primary-600 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold">
                            <?= $nbCompar ?>
                        </span>
                    </a>
                <?php endif; ?>

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

                    <!-- Messagerie privée (enveloppe) -->
                    <?php
                    $nbMP = 0;
                    try {
                        $nbMP = MessagePrive::nbNonLus(Auth::id());
                    } catch (Throwable $e) { /* silent */ }
                    ?>
                    <a href="<?= url('/messages.php') ?>"
                       class="relative p-2 text-gray-700 hover:text-primary-600 transition" title="Messagerie">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <?php if ($nbMP > 0): ?>
                            <span class="absolute top-0 right-0 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">
                                <?= $nbMP > 9 ? '9+' : $nbMP ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- Notifications (cloche) -->
                    <?php
                    // Calcul du nombre de notifications non lues
                    $nbNotifs = 0;
                    try {
                        $nbNotifs = Notification::nbNonLues(Auth::id());
                    } catch (Throwable $e) { /* silent */ }
                    ?>
                    <a href="<?= url('/notifications.php') ?>"
                       class="relative p-2 text-gray-700 hover:text-primary-600 transition" title="Notifications">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <?php if ($nbNotifs > 0): ?>
                            <span class="absolute top-0 right-0 bg-red-500 text-white text-[10px] font-bold rounded-full min-w-[18px] h-[18px] flex items-center justify-center px-1">
                                <?= $nbNotifs > 9 ? '9+' : $nbNotifs ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- Menu utilisateur -->
                    <div class="relative" data-menu>
                        <button type="button" data-menu-toggle
                                class="flex items-center gap-2 p-1 rounded-full hover:bg-gray-100 transition">
                            <img src="<?= h(asset_avatar($membreConnecte['avatar'])) ?>"
                                 alt="Avatar"
                                 class="w-8 h-8 rounded-full object-cover border-2 border-gray-200"
                                 onerror="this.src='<?= asset('assets/img/avatar-defaut.svg') ?>'">
                            <span class="hidden md:inline text-sm font-medium text-gray-700">
                                <?= h($membreConnecte['prenom']) ?>
                            </span>
                        </button>

                        <!-- Menu déroulant (click) -->
                        <div data-menu-panel
                             class="hidden absolute right-0 top-full mt-1 w-56 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
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
                                <a href="<?= url('/adresses.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Mes adresses
                                </a>
                                <a href="<?= url('/messages.php') ?>" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Ma messagerie
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
<main id="main-content" class="flex-grow container mx-auto px-4 py-6 animate-fade-in" role="main">
