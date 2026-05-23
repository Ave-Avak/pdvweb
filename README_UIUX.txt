═══════════════════════════════════════════════════════════════
  REFONTE UI/UX COMPLÈTE — Récap de tout ce qui change
═══════════════════════════════════════════════════════════════

🎨 PALETTE & THÈME (includes/header.php)
─────────────────────────────────────────────
- Bleu → Indigo (#6366f1) : plus chaleureux et moderne
- 4 couleurs sémantiques : success, warning, danger, info
- Police Inter chargée via Google Fonts
- Animations Tailwind : fade-in, slide-up, pulse-soft

♿ ACCESSIBILITÉ (header.php + style.css)
─────────────────────────────────────────────
- Skip link "Aller au contenu" visible au focus clavier
- id="main-content" sur le <main>
- role="banner", role="main", aria-label sur la navbar
- Focus visible amélioré (outline indigo)
- @media (prefers-reduced-motion) respecté

🏠 PAGE D'ACCUEIL (public/index.php + views/accueil.php)
─────────────────────────────────────────────
Refonte complète en 7 sections :
1. Hero avec gradient + motif grille + animations
2. 4 KPI dynamiques (articles, catégories, membres, billets)
3. 3 rayons (informatique, livre, hi-fi) avec icônes colorées
4. Coups de cœur (3 articles les mieux notés, avec étoiles)
5. Dernières actualités du blog
6. Comparaison "Visiteur vs Membre" (incitation à l'inscription)
7. Login rapide avec icône, "mot de passe oublié", design soigné

📦 EMPTY STATES (favoris, historique)
─────────────────────────────────────────────
- Icône colorée dans un rond
- Titre rassurant + explication
- Call-to-action clair vers le catalogue

🎨 COMPOSANTS CSS (style.css)
─────────────────────────────────────────────
Nouvelles classes utilitaires :
- .btn / .btn-primary / .btn-secondary / .btn-success / .btn-danger / .btn-ghost
- .btn-sm / .btn-lg
- .badge / .badge-primary / .badge-success / etc.
- .card / .card-padded / .card-hover
- .input-base
- .spinner (loader animé)
- .skeleton (placeholder shimmer)
- .line-clamp-1/2/3
- .scrollbar-thin

🛡️ ESPACE ADMIN (includes/admin_header.php)
─────────────────────────────────────────────
NOUVEAU fragment réutilisable pour identité visuelle admin :
- Bannière gradient indigo foncé
- Fil d'Ariane avec icône maison
- Icône ⚙ à côté du titre
- Slot d'actions à droite

INSTALLATION
─────────────────────────────────────────────
1. Extraire le ZIP par-dessus C:\xampp\htdocs\pdvweb\
2. Ctrl + Shift + R sur le site (vider cache)
3. Tester :
   - Page d'accueil (énorme changement visuel)
   - /favoris.php (sans favoris) → empty state
   - /historique.php (sans achats) → empty state
   - Tab key au chargement → skip link apparaît
   - Admin → fonctionne (pas encore changé visuellement,
     ça se fera page par page si vous le souhaitez)

NB : la bannière admin (admin_header.php) n'est pas encore
appliquée aux 23 pages admin — c'est un travail séparé qui
nécessite de modifier chaque vue. Dites-moi si vous voulez
que je l'applique automatiquement à toutes.

