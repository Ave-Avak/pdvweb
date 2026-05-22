-- =====================================================================
-- PDVWeb  -  Données de test  (34 tables - version pro complète)
-- À exécuter APRÈS 01_schema.sql
-- =====================================================================

USE pdvweb;


-- =====================================================================
-- GROUPE 1 - Identité & Sécurité
-- =====================================================================

-- Rôles
INSERT INTO role (id_role, code, nom, description) VALUES
  (1, 'admin',      'Administrateur', 'Accès complet à l''administration'),
  (2, 'moderateur', 'Modérateur',     'Peut gérer les commentaires et le mini-chat'),
  (3, 'membre',     'Membre',         'Membre standard du site');

-- Permissions
INSERT INTO permission (id_permission, code, nom, groupe) VALUES
  (1,  'membre.bloquer',      'Bloquer un membre',                'Membres'),
  (2,  'membre.consulter',    'Consulter le profil d''un membre', 'Membres'),
  (3,  'article.creer',       'Créer un article',                 'Articles'),
  (4,  'article.modifier',    'Modifier un article',              'Articles'),
  (5,  'article.supprimer',   'Supprimer un article',             'Articles'),
  (6,  'billet.creer',        'Créer un billet',                  'Blog'),
  (7,  'billet.modifier',     'Modifier un billet',               'Blog'),
  (8,  'billet.supprimer',    'Supprimer un billet',              'Blog'),
  (9,  'commentaire.moderer', 'Modérer les commentaires',         'Blog'),
  (10, 'minichat.moderer',    'Modérer le mini-chat',             'Minichat'),
  (11, 'commande.consulter',  'Consulter les commandes',          'Ventes'),
  (12, 'commande.modifier',   'Modifier le statut des commandes', 'Ventes'),
  (13, 'parametre.modifier',  'Modifier les paramètres du site',  'Système'),
  (14, 'audit.consulter',     'Consulter l''audit log',           'Système');

-- Rôle "admin" : toutes les permissions
INSERT INTO role_permission (id_role, id_permission)
  SELECT 1, id_permission FROM permission;

-- Rôle "moderateur" : permissions de modération
INSERT INTO role_permission (id_role, id_permission) VALUES
  (2, 1),  (2, 2),  (2, 9),  (2, 10);

-- Mot de passe admin : "admin2026"  (hash bcrypt à générer)
-- Mot de passe membres : "test1234"
INSERT INTO membre (id_membre, nom, prenom, date_naissance, email, email_verifie,
                    login, mot_passe, statut)
VALUES
  (1, 'Admin',   'Site',   '1980-01-01', 'admin@pdvweb.local',         1,
   'admin',    '$2y$10$PLACEHOLDERPLACEHOLDERPLACEHOLDERPLACEHOLDERPLACEHOLDERPL', 'admin'),
  (2, 'Dupont',  'Jean',   '1990-05-12', 'jean.dupont@example.com',    1,
   'jdupont',  '$2y$10$PLACEHOLDERPLACEHOLDERPLACEHOLDERPLACEHOLDERPLACEHOLDERPL', 'membre'),
  (3, 'Martin',  'Sophie', '1985-09-23', 'sophie.martin@example.com',  1,
   'smartin',  '$2y$10$PLACEHOLDERPLACEHOLDERPLACEHOLDERPLACEHOLDERPLACEHOLDERPL', 'membre'),
  (4, 'Lambert', 'Marc',   '1995-02-15', 'marc.lambert@example.com',   1,
   'mlambert', '$2y$10$PLACEHOLDERPLACEHOLDERPLACEHOLDERPLACEHOLDERPLACEHOLDERPL', 'membre');

-- Attribution des rôles
INSERT INTO membre_role (id_membre, id_role) VALUES
  (1, 1),  -- admin -> Administrateur
  (2, 3),  -- jdupont -> Membre
  (3, 3),  -- smartin -> Membre
  (4, 3);  -- mlambert -> Membre

