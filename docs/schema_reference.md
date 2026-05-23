# 📐 Référence des colonnes de la base de données

Document de référence à consulter avant tout code SQL ou accès à un `$row['colonne']`.

Ce document existe parce que la nomenclature du schéma initial n'est **pas parfaitement cohérente** (certains noms abrégés, d'autres complets, certaines colonnes "corps" et d'autres "contenu", etc.). On documente donc explicitement les noms réels.

---

## Tables principales

### `membre`
```
id_membre, nom, prenom, date_naissance, email, email_verifie, login,
mot_passe, avatar, statut, indesirable, derniere_connexion,
date_inscription, date_anonymisation
```
- `statut` : ENUM('membre', 'admin')
- `indesirable` : 0/1 (bloqué)
- `date_anonymisation` : NULL = compte actif, sinon timestamp anonymisation RGPD

### `adresse`
```
id_adresse, id_membre, libelle, nom, prenom, rue, numero, complement,
cp, ville, pays, telephone, type, est_defaut, date_creation
```
- `libelle` : nom donné par l'utilisateur (« Domicile », « Bureau »)
- `type` : ENUM('livraison', 'facturation', 'les_deux')

### `tentative_connexion` ⚠️ Noms abrégés
```
id_tentative, login_essaye, ip, user_agent, date_tent, succes
```
- `login_essaye` (PAS `login`)
- `date_tent` (PAS `date_tentative`)

### `log_connexion`
```
id_log, id_membre, date_log, ip
```

### `audit_log`
```
id_log, id_membre, action, entite, id_entite, details, ip, user_agent, date_action
```
- `details` : JSON
- `entite` : nom de la table affectée (ex: 'membre', 'article')

### `token`
```
id_token, id_membre, type, token_hash, date_creation, date_expiration, date_utilisation
```
- `type` : ENUM('reset_password', 'verif_email', 'invitation')

---

## Catalogue

### `categorie`
```
id_categorie, code, nom, description, ordre
```

### `article`
```
id_article, nom, id_categorie, description, prix, image, stock,
dispo, poids_grammes, date_ajout
```
- `dispo` : 0/1 (sert de soft delete)

### `vue_article`
```
id_vue, id_article, id_membre, ip, date_vue
```
- `id_membre` peut être NULL si visiteur non connecté

### `note_article` ⚠️ Clé composite, pas d'id_note
```
(id_membre, id_article) [PK composite], note, avis, date_note
```
- **PAS de colonne `id_note`** — clé composite (id_membre, id_article)
- `avis` (PAS `commentaire`)

---

## Commerce

### `statut_commande`
```
id_statut, code, nom, couleur, ordre
```

### `achat_facture`
```
id_facture, id_membre, id_statut, id_adresse_livraison,
id_adresse_facturation, reference, sous_total, montant_frais_port,
mode_livraison, montant_remise, prix_total, date_achat, notes
```

### `ligne_facture`
```
id_ligne, id_facture, id_article, quantite, prix_unitaire
```

### `paiement`
```
id_paiement, id_facture, methode, reference_ext, montant, statut, date_paiement
```

### `frais_port` ⚠️ "nom" et non "libelle"
```
id_frais, nom, pays, montant_min_panier, montant_max_panier,
prix, delai_jours, actif
```
- `nom` (PAS `libelle`) — ex: 'Standard', 'Express'

### `code_promo`
```
id_code, code, description, type_remise, valeur, montant_min_panier,
utilisations_max, utilisations_par_membre, date_debut, date_fin, actif
```
- `type_remise` : ENUM('pourcentage', 'montant_fixe', 'livraison_offerte')

### `code_promo_utilisation`
```
id_utilisation, id_code, id_membre, id_facture, montant_remise, date_utilisation
```

### `favori`
```
(id_membre, id_article) [PK composite], date_ajout
```

---

## Contenu / blog

### `billet`
```
id_billet, id_membre, titre, corps, date_billet,
date_suppression, id_membre_suppression
```
- `corps` (PAS `contenu`) — texte Markdown

### `commentaire` ⚠️ "corps" et "id_commentaire" complet
```
id_commentaire, id_billet, id_membre, corps, date_comm,
date_suppression, id_membre_suppression
```
- `id_commentaire` (PAS `id_comm`)
- `corps` (PAS `contenu`)
- `date_comm` (abrégé)

### `tag`
```
id_tag, code, nom
```

### `billet_tag`
```
(id_billet, id_tag) [PK composite]
```

### `like_contenu`
```
(id_membre, type, id_cible) [PK composite], date_like
```
- `type` : ENUM('billet', 'commentaire', 'article')
- `id_cible` : ID de la cible (selon le type)

---

## Communication

### `minichat`
```
id_message, id_membre, pseudo, message, date_message
```
- `pseudo` (ajouté en migration 07) : pseudo choisi par le membre pour cette session
- `message` (le contenu)

### `message_prive`
```
id_message, id_expediteur, id_destinataire, sujet, corps, lu, date_envoi
```
- Messagerie privée entre membres
- `lu` : 0/1 (côté destinataire)

### `mp_blocage` (ajoutée en migration 06)
```
(id_membre, id_membre_bloque) [PK composite], date_blocage
```
- Permet à un membre d'en bloquer un autre (blocage unidirectionnel)
- Un admin peut toujours envoyer un MP même si bloqué (modération)

### `notification`
```
id_notification, id_membre, type, titre, message, url_cible,
lue, date_creation, date_lecture
```

---

## Technique

### `parametre`
```
cle, valeur, description, date_maj
```
- PK : `cle`

### `recherche_log`
```
id_recherche, id_membre, terme, nb_resultats, contexte, date_recherche
```
- `contexte` : ENUM('blog', 'catalogue', 'tout')

---

## ⚠️ Tables non utilisées par le code actuel

Le schéma initial était ambitieux ; certaines tables n'ont jamais été branchées au code et restent dans la BDD comme "amélioration future" :

- `role`, `permission`, `role_permission`, `membre_role` — RBAC granulaire (le code utilise juste `membre.statut` ENUM)
- `consentement_rgpd` — traçage des consentements
- `newsletter_abonne` — abonnement newsletter

**Décision** : on les laisse en place (pas de migration de suppression) car elles sont des points d'évolution naturels. Documenté dans `docs/securite.md`.

---

## Conventions de nommage récapitulées

| Pattern | Exemple |
|---|---|
| Préfixe `id_` pour les PK | `id_membre`, `id_article` |
| Préfixe `date_` pour les datetimes | `date_inscription`, `date_creation` |
| `nom` pour les libellés affichables | `categorie.nom`, `statut_commande.nom`, `frais_port.nom` |
| `code` pour les slugs techniques | `categorie.code = 'informatique'` |
| `corps` pour le texte long | `billet.corps`, `commentaire.corps` |
| `message` pour les messages courts | `minichat.message`, `notification.message` |
| `avis` pour les avis produits | `note_article.avis` |
| Soft delete : `date_suppression` + `id_membre_suppression` | `billet`, `commentaire` |

---

*Document maintenu à jour à chaque modification de schéma. Dernière mise à jour : étape 9.*
