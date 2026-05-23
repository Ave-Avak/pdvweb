═══════════════════════════════════════════════════════════════
  CORRECTIF — 2 problèmes corrigés
═══════════════════════════════════════════════════════════════

✅ 1. FIX BUG INDEX.PHP (erreur 'actif' in where clause)
   Vous aviez encore l'ancien fichier index.php avec
   "WHERE actif = 1" sur les catégories.
   Le nouveau fichier corrige cette requête.

✅ 2. JOURNAL D'AUDIT ADMIN ACTIVÉ
   Avant : aucune action admin n'était tracée (à part 
   bloquer/débloquer membre et reset mdp).
   Maintenant : TOUTES les actions admin sont tracées :
   
   - billet.creer / billet.modifier / billet.supprimer / billet.restaurer
   - article.creer / article.modifier / article.supprimer
   - categorie.creer / categorie.modifier
   - commande.changer_statut
   - code_promo.toggle
   - frais_port.toggle
   - commentaire.restaurer

═══════════════════════════════════════════════════════════════
  INSTALLATION
═══════════════════════════════════════════════════════════════

1. Extraire ce ZIP par-dessus C:\xampp\htdocs\pdvweb\
2. Ctrl+F5 sur le site

NB : pas de migration SQL nécessaire pour ce fix.

═══════════════════════════════════════════════════════════════
  TEST AUDIT LOG
═══════════════════════════════════════════════════════════════

1. Connectez-vous en admin
2. Effectuez plusieurs actions :
   - Modifier un billet
   - Toggler un code promo
   - Changer le statut d'une commande
3. Allez sur /admin/audit.php
4. Vous devez voir TOUTES vos actions listées
   avec date, IP, type d'action et entité concernée

═══════════════════════════════════════════════════════════════
  RAPPEL — MIGRATIONS À EXÉCUTER
═══════════════════════════════════════════════════════════════

Si pas encore fait, exécutez avant de tester :
  sql/08_migration_billet_resume_image.sql
  sql/09_migration_categorie_actif.sql

Sinon : erreurs SQL sur les billets (résumé/image) ou
les catégories.

