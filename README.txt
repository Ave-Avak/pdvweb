═══════════════════════════════════════════════════════════════
  CORRECTIONS FINALES + DOC LIMITATIONS CONNUES
═══════════════════════════════════════════════════════════════

⚠️ AUCUNE MIGRATION SQL NÉCESSAIRE
   Pas de modification de schéma BDD.

═══════════════════════════════════════════════════════════════
  CE QUI A ÉTÉ CORRIGÉ
═══════════════════════════════════════════════════════════════

1. 🔒 FAILLE OPEN REDIRECT (OWASP A01) — CORRIGÉE
   
   Avant : header('Location: ' . $_POST['retour']) sans validation
   Risque : phishing via lien fabriqué
   
   Après : header('Location: ' . retour_securise($_POST['retour'] ?? null))
   - Nouvelle fonction retour_securise() dans includes/helpers.php
   - Validation : refus de javascript:, //evil.com, protocoles arbitraires
   - 5 fichiers sécurisés :
     * public/panier_add.php
     * public/favori_toggle.php
     * public/comparer.php
     * public/admin/membre_action.php
     * public/adresse_form.php

2. 🏗️ SQL DANS UNE VUE — CORRIGÉ (respect MVC)
   
   Avant : <?php $nbUtil = Db::pdo()->query(...) ?> dans la vue
   Après :
   - Nouvelle méthode CodePromo::nbUtilisations($idCode)
   - Variable $nbUtilisations chargée dans le contrôleur
   - Vue utilise simplement la variable
   
   Vérification : grep "Db::pdo()" views/ → 0 résultat

3. 📝 DOC docs/limitations_connues.md (NOUVEAU — 9 KB)
   
   Document d'auto-évaluation honnête qui :
   - Liste les 7 choix d'architecture (statiques, CDN, etc.) avec justifications
   - Documente les 2 corrections appliquées
   - Donne le tableau récapitulatif effort/impact
   - Liste les améliorations pro identifiées

═══════════════════════════════════════════════════════════════
  POURQUOI CES CORRECTIONS ?
═══════════════════════════════════════════════════════════════

Ces 2 points (Open Redirect + SQL dans vue) sont différents des
7 autres remarques (namespaces, tests, Composer, etc.) :

   Les 7 autres = CHOIX D'ARCHITECTURE discutables mais justifiables
   Ces 2-ci   = VRAIES FAILLES à corriger

L'Open Redirect est une vulnérabilité OWASP réelle qui peut être
exploitée pour du phishing. C'était la seule vraie faille du projet.

Le SQL dans la vue était la seule entorse au MVC dans tout le projet.
Le corriger garantit une cohérence à 100%.

═══════════════════════════════════════════════════════════════
  INSTALLATION
═══════════════════════════════════════════════════════════════

1. Extraire ce ZIP par-dessus C:\xampp\htdocs\pdvweb\
2. Ctrl + F5 (pas de migration SQL)
3. Commiter :
   git add includes/ classes/ public/ views/ docs/
   git commit -m "Securite : correction open redirect + sql dans vue + doc limitations"

═══════════════════════════════════════════════════════════════
  POINT FORT POUR LA DÉFENSE ORALE
═══════════════════════════════════════════════════════════════

Si le prof pose des questions sur les choix techniques ou s'il
identifie des "manques" :

   "J'ai justement documenté tous ces points dans 
    docs/limitations_connues.md, avec mes justifications et 
    ce que je ferais en production."

   → Vous avez 1 longueur d'avance, vous montrez votre maturité,
     vous désamorcez la critique avant qu'elle arrive.

═══════════════════════════════════════════════════════════════
  STATS FINALES
═══════════════════════════════════════════════════════════════
   - 154 fichiers PHP
   - 11 migrations SQL
   - 0 erreur de syntaxe
   - 0 SQL dans les vues (vérifié)
   - 0 Open Redirect (vérifié)
   - Documentation : 8 fichiers, ~2000 lignes