-- Adresses
INSERT INTO adresse (id_membre, libelle, nom, prenom, rue, numero, cp, ville, telephone, type, est_defaut) VALUES
  (1, 'Bureau',   'Admin',   'Site',   'Rue de l''Administration', '1',  '1000', 'Bruxelles', '+32 2 123 45 67', 'les_deux', 1),
  (2, 'Domicile', 'Dupont',  'Jean',   'Rue du Test',              '12', '1050', 'Bruxelles', '+32 2 234 56 78', 'les_deux', 1),
  (2, 'Bureau',   'Dupont',  'Jean',   'Avenue Louise',            '120','1050', 'Bruxelles', '+32 2 234 56 79', 'livraison', 0),
  (3, 'Domicile', 'Martin',  'Sophie', 'Avenue des Lilas',         '8',  '4000', 'Liège',     '+32 4 345 67 89', 'les_deux', 1),
  (4, 'Domicile', 'Lambert', 'Marc',   'Place du Marché',          '3',  '7000', 'Mons',      '+32 65 12 34 56', 'les_deux', 1);


-- =====================================================================
-- GROUPE 2 - Audit & RGPD
-- =====================================================================

-- Logs de connexion (pour stats admin)
INSERT INTO log_connexion (id_membre, date_log, ip) VALUES
  (2, NOW() - INTERVAL 1 DAY,  '192.168.1.10'),
  (2, NOW() - INTERVAL 2 DAY,  '192.168.1.10'),
  (2, NOW() - INTERVAL 5 DAY,  '192.168.1.10'),
  (3, NOW() - INTERVAL 1 DAY,  '192.168.1.11'),
  (3, NOW(),                   '192.168.1.11'),
  (4, NOW() - INTERVAL 3 DAY,  '192.168.1.12');

-- Quelques entrées d'audit log
INSERT INTO audit_log (id_membre, action, entite, id_entite, details, ip) VALUES
  (1, 'membre.connexion', 'membre', 1, '{"reussi": true}', '127.0.0.1'),
  (1, 'article.creer',    'article', 1, '{"nom": "MacBook Air M3"}', '127.0.0.1');


-- =====================================================================
-- GROUPE 3 - Catalogue
-- =====================================================================

-- Catégories
INSERT INTO categorie (id_categorie, code, nom, description, ordre) VALUES
  (1, 'informatique', 'Informatique', 'Ordinateurs, périphériques et accessoires', 1),
  (2, 'livre',        'Livres',       'Romans, essais et ouvrages techniques',     2),
  (3, 'hifi',         'Hi-Fi',        'Casques, enceintes, amplificateurs',        3);

-- INFORMATIQUE (id_categorie = 1)
INSERT INTO article (nom, id_categorie, description, prix, stock, image, poids_grammes) VALUES
  ('MacBook Air M3 13"', 1,
   'Ordinateur portable Apple, puce M3, 8 Go RAM, 256 Go SSD, écran Liquid Retina 13,6".',
   1299.00, 15, 'macbook-air-m3.jpg', 1240),
  ('Dell XPS 13 Plus', 1,
   'Ultraportable Intel Core i7, 16 Go RAM, 512 Go SSD, écran OLED 13,4".',
   1599.00, 8, 'dell-xps13.jpg', 1260),
  ('iPad Air 11" (M2)', 1,
   'Tablette Apple avec puce M2, 128 Go, Wi-Fi, écran Liquid Retina 11".',
   799.00, 20, 'ipad-air.jpg', 462),
  ('Souris Logitech MX Master 3S', 1,
   'Souris ergonomique sans fil, capteur 8000 dpi, USB-C, Bluetooth multidevice.',
   109.00, 50, 'mx-master-3s.jpg', 141),
  ('Clavier mécanique Keychron K8', 1,
   'Clavier mécanique sans fil, switches Gateron Brown, layout TKL, rétroéclairage RGB.',
   119.00, 30, 'keychron-k8.jpg', 960),
  ('Écran Dell UltraSharp 27" 4K', 1,
   'Moniteur IPS 27" 4K UHD, USB-C, HDR400, idéal bureautique et création.',
   549.00, 12, 'dell-u2723.jpg', 6500);

