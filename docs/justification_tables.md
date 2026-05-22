# Justification des choix de modélisation

Document destiné à expliquer **pourquoi** chaque table a été créée, en particulier celles qui dépassent le schéma initial du professeur. Utilisable comme aide-mémoire pour la défense orale.

---

## Tables conformes au schéma initial du professeur

Ces 7 tables sont **directement issues** du *Schema Simplifié BD Projet Dev. Web* fourni :

| Table dans le code | Table du schéma prof | Remarque |
|---|---|---|
| `membre` | Membre/Admin | Identique, enrichie de `email_verifie`, `derniere_connexion` |
| `log_connexion` | LogConnexion | Identique |
| `billet` | Blog/News | Identique |
| `commentaire` | Commentaires | Identique + ajout d'une colonne `corps` (oubliée au schéma) |
| `article` | Article | Identique, `categorie` devient `id_categorie` (FK vers nouvelle table) |
| `achat_facture` | AchatFacture | Identique, enrichie pour gérer les statuts et adresses |
| `ligne_facture` | LigneFacture | Identique |

---

## Ajouts indispensables au cahier des charges

| Table | Pourquoi | Justification |
|---|---|---|
| `minichat` | **Cahier des charges** : "Le mini-chat propose une liste des 10 derniers messages" | Sans cette table, impossible de stocker les messages du mini-chat. Absente du schéma manuscrit, c'est un oubli. |

---

## Ajouts standards e-commerce (Tier 1)

Tables présentes dans **tous** les e-commerces modernes (Magento, PrestaShop, WooCommerce, Shopify).

### `categorie`
**Pourquoi ?** Le schéma prof avait `categorie` comme attribut d'`article` (chaîne ou ENUM).
**Justification** : Le passer en table dédiée permet à l'admin de renommer ou ajouter des catégories sans toucher au code. Pattern *Reference Table* standard.

### `adresse`
**Pourquoi ?** Le schéma prof stockait l'adresse dans `membre`.
**Justification** : Un même membre peut avoir plusieurs adresses :
- Adresse de facturation différente de l'adresse de livraison
- Plusieurs adresses de livraison (domicile, bureau, parents)
- L'adresse au moment de la commande doit être figée (si le membre déménage, sa commande passée garde l'adresse d'origine)

Normalisation 3NF classique.

### `statut_commande`
**Pourquoi ?** Le schéma prof avait juste une commande figée.
**Justification** : Une commande passe par plusieurs états : *en attente de paiement → payée → en préparation → expédiée → livrée*. Ces statuts doivent être référencés pour permettre à l'admin de les gérer et de filtrer. Sans cela, l'admin ne peut pas voir "toutes les commandes à expédier".

### `paiement`
**Pourquoi ?** Le prix total figurait directement sur la facture.
**Justification** : Une commande peut avoir plusieurs paiements :
- Acompte + solde
- Paiement échelonné
- Remboursement partiel
- Paiement par carte refusé puis nouveau paiement par PayPal

Modélisation *1 facture = N paiements*. Standard PCI-DSS pour e-commerce.

### `token`
**Pourquoi ?** Sécurité standard.
**Justification** : Permet d'implémenter :
- "Mot de passe oublié" via email (token à usage unique)
- Vérification d'email lors de l'inscription
- Invitations
On stocke un **hash** du token, jamais le token en clair (analogie avec les mots de passe).

### `tentative_connexion`
**Pourquoi ?** Sécurité anti brute-force.
**Justification** : Tracer les essais de login permet de bloquer un attaquant après N échecs en quelques minutes. Recommandation OWASP. Sans cela, un attaquant peut tester des millions de mots de passe en quelques minutes.

### `audit_log`
**Pourquoi ?** Conformité RGPD.
**Justification** : L'article 30 du RGPD impose un *registre des traitements*. Tracer qui a modifié quoi (membre, article, billet) répond à plusieurs exigences :
- Accountability (article 5.2 RGPD)
- Détection d'incidents
- Investigation forensique

Standard de l'industrie. Champ `details` en JSON pour stocker l'état avant/après.

### `consentement_rgpd`
**Pourquoi ?** Conformité RGPD.
**Justification** : Article 7 RGPD impose de pouvoir prouver le consentement. Cette table garde la trace : qui a accepté quoi, quand, depuis quelle IP. Cookies essentiels, cookies analytiques, newsletter, CGV.

### `frais_port`
**Pourquoi ?** Réalisme e-commerce.
**Justification** : Une commande réelle a des frais de livraison qui dépendent du pays, du poids, du montant. Cette grille permet à l'admin de définir des règles ("livraison gratuite dès 50€", "express +12€").

### `code_promo` + `code_promo_utilisation`
**Pourquoi ?** Marketing standard.
**Justification** : Permet à l'admin de créer des promotions ponctuelles. La table de liaison garantit le respect du nombre d'utilisations maximum (global et par membre).

### `notification`
**Pourquoi ?** UX standard 2026.
**Justification** : La pastille rouge sur la cloche en haut à droite. Quand un membre commente votre billet, quand votre commande change de statut, quand vous recevez un message privé. Améliore drastiquement l'engagement.