-- LIVRE (id_categorie = 2)
INSERT INTO article (nom, id_categorie, description, prix, stock, image, poids_grammes) VALUES
  ('Sapiens - Une brève histoire de l''humanité', 2,
   'Yuval Noah Harari. Comment l''Homo sapiens a-t-il conquis le monde ? Essai best-seller.',
   24.90, 40, 'sapiens.jpg', 520),
  ('Clean Code', 2,
   'Robert C. Martin. Le manuel de référence pour écrire du code propre et maintenable.',
   42.00, 25, 'clean-code.jpg', 690),
  ('Le Petit Prince', 2,
   'Antoine de Saint-Exupéry. Édition illustrée, un classique intemporel.',
   12.50, 100, 'petit-prince.jpg', 180),
  ('PHP 8 - Développez un site web dynamique', 2,
   'Olivier Heurtel. Eyrolles, édition 2024. Guide complet pour développeurs PHP.',
   39.90, 18, 'php8-heurtel.jpg', 850),
  ('1984', 2,
   'George Orwell. Le grand classique de la dystopie politique, en édition de poche.',
   8.90, 60, '1984.jpg', 230),
  ('L''Étranger', 2,
   'Albert Camus. Folio Gallimard. Roman fondateur de la littérature du XXe siècle.',
   8.50, 75, 'etranger.jpg', 160);

-- HI-FI (id_categorie = 3)
INSERT INTO article (nom, id_categorie, description, prix, stock, image, poids_grammes) VALUES
  ('Casque Sony WH-1000XM5', 3,
   'Casque sans fil à réduction de bruit active. Autonomie 30h, USB-C.',
   379.00, 18, 'wh1000xm5.jpg', 250),
  ('Enceinte Bose SoundLink Flex', 3,
   'Enceinte Bluetooth portable, étanche IP67, jusqu''à 12h d''autonomie.',
   169.00, 25, 'bose-flex.jpg', 590),
  ('Platine vinyle Pro-Ject T1', 3,
   'Platine manuelle, plateau en verre, cellule Ortofon OM5e, finition piano.',
   349.00, 10, 'project-t1.jpg', 4500),
  ('Ampli Marantz PM6007', 3,
   'Amplificateur stéréo intégré 2x60W, DAC intégré, entrée phono MM.',
   599.00, 6, 'marantz-pm6007.jpg', 7400),
  ('Écouteurs Apple AirPods Pro 2', 3,
   'Écouteurs sans fil intra-auriculaires, réduction de bruit active, boîtier MagSafe.',
   279.00, 35, 'airpods-pro-2.jpg', 50),
  ('Enceintes Triangle Borea BR03', 3,
   'Enceintes bibliothèque haute fidélité, 100W, paire, finition noyer.',
   549.00, 8, 'triangle-br03.jpg', 11000);

-- Quelques notes
INSERT INTO note_article (id_membre, id_article, note, avis) VALUES
  (2, 13, 5, 'Réduction de bruit impressionnante, je recommande !'),
  (3, 9,  5, 'Un classique indémodable, à offrir absolument.'),
  (4, 4,  4, 'Très bonne souris, juste un peu lourde à la longue.');

-- Quelques vues d'articles
INSERT INTO vue_article (id_article, id_membre, ip, date_vue) VALUES
  (1, 2,    '192.168.1.10', NOW() - INTERVAL 2 HOUR),
  (1, NULL, '192.168.1.99', NOW() - INTERVAL 1 HOUR),
  (1, 3,    '192.168.1.11', NOW() - INTERVAL 30 MINUTE),
  (13, 2,   '192.168.1.10', NOW() - INTERVAL 3 HOUR),
  (7, 3,    '192.168.1.11', NOW() - INTERVAL 4 HOUR);


-- =====================================================================
-- GROUPE 4 - Vente & Paiement
-- =====================================================================

-- Statuts de commande
INSERT INTO statut_commande (id_statut, code, nom, couleur, ordre) VALUES
  (1, 'en_attente_paiement', 'En attente de paiement', 'orange', 1),
  (2, 'paye',                'Payée',                  'blue',   2),
  (3, 'preparation',         'En préparation',         'indigo', 3),
  (4, 'expedie',             'Expédiée',               'purple', 4),
  (5, 'livre',               'Livrée',                 'green',  5),
  (6, 'annule',              'Annulée',                'red',    6),
  (7, 'rembourse',           'Remboursée',             'gray',   7);

-- Grille de frais de port
INSERT INTO frais_port (nom, pays, montant_min_panier, montant_max_panier, prix, delai_jours) VALUES
  ('Standard',         'Belgique', 0,    50,    5.95, 3),
  ('Standard',         'Belgique', 50,   NULL,  0.00, 3),    -- gratuit dès 50€
  ('Express',          'Belgique', 0,    NULL,  12.95, 1),
  ('Standard Europe',  'France',   0,    NULL,  9.95, 5),
  ('Standard Europe',  'Allemagne',0,    NULL,  9.95, 5);

-- Code promo
INSERT INTO code_promo (code, description, type_remise, valeur, montant_min_panier, utilisations_max, date_debut, date_fin) VALUES
  ('BIENVENUE10', 'Bienvenue : -10% sur la première commande', 'pourcentage', 10.00, 0,
    NULL, '2026-01-01 00:00:00', '2026-12-31 23:59:59'),
  ('LIVRAISON',   'Livraison offerte',                          'livraison_offerte', 0, 0,
    NULL, '2026-01-01 00:00:00', '2026-12-31 23:59:59'),
  ('NOEL2026',    'Spécial Noël : -15€ dès 100€ d''achat',     'montant_fixe', 15.00, 100.00,
    500,  '2026-12-01 00:00:00', '2026-12-31 23:59:59');


-- =====================================================================
-- GROUPE 5 - Engagement
-- =====================================================================

-- Favoris
INSERT INTO favori (id_membre, id_article) VALUES
  (2, 1),    -- Jean ♥ MacBook
  (2, 13),   -- Jean ♥ Sony WH-1000XM5
  (3, 7);    -- Sophie ♥ Sapiens

-- Abonnés newsletter
INSERT INTO newsletter_abonne (email, id_membre) VALUES
  ('jean.dupont@example.com',   2),
  ('sophie.martin@example.com', 3),
  ('curieux@example.com',       NULL);   -- non-membre


-- =====================================================================
-- GROUPE 6 - Contenu
-- =====================================================================

-- Tags
INSERT INTO tag (id_tag, code, nom) VALUES
  (1, 'promo',     'Promotion'),
  (2, 'nouveaute', 'Nouveauté'),
  (3, 'conseil',   'Conseil'),
  (4, 'evenement', 'Événement');

-- Billets
INSERT INTO billet (id_membre, titre, corps) VALUES
  (1, 'Bienvenue sur PDVWeb !',
   'Bienvenue sur notre toute nouvelle boutique en ligne ! Vous trouverez ici une sélection soigneusement choisie de matériel informatique, de livres et de produits hi-fi de qualité. N''hésitez pas à créer un compte pour profiter de toutes nos fonctionnalités et participer aux discussions sur le mini-chat.'),
  (1, 'Promotion sur les casques audio',
   'Pendant tout le mois de juin, profitez de tarifs préférentiels sur notre gamme de casques audio haut de gamme. Sony, Bose, Apple : les plus grandes marques sont à prix réduit. Connectez-vous pour découvrir nos offres exclusives membres.'),
  (1, 'Nouveau : MacBook Air M3 disponible !',
   'Le tout dernier MacBook Air avec la puce M3 est désormais disponible dans notre catalogue. Performance accrue, autonomie record et écran Liquid Retina : un concentré de technologie pour les pros et les créatifs.');