---

## Ajouts engagement / fidélité (Tier 2)

### `favori`
**Pourquoi ?** Liste de souhaits, pattern e-commerce universel.
**Justification** : Permet d'enregistrer des articles à acheter plus tard sans les mettre directement au panier. Augmente la rétention.

### `note_article`
**Pourquoi ?** Confiance acheteurs.
**Justification** : Les avis utilisateurs sont devenus indispensables sur Amazon, Booking, etc. Note 1-5 avec contrainte CHECK + avis textuel.

### `newsletter_abonne`
**Pourquoi ?** Marketing email.
**Justification** : Une personne peut s'abonner à la newsletter sans être membre du site. La table est donc distincte de `membre`.

### `like_contenu`
**Pourquoi ?** Engagement social.
**Justification** : Implémentation *polymorphe* : un seul `like_contenu` peut représenter un like sur un billet, un commentaire ou un article. Une colonne `type` + une colonne `id_cible` au lieu de 3 tables séparées. Pattern reconnu (utilisé par Laravel `morphTo`).

### `tag` + `billet_tag`
**Pourquoi ?** Catégorisation du blog.
**Justification** : Permet de filtrer les billets par #promo, #nouveauté, #conseil. Liaison N-N classique.

### `vue_article`
**Pourquoi ?** Analytique.
**Justification** : Comptage des consultations. Pour l'admin : "produits les plus vus mais peu vendus" → signal d'un problème de prix ou de description.

### `message_prive`
**Pourquoi ?** Communication 1-à-1.
**Justification** : Le mini-chat est public. Pour des échanges privés (poser une question à l'admin par ex.), il faut un système de messagerie privée. Très utilisé sur les forums et marketplaces.

---

## Ajouts permissions étendues (Tier 3)

### `role` + `permission` + `role_permission` + `membre_role`
**Pourquoi ?** Permissions fines.
**Justification** : Le schéma prof a un simple ENUM (`membre` / `admin`). Cette structure RBAC (*Role-Based Access Control*) permet :
- Créer un rôle "modérateur" qui peut gérer les commentaires mais pas les articles
- Cumuler les rôles (un membre peut être à la fois modérateur ET comptable)
- Modifier les permissions sans toucher au code

Standard de l'industrie. Pattern reconnu (Symfony Voters, Spring Security, etc.).
**Note** : On conserve `membre.statut` pour rester compatible avec le schéma prof. Les deux coexistent. À l'oral, on peut dire : *"`statut` est l'attribut hérité du schéma initial, `role` étend les capacités."*

---

## Ajouts paramétrage & stats (Tier 3)

### `parametre`
**Pourquoi ?** Configuration sans toucher au code.
**Justification** : Au lieu d'écrire `const NB_MESSAGES_MINICHAT = 10;` dans le code, on stocke `('minichat.nb_messages', '10')` en BDD. L'admin peut modifier ces valeurs via une page d'administration. Pattern *Key-Value Store*.

### `recherche_log`
**Pourquoi ?** Analytique.
**Justification** : Mémorise les termes recherchés. L'admin voit dans une page de stats :
- Les recherches les plus fréquentes
- Les recherches sans résultat (signal d'un manque de contenu)

---

## Points à anticiper à l'oral

**Q : "Pourquoi 34 tables alors que mon schéma n'en a que 7 ?"**
R : Les 7 tables du schéma initial sont **toutes** présentes. Les 27 autres sont des **extensions** qui couvrent des standards e-commerce modernes (Amazon, Shopify, etc.) sans rien retirer du schéma initial. C'est une démarche d'enrichissement, pas de remplacement.

**Q : "Pourquoi `role` ET `statut` dans `membre` ?"**
R : `statut` est conservé pour rester fidèle au schéma initial. `role` est la version étendue qui permet une granularité supplémentaire. Les deux coexistent par compatibilité.

**Q : "Pourquoi cet audit_log ? Tu te crois chez une banque ?"**
R : Le RGPD impose un registre des traitements (article 30). Sans audit log, l'application n'est pas conforme. Toute application qui manipule des données personnelles devrait en avoir un.

**Q : "Tu n'as pas un peu surchargé ?"**
R : Toutes les tables ajoutées sont issues d'une analyse des e-commerces du marché (Magento, Shopify, PrestaShop, WooCommerce). Aucune n'est gratuite : chacune répond à un besoin concret (paiement échelonné, frais de port variables, codes promo, RGPD, anti brute-force, etc.). On peut me demander la justification de chaque table individuellement.

**Q : "Pourquoi ne pas avoir utilisé un framework comme Symfony ou Laravel ?"**
R : J'ai préféré rester proche du style enseigné dans le cours (PHP procédural + PDO) pour cohérence pédagogique. Les classes utilitaires (`Auth`, `Db`, `Csrf`...) apportent les bénéfices de la POO sans la complexité d'un framework complet. Un MVC fait main démontre que je comprends le concept, sans dépendre d'une couche externe.