-- Liaisons billet <-> tags
INSERT INTO billet_tag (id_billet, id_tag) VALUES
  (1, 4),         -- Bienvenue -> Événement
  (2, 1),         -- Promo -> Promotion
  (3, 2),         -- MacBook -> Nouveauté
  (3, 3);         -- MacBook -> Conseil

-- Commentaires
INSERT INTO commentaire (id_billet, id_membre, corps) VALUES
  (1, 2, 'Super site, hâte de découvrir tout ça !'),
  (1, 3, 'Bravo pour le lancement, l''interface est très claire.'),
  (3, 4, 'Le M3 est-il déjà testé ? J''hésite avec le M2.');

-- Quelques likes
INSERT INTO like_contenu (id_membre, type, id_cible) VALUES
  (2, 'billet', 1),
  (3, 'billet', 1),
  (4, 'billet', 3),
  (2, 'article', 1),
  (3, 'commentaire', 1);


-- =====================================================================
-- GROUPE 7 - Communication
-- =====================================================================

-- Mini-chat
INSERT INTO minichat (id_membre, message) VALUES
  (2, 'Salut tout le monde, contente de découvrir le site !'),
  (3, 'Quelqu''un a déjà testé le casque Sony WH-1000XM5 ?'),
  (4, 'Bonjour à tous, je viens de m''inscrire.'),
  (2, 'Le MacBook Air M3 est vraiment excellent, je le recommande.');

-- Messages privés
INSERT INTO message_prive (id_expediteur, id_destinataire, sujet, corps) VALUES
  (2, 3, 'Question livre', 'Bonjour Sophie, tu as lu Sapiens ? Tu me le conseilles ?'),
  (3, 2, 'Re: Question livre', 'Salut Jean, oui je le conseille vraiment, lecture passionnante !');

-- Notifications
INSERT INTO notification (id_membre, type, titre, message, url_cible) VALUES
  (2, 'message_prive', 'Nouveau message', 'Sophie vous a répondu', '/messages.php?id=2'),
  (1, 'commentaire.nouveau', 'Nouveau commentaire', 'Marc a commenté votre billet "Nouveau : MacBook"', '/billet.php?id=3');


-- =====================================================================
-- GROUPE 8 - Configuration
-- =====================================================================

-- Paramètres applicatifs
INSERT INTO parametre (cle, valeur, description) VALUES
  ('site.nom',                  'PDVWeb',                                 'Nom du site affiché dans le header'),
  ('site.slogan',               'Votre boutique en ligne multi-rayons',   'Slogan sous le logo'),
  ('site.email_contact',        'contact@pdvweb.local',                   'Email de contact affiché en pied de page'),
  ('minichat.longueur_max',     '255',                                    'Longueur max d''un message du mini-chat'),
  ('minichat.nb_messages',      '10',                                     'Nb de derniers messages à afficher'),
  ('achat.qte_max_par_article', '10',                                     'Quantité max par article dans un achat'),
  ('upload.taille_max',         '2097152',                                'Taille max upload avatar (2 Mo en octets)'),
  ('securite.tentatives_max',   '5',                                      'Nb max de tentatives de login ratées'),
  ('securite.blocage_minutes',  '15',                                     'Durée de blocage après tentatives ratées'),
  ('paiement.tva_taux',         '21.00',                                  'Taux TVA Belgique (%)'),
  ('livraison.seuil_gratuit',   '50.00',                                  'Montant à partir duquel la livraison est offerte');


-- =====================================================================
-- IMPORTANT  :  les hashes de mots de passe ci-dessus sont des placeholders.
-- Exécutez maintenant :  php sql/generer_hashes.php
-- pour générer de vrais hashes bcrypt.
-- =====================================================================
