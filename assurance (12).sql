-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 12 mai 2026 à 02:26
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `assurance`
--

-- --------------------------------------------------------

--
-- Structure de la table `admin`
--

CREATE TABLE `admin` (
  `id_user` int(11) NOT NULL,
  `niveau_acces` enum('superadmin','admin_agence') NOT NULL DEFAULT 'admin_agence',
  `id_agence` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `admin`
--

INSERT INTO `admin` (`id_user`, `niveau_acces`, `id_agence`) VALUES
(101, 'superadmin', NULL),
(102, '', 1);

-- --------------------------------------------------------

--
-- Structure de la table `agence`
--

CREATE TABLE `agence` (
  `id_agence` int(11) NOT NULL,
  `nom_agence` varchar(100) NOT NULL,
  `pays` varchar(50) DEFAULT NULL,
  `tel` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `avis_json` longtext DEFAULT NULL,
  `statut` enum('active','inactive') NOT NULL DEFAULT 'active',
  `adresse` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `agence`
--

INSERT INTO `agence` (`id_agence`, `nom_agence`, `pays`, `tel`, `email`, `avis_json`, `statut`, `adresse`) VALUES
(1, 'Protex Tunis', 'Tunisie', '71111222', 'tunisia@protex.tn', '[{\"id_client\":4,\"note\":1,\"commentaire\":\"kh\",\"auteur\":\"Anonyme\",\"id_avis\":\"1777744408_1288\",\"date_avis\":\"2026-05-02 19:53:28\",\"hidden\":false}]', 'active', NULL),
(2, 'Protex Sfax', 'Tunisie', '74444670', 'sfax@protex.tn', '[{\"id_client\":4,\"note\":5,\"commentaire\":\"bb\",\"auteur\":\"Anonyme\",\"id_avis\":\"1777912990_2166\",\"date_avis\":\"2026-05-04 18:43:10\"}]', 'active', NULL),
(3, 'Protex Sousse', 'Tunisie', '73333444', 'sousse@protex.tn', '[{\"id_client\":4,\"note\":5,\"commentaire\":\"naatiwha 5 aala ma yweti\",\"auteur\":\"Anonyme\",\"id_avis\":\"1777744144_7982\",\"date_avis\":\"2026-05-02 19:49:04\"}]', 'active', NULL),
(9, 'Protex nabeul', 'Tunisie', '44554541', 'qbd@protex.tn', '[{\"id_client\":4,\"note\":5,\"commentaire\":\"semha\",\"auteur\":\"Anonyme\",\"id_avis\":\"1777744386_2099\",\"date_avis\":\"2026-05-02 19:53:06\",\"hidden\":false},{\"id_client\":4,\"note\":5,\"commentaire\":\"b\",\"auteur\":\"Anonyme\",\"id_avis\":\"1778004966_8508\",\"date_avis\":\"2026-05-05 20:16:06\",\"hidden\":false}]', 'active', NULL),
(11, 'dsvj', 'sqkcj', '', 'qhsuc@protex.tn', NULL, 'active', NULL),
(12, 'svjnq', 'zddf', '24484884', 'ffb@protex.tn', NULL, 'active', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `agent`
--

CREATE TABLE `agent` (
  `id_user` int(11) NOT NULL,
  `agence` varchar(100) DEFAULT NULL,
  `salaire` decimal(10,2) DEFAULT NULL,
  `id_agence` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `agent`
--

INSERT INTO `agent` (`id_user`, `agence`, `salaire`, `id_agence`) VALUES
(103, 'Agence Centrale Tunis', 1500.00, 1);

-- --------------------------------------------------------

--
-- Structure de la table `audit_reclamation`
--

CREATE TABLE `audit_reclamation` (
  `id` int(10) UNSIGNED NOT NULL,
  `action` varchar(50) NOT NULL,
  `reclamation_id` int(10) UNSIGNED NOT NULL,
  `reponse_id` int(10) UNSIGNED DEFAULT NULL,
  `id_user` int(10) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `audit_reclamation`
--

INSERT INTO `audit_reclamation` (`id`, `action`, `reclamation_id`, `reponse_id`, `id_user`, `ip_address`, `details`, `created_at`) VALUES
(1, 'reponse_ajoutee', 37, 33, 102, '::1', 'qcdscdscsdc', '2026-05-12 00:58:56');

-- --------------------------------------------------------

--
-- Structure de la table `avis_agence`
--

CREATE TABLE `avis_agence` (
  `id_avis` int(11) NOT NULL,
  `note` int(11) NOT NULL CHECK (`note` between 1 and 5),
  `commentaire` text NOT NULL,
  `date_avis` datetime NOT NULL DEFAULT current_timestamp(),
  `id_agence` int(11) NOT NULL,
  `id_client` int(11) NOT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `avis_agence`
--

INSERT INTO `avis_agence` (`id_avis`, `note`, `commentaire`, `date_avis`, `id_agence`, `id_client`, `hidden`) VALUES
(1, 1, 'kh', '2026-05-02 19:53:28', 1, 4, 0),
(2, 5, 'bb', '2026-05-04 18:43:10', 2, 4, 1),
(3, 5, 'naatiwha 5 aala ma yweti', '2026-05-02 19:49:04', 3, 4, 0),
(4, 5, 'semha', '2026-05-02 19:53:06', 9, 4, 0),
(5, 5, 'b', '2026-05-05 20:16:06', 9, 4, 0),
(11, 5, 'hlowa', '2026-05-07 12:25:36', 3, 4, 0),
(12, 5, 'sfv', '2026-05-07 13:57:00', 1, 4, 0),
(13, 5, 'behya', '2026-05-07 15:54:04', 1, 4, 1);

-- --------------------------------------------------------

--
-- Structure de la table `categorie`
--

CREATE TABLE `categorie` (
  `id_categorie` int(11) NOT NULL,
  `nom_categorie` varchar(100) NOT NULL,
  `description_categorie` text DEFAULT NULL,
  `slug_categorie` varchar(100) DEFAULT NULL,
  `icone_categorie` varchar(100) DEFAULT NULL,
  `couleur_categorie` varchar(50) DEFAULT NULL,
  `description_front` varchar(255) DEFAULT NULL,
  `lien_front` varchar(255) DEFAULT NULL,
  `page_formule` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `categorie`
--

INSERT INTO `categorie` (`id_categorie`, `nom_categorie`, `description_categorie`, `slug_categorie`, `icone_categorie`, `couleur_categorie`, `description_front`, `lien_front`, `page_formule`) VALUES
(2, 'Auto', 'Assurance automobile et mobilité', NULL, NULL, NULL, NULL, NULL, NULL),
(3, 'Habitation', 'Protection du logement et du patrimoine.', NULL, NULL, NULL, NULL, NULL, NULL),
(4, 'Santé', 'Couverture santé et assistance médicale', NULL, NULL, NULL, NULL, NULL, NULL),
(5, 'Protection', 'Prévoyance , sécurité et assistance', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `client`
--

CREATE TABLE `client` (
  `id_user` int(11) NOT NULL,
  `numero_client` varchar(50) DEFAULT NULL,
  `id_agence` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `client`
--

INSERT INTO `client` (`id_user`, `numero_client`, `id_agence`) VALUES
(104, 'CL-TEST-01', 1),
(105, 'CL-10771', 1);

-- --------------------------------------------------------

--
-- Structure de la table `commentaire`
--

CREATE TABLE `commentaire` (
  `id_commentaire` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `date_commentaire` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_poste` int(11) NOT NULL,
  `id_client` int(11) NOT NULL,
  `id_commentaire_parent` int(11) DEFAULT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commentaire`
--

INSERT INTO `commentaire` (`id_commentaire`, `contenu`, `date_commentaire`, `id_poste`, `id_client`, `id_commentaire_parent`, `hidden`) VALUES
(1, 'behya', '2026-05-02 18:50:54', 12, 4, NULL, 0),
(2, 'meh', '2026-05-02 18:52:41', 12, 4, 1, 0),
(3, 'hehehe', '2026-05-02 18:48:23', 13, 4, NULL, 0),
(4, 'tadh7ek ?', '2026-05-02 18:52:23', 13, 4, 3, 1),
(9, 'sfvhb', '2026-05-07 11:24:45', 13, 4, NULL, 0),
(10, 'sqdvbj', '2026-05-07 11:24:53', 13, 4, 3, 0),
(11, 'dfrtj', '2026-05-07 11:36:40', 14, 4, NULL, 0),
(12, 'sdkjf', '2026-05-07 11:36:41', 14, 4, 11, 0),
(14, 'behya', '2026-05-07 14:54:51', 9, 4, NULL, 0),
(15, 'eyg', '2026-05-07 14:54:55', 9, 4, 14, 0);

-- --------------------------------------------------------

--
-- Structure de la table `contrat`
--

CREATE TABLE `contrat` (
  `id_contrat` int(11) NOT NULL,
  `numero_contrat` varchar(50) DEFAULT NULL,
  `type_contrat` varchar(50) DEFAULT NULL,
  `date_debut_contrat` date DEFAULT NULL,
  `date_fin_contrat` date DEFAULT NULL,
  `prime_contrat` decimal(10,2) DEFAULT NULL,
  `franchise_contrat` decimal(10,2) DEFAULT NULL,
  `statut_contrat` varchar(50) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  `id_categorie` int(11) DEFAULT NULL,
  `formule_contrat` varchar(100) DEFAULT NULL,
  `details_contrat` text DEFAULT NULL,
  `id_formule` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `contrat`
--

INSERT INTO `contrat` (`id_contrat`, `numero_contrat`, `type_contrat`, `date_debut_contrat`, `date_fin_contrat`, `prime_contrat`, `franchise_contrat`, `statut_contrat`, `id_user`, `id_categorie`, `formule_contrat`, `details_contrat`, `id_formule`) VALUES
(100, 'CNT-TEST-001', NULL, '2025-01-01', NULL, NULL, NULL, 'actif', 104, NULL, NULL, NULL, NULL),
(101, 'CTR-2026-251001', 'Auto', '2026-05-10', '2027-05-10', 55.00, 100.00, 'en attente', 104, 2, 'Tous risques', '{\"garanties\":[\"Vol\",\"Incendie\",\"Bris de glace\",\"Assistance\"],\"immatriculation\":\"244tun8763\",\"marque\":\"Volkswagen\",\"usage_vehicule\":\"Personnel\",\"kilometrage\":\"Plus de 60 000 km\",\"puissance\":\"6\",\"date_circulation\":\"2000-01-04\",\"valeur_venale\":\"80000\",\"financement\":\"Achat comptant\",\"identite\":\"Monsieur\",\"email\":\"sansbibi@gmail.com\",\"nom\":\"Sans\",\"prenom\":\"Bibi\",\"telephone\":\"22709307\",\"date_naissance\":\"2000-01-04\",\"nationalite\":\"Tunisienne\",\"situation_professionnelle\":\"Étudiant\",\"adresse\":\"gabes\",\"situation_matrimoniale\":\"Célibataire\",\"revenu_annuel\":\"Plus de 40 000 DT\",\"estimation_km\":\"Plus de 30 000 km\",\"conducteurs\":\"Conducteurs multiples\",\"stationnement\":\"Garage privé\",\"utilisation\":\"Déplacements quotidiens\",\"trajets_prevus\":\"Ville\",\"details_formule\":\"sghudvjfk,v\"}', 3),
(102, 'CTR-2026-250727', 'Protection', '2026-05-11', '2027-05-11', 120.00, 80.00, 'en attente', 105, 5, 'Premium plus', '{\"garanties\":[\"Assistance juridique\",\"Défense en cas de litige\",\"Assistance en cas de vol identité\",\"Garanties Sécurité\",\"Protection financière en cas de fraude\",\"Assistance en cas de vol identité\",\"Garanties Max Protection\",\"Protection juridique complète\",\"Couverture internationale\",\"Protection famille étendue\",\"Assistance VIP\"],\"type_protection\":\"Protection juridique\",\"niveau_couverture\":\"Avancé\",\"montant_couverture\":\"5555\",\"duree_contrat\":\"3 ans\",\"couvrir_famille\":\"oui\",\"identite\":\"Madame\",\"email\":\"mchabetsabrine319@gmail.com\",\"nom\":\"sabrine\",\"prenom\":\"mchabet\",\"telephone\":\"21419625\",\"date_naissance\":\"2000-01-01\",\"nationalite\":\"Tunisienne\",\"situation_professionnelle\":\"Salarié\",\"adresse\":\"tatouin\",\"situation_matrimoniale\":\"Marié(e)\",\"revenu_annuel\":\"Plus de 40 000 DT\"}', 11);

-- --------------------------------------------------------

--
-- Structure de la table `conversations`
--

CREATE TABLE `conversations` (
  `id_conversation` int(11) NOT NULL,
  `nom` varchar(200) DEFAULT NULL COMMENT 'NULL = conversation privée',
  `type` enum('prive','groupe') DEFAULT 'prive',
  `cree_par` int(11) NOT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `derniere_activite` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `conversation_participants`
--

CREATE TABLE `conversation_participants` (
  `id_conversation` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `dernier_message_lu` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `devis`
--

CREATE TABLE `devis` (
  `id_devis` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(30) NOT NULL,
  `type_assurance` enum('auto','sante','habitation') NOT NULL,
  `id_offre` int(11) DEFAULT NULL,
  `montant_estime` decimal(10,3) DEFAULT NULL,
  `statut` enum('en_attente','accepte','refuse','traite') NOT NULL DEFAULT 'en_attente',
  `reponse_admin` text DEFAULT NULL,
  `date_demande` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `devis`
--

INSERT INTO `devis` (`id_devis`, `nom`, `prenom`, `email`, `telephone`, `type_assurance`, `id_offre`, `montant_estime`, `statut`, `reponse_admin`, `date_demande`) VALUES
(10, 'sadok', 'berrzouga', 'sadokberrzouga123@gmail.com', '29527030', 'auto', 1, NULL, 'refuse', '', '2026-04-29 19:20:39'),
(12, 'ben salah', 'ali', 'sadokberrzouga123@gmail.com', '12345678', 'habitation', 4, NULL, 'accepte', '', '2026-04-29 19:37:45'),
(13, 'sami', 'ben ali', 'samibenali@gmail.com', '58236914', 'auto', 1, NULL, 'en_attente', NULL, '2026-04-29 21:02:40'),
(14, 'sami', 'mabrouki', 'samimabrouki@gg.com', '58984568', 'auto', 1, 1500.000, 'accepte', '', '2026-04-29 21:28:36'),
(15, 'devies', 'validation', 'sevivalidation@gmail.com', '28295270', 'auto', 5, NULL, 'accepte', '', '2026-04-30 11:23:23'),
(16, 'test', 'mail', 'sadokberrzouga123@gmail.com', '28295270', 'auto', 5, 0.002, 'accepte', 'le montant estimer est tres bas', '2026-05-05 11:49:56'),
(17, 'test', 'mail', 'sadokberrzouga580@gmail.com', '28295270', 'auto', 5, NULL, 'refuse', '', '2026-05-05 15:33:02');

-- --------------------------------------------------------

--
-- Structure de la table `devis_auto`
--

CREATE TABLE `devis_auto` (
  `id_auto` int(11) NOT NULL,
  `id_devis` int(11) NOT NULL,
  `marque` varchar(100) NOT NULL,
  `modele` varchar(100) NOT NULL,
  `annee` int(11) DEFAULT NULL,
  `carburant` varchar(50) DEFAULT NULL,
  `puissance` int(11) DEFAULT NULL,
  `immatriculation` varchar(50) DEFAULT NULL,
  `usage_vehicule` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `devis_auto`
--

INSERT INTO `devis_auto` (`id_auto`, `id_devis`, `marque`, `modele`, `annee`, `carburant`, `puissance`, `immatriculation`, `usage_vehicule`) VALUES
(1, 10, 'audi', 'rs7', 2026, 'Essence', 48, '123TUN255', 'Personnel'),
(2, 13, 'porche', 'panamera', 2002, 'Diesel', 9, '123tun255', 'Mixte'),
(3, 14, 'porche', '2008', 2018, 'Essence', 5, '154TUN222', 'Personnel'),
(4, 15, 'audi', 'rs3', 2008, 'Essence', 7, '552TUN255', 'Personnel'),
(5, 16, 'audi', 'rs3', 2008, 'Essence', 7, '552TUN255', 'Personnel'),
(6, 17, 'audi', 'rs3', 2008, 'Essence', 7, '552TUN255', 'Personnel');

-- --------------------------------------------------------

--
-- Structure de la table `devis_habitation`
--

CREATE TABLE `devis_habitation` (
  `id_habitation` int(11) NOT NULL,
  `id_devis` int(11) NOT NULL,
  `type_logement` varchar(100) NOT NULL,
  `surface` int(11) DEFAULT NULL,
  `valeur_bien` decimal(12,3) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `proprietaire_locataire` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `devis_habitation`
--

INSERT INTO `devis_habitation` (`id_habitation`, `id_devis`, `type_logement`, `surface`, `valeur_bien`, `ville`, `adresse`, `proprietaire_locataire`) VALUES
(1, 12, 'Appartement', 123, 1200.000, '', 'sfax', 'Propriétaire');

-- --------------------------------------------------------

--
-- Structure de la table `devis_sante`
--

CREATE TABLE `devis_sante` (
  `id_sante` int(11) NOT NULL,
  `id_devis` int(11) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `situation_familiale` varchar(100) DEFAULT NULL,
  `nombre_enfants` int(11) DEFAULT 0,
  `maladies` text DEFAULT NULL,
  `couverture_souhaitee` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `formule`
--

CREATE TABLE `formule` (
  `id_formule` int(11) NOT NULL,
  `nom_formule` varchar(100) NOT NULL,
  `description_formule` text DEFAULT NULL,
  `prix_formule` decimal(10,2) DEFAULT 0.00,
  `franchise_formule` decimal(10,2) NOT NULL DEFAULT 0.00,
  `id_categorie` int(11) NOT NULL,
  `niveau_formule` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `formule`
--

INSERT INTO `formule` (`id_formule`, `nom_formule`, `description_formule`, `prix_formule`, `franchise_formule`, `id_categorie`, `niveau_formule`) VALUES
(1, 'Classique', 'Formule auto de base', 30.00, 300.00, 2, 'Essentiel'),
(2, 'Tierce collision', 'Formule auto intermédiaire', 40.00, 200.00, 2, 'Intermédiaire'),
(3, 'Tous risques', 'Formule auto complète', 55.00, 100.00, 2, 'Premium'),
(4, 'VIVAPRO Economique', 'Couverture logement de base', 35.00, 220.00, 3, 'Essentiel'),
(5, 'VIVAPRO Privilège', 'Couverture intermédiaire habitation', 55.00, 100.00, 3, 'Premium'),
(6, 'Économique', 'Formule santé basique', 40.00, 200.00, 4, 'Essentiel'),
(7, 'Confort', 'Formule santé intermédiaire', 110.00, 120.00, 4, 'Intermédiaire'),
(8, 'Premium', 'Formule santé complète', 180.00, 50.00, 4, 'Premium'),
(9, 'Sécurité', 'Protection de base', 35.00, 250.00, 5, 'Essentiel'),
(10, 'Max Protection', 'Protection avancée', 70.00, 150.00, 5, 'Intermédiaire'),
(11, 'Premium plus', 'Protection Premium', 120.00, 80.00, 5, 'Premium');

-- --------------------------------------------------------

--
-- Structure de la table `formule_garantie`
--

CREATE TABLE `formule_garantie` (
  `id_formule` int(11) NOT NULL,
  `id_garantie` int(11) NOT NULL,
  `niveau_couvert_garantie` enum('basique','option','non disponible') NOT NULL DEFAULT 'basique'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `formule_garantie`
--

INSERT INTO `formule_garantie` (`id_formule`, `id_garantie`, `niveau_couvert_garantie`) VALUES
(1, 92, 'basique'),
(1, 93, 'basique'),
(1, 94, 'option'),
(1, 95, 'option'),
(1, 97, 'option'),
(1, 98, 'option'),
(1, 99, 'non disponible'),
(1, 100, 'non disponible'),
(2, 92, 'basique'),
(2, 93, 'basique'),
(2, 94, 'option'),
(2, 95, 'option'),
(2, 97, 'option'),
(2, 98, 'option'),
(2, 99, 'basique'),
(2, 100, 'non disponible'),
(3, 92, 'basique'),
(3, 93, 'basique'),
(3, 94, 'option'),
(3, 95, 'option'),
(3, 97, 'option'),
(3, 98, 'option'),
(3, 99, 'non disponible'),
(3, 100, 'basique'),
(4, 101, 'basique'),
(4, 102, 'basique'),
(4, 103, 'basique'),
(4, 104, 'basique'),
(4, 105, 'option'),
(4, 106, 'option'),
(4, 107, 'non disponible'),
(4, 108, 'option'),
(4, 110, 'non disponible'),
(4, 112, 'option'),
(4, 113, 'non disponible'),
(4, 114, 'option'),
(5, 106, 'basique'),
(5, 107, 'basique'),
(5, 108, 'basique'),
(5, 109, 'basique'),
(5, 110, 'basique'),
(5, 111, 'basique'),
(5, 112, 'basique'),
(5, 113, 'basique'),
(5, 114, 'option'),
(5, 115, 'option'),
(5, 116, 'option'),
(6, 34, 'basique'),
(6, 35, 'basique'),
(6, 36, 'basique'),
(6, 37, 'non disponible'),
(6, 38, 'non disponible'),
(6, 50, 'non disponible'),
(6, 51, 'non disponible'),
(6, 91, 'basique'),
(7, 37, 'option'),
(7, 38, 'option'),
(7, 50, 'option'),
(7, 51, 'non disponible'),
(7, 52, 'basique'),
(7, 53, 'basique'),
(7, 54, 'basique'),
(7, 62, 'basique'),
(8, 50, 'basique'),
(8, 51, 'option'),
(8, 55, 'basique'),
(8, 56, 'basique'),
(8, 57, 'basique'),
(8, 58, 'basique'),
(9, 117, 'basique'),
(9, 119, 'basique'),
(9, 120, 'option'),
(9, 121, 'option'),
(9, 124, 'basique'),
(10, 122, 'basique'),
(10, 123, 'basique'),
(10, 124, 'basique'),
(10, 125, 'option'),
(10, 126, 'option'),
(11, 127, 'basique'),
(11, 128, 'basique'),
(11, 129, 'basique'),
(11, 130, 'option'),
(11, 131, 'option');

-- --------------------------------------------------------

--
-- Structure de la table `fraud_analysis`
--

CREATE TABLE `fraud_analysis` (
  `id_fraud` int(11) NOT NULL,
  `id_sinistre` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `score_global` tinyint(3) NOT NULL DEFAULT 0 COMMENT '0-100, plus haut = plus suspect',
  `niveau_risque` enum('faible','normal','fraude') DEFAULT 'faible',
  `suggestion_ia` enum('accepter','investiguer','refuser') DEFAULT 'accepter',
  `score_texte` tinyint(3) DEFAULT 0 COMMENT 'Analyse sémantique description',
  `score_comportement` tinyint(3) DEFAULT 0 COMMENT 'Historique sinistres utilisateur',
  `score_contrat` tinyint(3) DEFAULT 0 COMMENT 'Ancienneté contrat / franchise',
  `score_image` tinyint(3) DEFAULT 0 COMMENT 'Analyse photo (si disponible)',
  `flag_description_vague` tinyint(1) DEFAULT 0,
  `flag_sinistres_multiples` tinyint(1) DEFAULT 0,
  `flag_contrat_recent` tinyint(1) DEFAULT 0,
  `flag_montant_eleve` tinyint(1) DEFAULT 0,
  `flag_image_suspecte` tinyint(1) DEFAULT 0,
  `analyse_texte` text DEFAULT NULL COMMENT 'Explication IA de l analyse textuelle',
  `analyse_comportement` text DEFAULT NULL,
  `analyse_image` text DEFAULT NULL,
  `recommandation_ia` text DEFAULT NULL COMMENT 'Recommandation complète pour l agent',
  `date_analyse` datetime NOT NULL DEFAULT current_timestamp(),
  `version_modele` varchar(50) DEFAULT 'claude-sonnet-4-20250514'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Résultats d analyse antifraud IA par sinistre';

--
-- Déchargement des données de la table `fraud_analysis`
--

INSERT INTO `fraud_analysis` (`id_fraud`, `id_sinistre`, `id_user`, `score_global`, `niveau_risque`, `suggestion_ia`, `score_texte`, `score_comportement`, `score_contrat`, `score_image`, `flag_description_vague`, `flag_sinistres_multiples`, `flag_contrat_recent`, `flag_montant_eleve`, `flag_image_suspecte`, `analyse_texte`, `analyse_comportement`, `analyse_image`, `recommandation_ia`, `date_analyse`, `version_modele`) VALUES
(166, 184, 24, 80, 'fraude', 'refuser', 100, 0, 50, 0, 1, 0, 1, 0, 0, 'Risque textuel élevé (score: 100/100). Critère dominant : manque de détails concrets. Détails : Aucune indication temporelle. Peu de mots-clés cohérents avec le type \"Accident auto\". CRITIQUE : Date incohérente (dans le futur : 20/05/2026).', '1 sinistre(s) en 90 jours (normal).', '', '⚠️ FRAUDE DÉTECTÉE (score: 80/100) — Ce sinistre présente plusieurs signaux d\'alerte sérieux : description vague ou incomplète, incohérence entre le type et le contenu déclaré, contrat très récent au moment de la déclaration. Ne pas traiter ce dossier avant une investigation approfondie. Actions requises avant tout traitement : demander une description plus détaillée (lieu exact, heure, circonstances précises); vérifier la date de souscription du contrat et l\'intention d\'assurance; demander le rapport de police ou constat amiable signé. Si les justificatifs ne sont pas fournis dans les 5 jours ouvrables, recommander le refus.', '2026-05-10 20:43:20', 'claude-sonnet-4-20250514'),
(168, 201, 104, 100, 'fraude', 'refuser', 100, 25, 50, 0, 1, 0, 1, 0, 0, 'Risque textuel élevé (score: 100/100). Critère dominant : manque de détails concrets. Détails : Description très succincte. Description composée d\'un seul mot (très suspect). Aucune indication temporelle.', '2 sinistres déclarés en 90 jours (élevé).', NULL, '⚠️ FRAUDE DÉTECTÉE (score: 100/100) — Ce sinistre présente plusieurs signaux d\'alerte sérieux : description vague ou incomplète, incohérence entre le type et le contenu déclaré, contrat très récent au moment de la déclaration. Ne pas traiter ce dossier avant une investigation approfondie. Actions requises avant tout traitement : demander une description plus détaillée (lieu exact, heure, circonstances précises); vérifier la date de souscription du contrat et l\'intention d\'assurance; demander le rapport de police ou constat amiable signé. Si les justificatifs ne sont pas fournis dans les 5 jours ouvrables, recommander le refus. [ALERTE: Aucune photo fournie - vigilance accrue recommandée]', '2026-05-10 22:19:34', 'claude-sonnet-4-20250514');

-- --------------------------------------------------------

--
-- Structure de la table `friendships`
--

CREATE TABLE `friendships` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('pending','accepted','refused') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_trusted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `garantie`
--

CREATE TABLE `garantie` (
  `id_garantie` int(11) NOT NULL,
  `nom_garantie` varchar(100) DEFAULT NULL,
  `description_garantie` text DEFAULT NULL,
  `plafond_couvert_garantie` decimal(10,2) DEFAULT NULL,
  `id_categorie` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `garantie`
--

INSERT INTO `garantie` (`id_garantie`, `nom_garantie`, `description_garantie`, `plafond_couvert_garantie`, `id_categorie`) VALUES
(34, 'Hospitalisation partielle', 'Hospitalisation partielle', 2000.00, 4),
(35, 'Médicaments essentielles', 'Médicaments essentielles', 400.00, 4),
(36, 'Urgences médicales', 'Urgences médicales', 1500.00, 4),
(37, 'Dentaire', 'Dentaire', 800.00, 4),
(38, 'Optique', 'Optique', 600.00, 4),
(50, 'Maternité', 'Maternité', 3000.00, 4),
(51, 'Couverture Internationale', 'Couverture Internationale', 10000.00, 4),
(52, 'Consultation + spécialistes', 'Consultation + spécialistes', 1000.00, 4),
(53, 'Analyse et examens', 'Analyse et examens', 1200.00, 4),
(54, 'Médicaments avancés', 'Médicaments avancés', 1000.00, 4),
(55, 'Tout de Confort', 'Tout de Confort', 3000.00, 4),
(56, 'Dentaire complet', 'Dentaire complet', 2000.00, 4),
(57, 'Optique Complet', 'Optique Complet', 1200.00, 4),
(58, 'Assistance VIP', 'Assistance VIP', 5000.00, 4),
(62, 'Hospitalisation Complète', 'Hospitalisation Complète', 6000.00, 4),
(91, 'Consultation médicale de base', 'Consultation médicale de base', 300.00, 4),
(92, 'Responsabilité civile', 'Responsabilité civile', 20000.00, 2),
(93, 'Défense et recours', 'Défense et recours', 3000.00, 2),
(94, 'Vol', 'Vol', 8000.00, 2),
(95, 'Incendie', 'Incendie', 7000.00, 2),
(97, 'Bris de glace', 'Bris de glace', 1200.00, 2),
(98, 'Assistance', 'Assistance', 500.00, 2),
(99, 'Tierce collision', 'Tierce collision', 6000.00, 2),
(100, 'Dommages aux véhicules', 'Dommages aux véhicules', 15000.00, 2),
(101, 'Incendie / Explosion', 'Incendie / Explosion', 15000.00, 3),
(102, 'Dégâts des eaux', 'Dégâts des eaux', 8000.00, 3),
(103, 'Défense et recours', 'Défense et recours', 3000.00, 3),
(104, 'Assistance domiciliaire', 'Assistance domiciliaire', 1000.00, 3),
(105, 'Tempêtes / cyclones', 'Tempêtes / cyclones', 12000.00, 3),
(106, 'Accidents corporels famille', 'Accidents corporels famille', 5000.00, 3),
(107, 'Hospitalisation', 'Hospitalisation', 4000.00, 3),
(108, 'Premières urgences après sinistre', 'Premières urgences après sinistre', 1500.00, 3),
(109, 'Garanties du pack Economique', 'Garanties du pack Economique', 5000.00, 3),
(110, 'Valeur à neuf bâtiment', 'Valeur à neuf bâtiment', 20000.00, 3),
(111, 'Dommages électriques / électroménagers', 'Dommages électriques / électroménagers', 3000.00, 3),
(112, 'Bris de glaces / miroirs fixes', 'Bris de glaces / miroirs fixes', 2000.00, 3),
(113, 'Sinistre rendant le domicile inhabitable', 'Sinistre rendant le domicile inhabitable', 7000.00, 3),
(114, 'Inondations', 'Inondations', 12000.00, 3),
(115, 'Frais funéraires', 'Frais funéraires', 3000.00, 3),
(116, 'Gens de maison', 'Gens de maison', 4000.00, 3),
(117, 'Assistance juridique', 'Assistance juridique', 1500.00, 5),
(118, 'Protection des données personnelles', 'Protection des données personnelles', 12000.00, 5),
(119, 'Défense en cas de litige', 'Défense en cas de litige', 3000.00, 5),
(120, 'Surveillance identité numérique', 'Surveillance identité numérique', 2000.00, 5),
(121, 'Assistance cyber', 'Assistance cyber', 2500.00, 5),
(122, 'Garanties Sécurité', 'Garanties Sécurité', 5000.00, 5),
(123, 'Protection financière en cas de fraude', 'Protection financière en cas de fraude', 8000.00, 5),
(124, 'Assistance en cas de vol identité', 'Assistance en cas de vol identité', 3500.00, 5),
(125, 'Protection achat en ligne', 'Protection achat en ligne', 2000.00, 5),
(126, 'Assistance voyage', 'Assistance voyage', 4000.00, 5),
(127, 'Garanties Max Protection', 'Garanties Max Protection', 10000.00, 5),
(128, 'Protection juridique complète', 'Protection juridique complète', 12000.00, 5),
(129, 'Couverture internationale', 'Couverture internationale', 15000.00, 5),
(130, 'Protection famille étendue', 'Protection famille étendue', 8000.00, 5),
(131, 'Assistance VIP', 'Assistance VIP', 20000.00, 5);

-- --------------------------------------------------------

--
-- Structure de la table `like_post`
--

CREATE TABLE `like_post` (
  `id_like` int(11) NOT NULL,
  `id_poste` int(11) NOT NULL,
  `id_client` int(11) NOT NULL,
  `date_like` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `like_post`
--

INSERT INTO `like_post` (`id_like`, `id_poste`, `id_client`, `date_like`) VALUES
(1, 10, 4, '2026-05-07 11:16:56'),
(2, 12, 4, '2026-05-07 11:16:56'),
(3, 13, 4, '2026-05-07 11:16:56'),
(7, 9, 4, '2026-05-07 11:24:38'),
(9, 14, 4, '2026-05-07 11:36:28'),
(11, 8, 4, '2026-05-07 13:01:41');

-- --------------------------------------------------------

--
-- Structure de la table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `statut` enum('success','failed') DEFAULT 'failed',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `login_attempts`
--

INSERT INTO `login_attempts` (`id`, `email`, `ip`, `user_agent`, `statut`, `created_at`) VALUES
(1, 'medkarimmiledi123@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-05 12:45:04'),
(2, 'medkarimmiledi@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-05 13:08:03'),
(3, 'medkarimmiledi@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-05 13:32:36'),
(6, 'sansbibi@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-05 13:48:34'),
(7, 'sansbibi@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-05 14:01:54'),
(8, 'admin@protex.tn', '0.0.0.0', '', 'failed', '2026-05-05 18:29:12'),
(9, 'client.tunis@email.com', '0.0.0.0', '', 'failed', '2026-05-05 18:29:12'),
(10, 'Medkarimmiledi@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-05 20:17:27'),
(11, 'Medkarimmiledi@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-06 16:43:59'),
(15, 'Medkarimmiledi@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-06 17:09:16'),
(16, 'Medkarimmiledi@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-06 17:12:38'),
(17, 'Medkarimmiledi123@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-06 17:13:36'),
(22, 'medkarimmiledi@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-06 18:41:42'),
(24, 'muledikarim@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-06 18:50:17'),
(26, 'medkarimmiledi123@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-06 23:45:19'),
(27, 'medkarimmiledi@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-06 23:53:20'),
(28, 'medkarimmiledi123@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-06 23:58:46'),
(29, 'medkarimmiledi@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-07 00:00:11'),
(30, 'muledikarim@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-07 00:05:42'),
(31, 'medkarimmiledi123@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-07 09:34:37'),
(32, 'muledikarim@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-07 09:42:54'),
(36, 'mchabetsabrine319@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-07 09:55:24'),
(37, 'bouazizimariem82@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success', '2026-05-09 18:23:38'),
(38, 'bouazizimariem82@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success', '2026-05-09 20:53:11'),
(39, 'bouazizimariem82@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success', '2026-05-10 00:36:16'),
(40, 'bouazizimariem82@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success', '2026-05-10 00:46:38'),
(41, 'mariembouazizi25@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success', '2026-05-10 00:51:47'),
(42, 'bouazizimariem82@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success', '2026-05-10 01:26:32'),
(43, 'bouazizimariem82@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success', '2026-05-10 04:17:13'),
(45, 'mariembouazizi25@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success', '2026-05-10 04:17:47'),
(46, 'bouazizimariem82@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'success', '2026-05-10 04:25:15'),
(47, 'medkarimmiledi@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0', 'success', '2026-05-10 15:08:35'),
(51, 'nexusvault99@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0', 'success', '2026-05-10 16:18:00'),
(52, 'medkarimmiledi@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0', 'success', '2026-05-10 18:20:16'),
(56, 'nexusvault99@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0', 'success', '2026-05-10 19:53:32'),
(58, 'muledikarim@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'success', '2026-05-10 19:56:46'),
(59, 'nexusvault99@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0', 'success', '2026-05-10 21:03:52'),
(63, 'sansbibi@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0', 'success', '2026-05-10 21:21:27'),
(64, 'muledikarim@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'success', '2026-05-10 21:23:38'),
(65, 'medkarimmiledi123@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'success', '2026-05-10 21:53:09'),
(66, 'medkarimmiledi123@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36 Edg/148.0.0.0', 'success', '2026-05-10 22:03:10'),
(67, 'sansbibi@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/146.0.0.0 Safari/537.36 OPR/130.0.0.0', 'success', '2026-05-10 22:09:12'),
(68, 'mchabetsabrine319@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-11 21:23:22'),
(69, 'medkarimmiledi@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-11 22:48:28'),
(70, 'mchabetsabrine319@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-11 23:26:45'),
(71, 'medkarimmiledi123@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-11 23:46:21'),
(72, 'mchabetsabrine319@gmail.com', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36', 'success', '2026-05-12 00:51:56');

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `receiver_id`, `content`, `created_at`, `is_read`) VALUES
(1, 13, 14, 'cc', '2026-05-06 16:19:00', 1),
(2, 14, 13, 'cv', '2026-05-06 16:19:06', 1),
(3, 24, 25, 'ccc', '2026-05-07 08:55:48', 1),
(4, 25, 24, 'cccc', '2026-05-07 08:55:56', 1),
(5, 24, 25, 'cccccc', '2026-05-07 09:16:12', 1);

-- --------------------------------------------------------

--
-- Structure de la table `messages_admin`
--

CREATE TABLE `messages_admin` (
  `id_message` int(11) NOT NULL,
  `id_conversation` int(11) NOT NULL,
  `id_expediteur` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `type_message` enum('texte','mention','fichier','systeme') DEFAULT 'texte',
  `est_lu` tinyint(1) DEFAULT 0,
  `date_envoi` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `message_mentions`
--

CREATE TABLE `message_mentions` (
  `id_mention` int(11) NOT NULL,
  `id_message` int(11) NOT NULL,
  `id_user_mentionne` int(11) NOT NULL,
  `est_resolu` tinyint(1) DEFAULT 0 COMMENT '0 = appel en cours, 1 = traité',
  `date_mention` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notification`
--

CREATE TABLE `notification` (
  `id_notification` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'info',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `notification`
--

INSERT INTO `notification` (`id_notification`, `id_user`, `message`, `type`, `created_at`, `is_read`) VALUES
(1, 4, 'Nous vous remercions pour votre avis. Votre retour est essentiel pour l\'amélioration continue de la qualité de nos services.', 'thanks', '2026-05-07 12:57:00', 1),
(2, 4, 'Votre avis a été masqué conformément à notre politique de modération en raison de termes non conformes. Nous vous remercions de votre compréhension et vous invitons à soumettre un nouvel avis respectant nos directives.', 'hidden', '2026-05-07 12:57:21', 1),
(5, 4, 'Votre avis a été masqué conformément à notre politique de modération en raison de termes non conformes. Nous vous remercions de votre compréhension et vous invitons à soumettre un nouvel avis respectant nos directives.', 'hidden', '2026-05-07 12:59:08', 1),
(6, 4, 'Nous vous remercions pour votre avis. Votre retour est essentiel pour l\'amélioration continue de la qualité de nos services.', 'thanks', '2026-05-07 14:54:04', 1),
(7, 4, 'Votre avis a été masqué conformément à notre politique de modération en raison de termes non conformes. Nous vous remercions de votre compréhension et vous invitons à soumettre un nouvel avis respectant nos directives.', 'hidden', '2026-05-07 14:54:28', 1),
(8, 4, 'Nous vous remercions pour votre commentaire. Votre retour est précieux pour améliorer nos services.', 'thanks', '2026-05-07 14:54:51', 1),
(9, 4, 'Nous vous remercions pour votre réponse. Votre participation contribue à enrichir nos échanges.', 'thanks', '2026-05-07 14:54:55', 1),
(10, 4, 'Votre avis a été masqué conformément à notre politique de modération en raison de termes non conformes. Nous vous remercions de votre compréhension et vous invitons à soumettre un nouvel avis respectant nos directives.', 'hidden', '2026-05-07 17:58:59', 1),
(11, 4, 'Votre commentaire a été masqué conformément à notre politique de modération en raison de termes non conformes. Nous vous invitons à reformuler votre message dans le respect de nos directives.', 'hidden', '2026-05-07 18:36:07', 1);

-- --------------------------------------------------------

--
-- Structure de la table `offre`
--

CREATE TABLE `offre` (
  `id_offre` int(11) NOT NULL,
  `nom_offre` varchar(100) NOT NULL,
  `type_offre` enum('auto','sante','habitation','vie') NOT NULL,
  `description` text DEFAULT NULL,
  `prix_mensuel` decimal(10,3) NOT NULL,
  `prix_annuel` decimal(10,3) NOT NULL,
  `couverture` text DEFAULT NULL,
  `plafond` decimal(10,3) DEFAULT NULL,
  `duree_min` int(11) DEFAULT 1,
  `statut` enum('active','suspendue','archivee') DEFAULT 'active',
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_modification` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `offre`
--

INSERT INTO `offre` (`id_offre`, `nom_offre`, `type_offre`, `description`, `prix_mensuel`, `prix_annuel`, `couverture`, `plafond`, `duree_min`, `statut`, `date_creation`, `date_modification`) VALUES
(1, 'Auto Premium Plus', 'auto', '555', 186.000, 15963.000, 'a,k,k,l', 123456.000, 1, 'active', '2026-04-22 18:42:26', '2026-04-22 18:42:26'),
(2, 'auto sado', 'sante', 'azertyuiop', 123.000, 12345.000, '^kk,kk,nn,ll,mm', 153220.000, 1, 'active', '2026-04-22 18:43:37', '2026-04-25 22:26:32'),
(4, 'offre de sadok', 'habitation', 'haja heyla barcha', 66.000, 600.000, 'aze,\r\nrty,\r\nuio,', 123455.999, 1, 'suspendue', '2026-04-29 19:32:55', '2026-05-05 15:53:52'),
(5, 'auto validation', 'auto', 'azertyyuio', 123.000, 1234.000, 'aa,\r\nzz,\r\nrr,', 153220.000, 1, 'archivee', '2026-04-30 11:19:28', '2026-05-05 15:53:44');

-- --------------------------------------------------------

--
-- Structure de la table `otp_codes`
--

CREATE TABLE `otp_codes` (
  `id` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `otp_codes`
--

INSERT INTO `otp_codes` (`id`, `id_user`, `code`, `expires_at`, `used`, `created_at`) VALUES
(50, 104, '580195', '2026-05-10 23:13:47', 1, '2026-05-10 22:08:47'),
(52, 101, '105490', '2026-05-11 23:53:05', 1, '2026-05-11 22:48:05'),
(54, 102, '875084', '2026-05-12 00:51:08', 1, '2026-05-11 23:46:08'),
(57, 105, '003193', '2026-05-12 01:55:54', 1, '2026-05-12 00:50:54');

-- --------------------------------------------------------

--
-- Structure de la table `paiement`
--

CREATE TABLE `paiement` (
  `id_paiement` int(11) NOT NULL,
  `id_offre` int(11) NOT NULL,
  `id_devis` int(11) DEFAULT NULL,
  `reference` varchar(20) NOT NULL,
  `montant` decimal(10,3) NOT NULL,
  `methode` enum('carte','virement','mobile') NOT NULL,
  `periodicite` enum('mensuel','annuel') NOT NULL,
  `statut` enum('en_attente','valide','refuse','rembourse') DEFAULT 'en_attente',
  `date_paiement` datetime DEFAULT current_timestamp(),
  `date_echeance` date DEFAULT NULL,
  `num_carte_masque` varchar(25) DEFAULT NULL,
  `recu_pdf` varchar(255) DEFAULT NULL,
  `motif_refus` text DEFAULT NULL,
  `code_promo` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires_at`, `used`, `created_at`) VALUES
(2, 'mchabetsabrine319@gmail.com', 'c93f35d9cde9b188c17d0a068364183b561503b1be9a84328f5d6d8351ff4ab5', '2026-05-07 11:05:41', 0, '2026-05-07 08:50:41'),
(4, 'sansbibi@gmail.com', '786e5e6cdc91af1017621223d707aa8dc5aff14bf82d502db059afecc2640ce8', '2026-05-10 20:53:18', 0, '2026-05-10 18:38:18');

-- --------------------------------------------------------

--
-- Structure de la table `poste`
--

CREATE TABLE `poste` (
  `id_poste` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `date_publication` date DEFAULT NULL,
  `note` int(11) DEFAULT NULL CHECK (`note` between 1 and 5),
  `auteur` varchar(100) DEFAULT NULL,
  `nb_likes` int(11) DEFAULT 0,
  `nb_commentaires` int(11) DEFAULT 0,
  `id_agence` int(11) DEFAULT NULL,
  `commentaires_json` longtext DEFAULT NULL,
  `likes_json` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `poste`
--

INSERT INTO `poste` (`id_poste`, `contenu`, `date_publication`, `note`, `auteur`, `nb_likes`, `nb_commentaires`, `id_agence`, `commentaires_json`, `likes_json`) VALUES
(8, '....', '2026-04-30', NULL, 'sadok', 1, 0, 1, '[]', '[]'),
(9, 'ken fama haja khayba koulouli', '2026-04-30', NULL, 'abdou', 1, 2, 1, '[]', '[]'),
(10, 'ay haja', '2026-04-30', NULL, 'karim', 1, 0, 2, '[]', '[4]'),
(12, 'chkawlkom ?', '2026-04-30', NULL, 'l abdou lellah', 1, 2, 3, '[{\"id_client\":4,\"contenu\":\"behya\",\"auteur\":\"Anonyme\",\"id_commentaire\":\"1777744254_7654\",\"date_commentaire\":\"2026-05-02 19:50:54\",\"reponses\":[{\"id_client\":4,\"contenu\":\"meh\",\"auteur\":\"Anonyme\",\"id_commentaire\":\"1777744361_5790\",\"date_commentaire\":\"2026-05-02 19:52:41\",\"hidden\":false}],\"hidden\":true}]', '[4]'),
(13, 'ay haja', '2026-04-30', NULL, 'admin', 1, 4, 9, '[{\"id_client\":4,\"contenu\":\"hehehe\",\"auteur\":\"Anonyme\",\"id_commentaire\":\"1777744103_1624\",\"date_commentaire\":\"2026-05-02 19:48:23\",\"reponses\":[{\"id_client\":4,\"contenu\":\"tadh7ek ?\",\"auteur\":\"Anonyme\",\"id_commentaire\":\"1777744343_9763\",\"date_commentaire\":\"2026-05-02 19:52:23\"}]}]', '[4]'),
(14, 'qdnc', '2026-05-07', NULL, 'qdjc', 1, 2, 2, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `reclamation`
--

CREATE TABLE `reclamation` (
  `id` int(11) NOT NULL,
  `objet` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `ref_contrat` varchar(100) DEFAULT NULL,
  `priorite` varchar(50) DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL,
  `date_depot` date DEFAULT NULL,
  `rec_ref` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `email` varchar(150) NOT NULL DEFAULT '',
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `reclamation`
--

INSERT INTO `reclamation` (`id`, `objet`, `type`, `ref_contrat`, `priorite`, `statut`, `date_depot`, `rec_ref`, `description`, `email`, `id_user`) VALUES
(37, ',xc;lvl;', 'Santé', 'CTR-2026-250727', 'Normale', 'closed', '2026-05-12', 'REC-20260512005256', 'dfsfsfdsfdfdsfdsfsdfdsfdsf', 'mchabetsabrine319@gmail.com', 105),
(38, 'dwff', 'Santé', 'CTR-2026-250727', 'Normale', 'open', '2026-05-12', 'REC-20260512015621', 'sdfdfdsfdsf', 'mchabetsabrine319@gmail.com', 105),
(39, 'dffdf', 'Santé', 'CTR-2026-250727', 'Normale', 'open', '2026-05-12', 'REC-20260512020033', 'dfsfdsfdsfdsf', 'mchabetsabrine319@gmail.com', 105),
(40, 'dfsfds', 'Santé', 'CTR-2026-250727', 'Normale', 'open', '2026-05-12', 'REC-20260512020254', 'sfdfdsfsfdsfdsf', 'mchabetsabrine319@gmail.com', 105),
(41, 'kdsfndsfknds', 'Santé', 'CTR-2026-250727', 'Normale', 'open', '2026-05-12', 'REC-20260512020306', 'dsffsdfdsf', 'mchabetsabrine319@gmail.com', 105);

-- --------------------------------------------------------

--
-- Structure de la table `recommandation_historique`
--

CREATE TABLE `recommandation_historique` (
  `id_recommandation` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `besoin` varchar(100) DEFAULT NULL,
  `budget` varchar(50) DEFAULT NULL,
  `profil_risque` varchar(50) DEFAULT NULL,
  `id_formule_recommandee` int(11) DEFAULT NULL,
  `date_recommandation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `reponse`
--

CREATE TABLE `reponse` (
  `id_re` int(11) NOT NULL,
  `date_reponse` date DEFAULT NULL,
  `contenu` text NOT NULL,
  `statut` varchar(50) DEFAULT NULL,
  `reclamation_id` int(11) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `reponse`
--

INSERT INTO `reponse` (`id_re`, `date_reponse`, `contenu`, `statut`, `reclamation_id`, `id_user`) VALUES
(33, '2026-05-12', 'qcdscdscsdc', 'envoyee', 37, 102);

-- --------------------------------------------------------

--
-- Structure de la table `reponse_history`
--

CREATE TABLE `reponse_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `reponse_id` int(10) UNSIGNED NOT NULL,
  `ancien_contenu` text NOT NULL,
  `modifie_par` int(10) UNSIGNED DEFAULT NULL,
  `modifie_le` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `roulette_gains`
--

CREATE TABLE `roulette_gains` (
  `id_gain` int(11) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(100) DEFAULT NULL,
  `paiements` int(11) DEFAULT NULL,
  `cadeau_label` varchar(200) DEFAULT NULL,
  `cadeau_icone` varchar(50) DEFAULT NULL,
  `code_promo` varchar(50) DEFAULT NULL,
  `valeur_reduction` decimal(10,3) DEFAULT NULL,
  `type_recompense` varchar(50) DEFAULT NULL,
  `date_jeu` datetime DEFAULT current_timestamp(),
  `utilise` tinyint(1) DEFAULT 0,
  `date_utilisation` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `roulette_gains`
--

INSERT INTO `roulette_gains` (`id_gain`, `email`, `nom`, `prenom`, `paiements`, `cadeau_label`, `cadeau_icone`, `code_promo`, `valeur_reduction`, `type_recompense`, `date_jeu`, `utilise`, `date_utilisation`) VALUES
(1, 'sadokberrzouga123@gmail.com', 'test', 'mail', 4, '-10% sur votre prochaine cotisation', '🏷️', 'PROTEX-SPIN-F8387C', 10.000, 'reduction_pct', '2026-05-05 15:23:03', 0, NULL),
(2, 'sadokberrzouga580@gmail.com', 'test', 'mail', 4, '-10% sur votre prochaine cotisation', '🏷️', 'PROTEX-SPIN-46EE3F', 10.000, 'reduction_pct', '2026-05-05 15:39:48', 0, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `roulette_jeu`
--

CREATE TABLE `roulette_jeu` (
  `id` int(11) NOT NULL,
  `email_client` varchar(150) NOT NULL,
  `palier` enum('bronze','argent','or') NOT NULL,
  `cadeau_label` varchar(100) DEFAULT NULL,
  `code_promo` varchar(30) DEFAULT NULL,
  `valeur_reduction` decimal(10,3) DEFAULT NULL,
  `date_jeu` datetime DEFAULT current_timestamp(),
  `utilise` tinyint(1) DEFAULT 0,
  `date_utilisation` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sinistre`
--

CREATE TABLE `sinistre` (
  `id_sinistre` int(11) NOT NULL,
  `id_contrat` int(11) DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `type` enum('Accident auto','Incendie','Vol','Degat des eaux','Dégât des eaux') NOT NULL,
  `description` text DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `date_declaration` date NOT NULL,
  `statut` enum('en_attente','rembourse','refuse') DEFAULT 'en_attente',
  `is_read` tinyint(1) DEFAULT 0,
  `id_agence` int(11) DEFAULT NULL,
  `id_agent_assigne` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sinistre`
--

INSERT INTO `sinistre` (`id_sinistre`, `id_contrat`, `id_user`, `type`, `description`, `photo_url`, `date_declaration`, `statut`, `is_read`, `id_agence`, `id_agent_assigne`) VALUES
(184, NULL, 24, 'Accident auto', 'le 20/05/2026 j\'étais en circulation sur l\'avenue Habib Bourguiba à Tunis. Un véhicule arrivant sur ma droite au carrefour n\'a pas respecté la priorité et a heurté mon aile avant droite. Le choc a été modéré mais la carrosserie est enfoncée.', 'uploads/sinistres/sin_6a00df1e76c01.jpg', '2026-05-10', 'refuse', 1, NULL, NULL),
(201, 101, 104, 'Accident auto', 'bhgfwuejif', NULL, '2026-05-10', 'refuse', 1, 1, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `sms_alerts`
--

CREATE TABLE `sms_alerts` (
  `id_alert` int(11) NOT NULL,
  `id_contrat` int(11) NOT NULL,
  `id_client` int(11) DEFAULT NULL,
  `telephone` varchar(30) NOT NULL,
  `message` text NOT NULL,
  `type_alert` varchar(100) NOT NULL DEFAULT 'expiration_contrat',
  `statut` varchar(50) NOT NULL,
  `infobip_message_id` varchar(100) DEFAULT NULL,
  `infobip_bulk_id` varchar(100) DEFAULT NULL,
  `response_json` text DEFAULT NULL,
  `date_envoi` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `sms_alerts`
--

INSERT INTO `sms_alerts` (`id_alert`, `id_contrat`, `id_client`, `telephone`, `message`, `type_alert`, `statut`, `infobip_message_id`, `infobip_bulk_id`, `response_json`, `date_envoi`) VALUES
(3, 14, NULL, '54122325', 'Bonjour Akrem BACCARI, le statut de votre contrat d\'assurance CTR-2026-000007 a été mis à jour : actif.', 'expiration_contrat', 'PENDING_ACCEPTED', '177792366436907950919398', '177792366436907950919397', '{\"success\":true,\"http_code\":200,\"error\":\"\",\"response\":{\"bulkId\":\"177792366436907950919397\",\"messages\":[{\"messageId\":\"177792366436907950919398\",\"status\":{\"groupId\":1,\"groupName\":\"PENDING\",\"id\":26,\"name\":\"PENDING_ACCEPTED\",\"description\":\"Message sent to next instance\"},\"destination\":\"21654122325\"}]}}', '2026-05-05 20:41:05'),
(5, 14, 2, '93981981', 'Bonjour Akrem BACCARI, le statut de votre contrat d\'assurance CTR-2026-000007 a été mis à jour : actif.', 'changement_statut_actif', 'PENDING_ACCEPTED', '177792440823607950966177', '177792440823607950966176', '{\"success\":true,\"http_code\":200,\"error\":\"\",\"response\":{\"bulkId\":\"177792440823607950966176\",\"messages\":[{\"messageId\":\"177792440823607950966177\",\"status\":{\"groupId\":1,\"groupName\":\"PENDING\",\"id\":26,\"name\":\"PENDING_ACCEPTED\",\"description\":\"Message sent to next instance\"},\"destination\":\"21693981981\"}]}}', '2026-05-05 20:53:27'),
(6, 15, 1, '93981981', 'Bonjour Meryem bouazizi, le statut de votre contrat d\'assurance CTR-2026-890918 a été mis à jour : actif.', 'changement_statut_actif', 'PENDING_ACCEPTED', '177801413694307950474375', '177801413694307950474374', '{\"success\":true,\"http_code\":200,\"error\":\"\",\"response\":{\"bulkId\":\"177801413694307950474374\",\"messages\":[{\"messageId\":\"177801413694307950474375\",\"status\":{\"groupId\":1,\"groupName\":\"PENDING\",\"id\":26,\"name\":\"PENDING_ACCEPTED\",\"description\":\"Message sent to next instance\"},\"destination\":\"21693981981\"}]}}', '2026-05-05 21:48:56'),
(7, 19, 26, '93981981', 'Bonjour Meriem bouazizi, le statut de votre contrat d\'assurance CTR-2026-831839 a été mis à jour : actif.', 'changement_statut_actif', 'unknown', NULL, NULL, '{\"success\":false,\"http_code\":0,\"error\":\"Configuration Infobip manquante dans .env.\",\"response\":null}', '2026-05-10 00:02:57'),
(8, 20, 27, '93981981', 'Bonjour Meryem Bz, le statut de votre contrat d\'assurance CTR-2026-309107 a été mis à jour : actif.', 'changement_statut_actif', 'unknown', NULL, NULL, '{\"success\":false,\"http_code\":0,\"error\":\"Configuration Infobip manquante dans .env.\",\"response\":null}', '2026-05-10 02:33:20'),
(9, 22, 27, '93981981', 'Bonjour Meryem Bz, le statut de votre contrat d\'assurance CTR-2026-571024 a été mis à jour : refusé.', 'changement_statut_refus__', 'unknown', NULL, NULL, '{\"success\":false,\"http_code\":0,\"error\":\"Configuration Infobip manquante dans .env.\",\"response\":null}', '2026-05-10 02:54:44'),
(10, 23, 26, '93981981', 'Bonjour Meriem bouazizi, le statut de votre contrat d\'assurance CTR-2026-988014 a été mis à jour : actif.', 'changement_statut_actif', 'unknown', NULL, NULL, '{\"success\":false,\"http_code\":0,\"error\":\"Configuration Infobip manquante dans .env.\",\"response\":null}', '2026-05-10 03:07:37'),
(11, 24, 27, '93981981', 'Bonjour Meryem Bz, le statut de votre contrat d\'assurance CTR-2026-382433 a été mis à jour : résilié.', 'changement_statut_r__sili__', 'unknown', NULL, NULL, '{\"success\":false,\"http_code\":0,\"error\":\"Configuration Infobip manquante dans .env.\",\"response\":null}', '2026-05-10 04:26:14'),
(12, 21, NULL, '93981981', 'Bonjour Meryem Bz, le statut de votre contrat d\'assurance CTR-2026-800552 a été mis à jour : actif.', 'changement_statut_actif', 'unknown', NULL, NULL, '{\"success\":false,\"http_code\":404,\"error\":\"\",\"response\":{\"requestError\":{\"serviceException\":{\"messageId\":\"NOT_FOUND\",\"text\":\"Requested URL not found: /sms/3/messages/sms/3/messages\"}}}}', '2026-05-10 05:59:48'),
(13, 18, 26, '93981981', 'Bonjour Meriem bouazizi, votre contrat Protex CTR-2026-709507 est maintenant actif.', 'changement_statut_actif', 'failed', NULL, NULL, '{\"success\":false,\"http_code\":404,\"error\":\"\",\"response\":{\"requestError\":{\"serviceException\":{\"messageId\":\"NOT_FOUND\",\"text\":\"Requested URL not found: /sms/3/messages/sms/3/messages\"}}}}', '2026-05-10 06:05:19'),
(14, 17, 26, '93981981', 'Bonjour Meriem bouazizi, votre contrat Protex CTR-2026-676870 est maintenant actif.', 'changement_statut_actif', 'PENDING_ACCEPTED', '177839014232507950933279', '177839014232507950933278', '{\"success\":true,\"http_code\":200,\"error\":\"\",\"response\":{\"bulkId\":\"177839014232507950933278\",\"messages\":[{\"messageId\":\"177839014232507950933279\",\"status\":{\"groupId\":1,\"groupName\":\"PENDING\",\"id\":26,\"name\":\"PENDING_ACCEPTED\",\"description\":\"Message sent to next instance\"},\"destination\":\"21693981981\"}]}}', '2026-05-10 06:15:43'),
(15, 20, NULL, '93981981', 'Bonjour Meryem Bz, votre contrat d\'assurance CTR-2026-309107 expire le 09/06/2026, dans 30 jour(s). Merci de le renouveler avant son expiration.', 'expiration_contrat', 'PENDING_ACCEPTED', '177839123870207951985298', '177839123870207951985297', '{\"success\":true,\"http_code\":200,\"error\":\"\",\"response\":{\"bulkId\":\"177839123870207951985297\",\"messages\":[{\"messageId\":\"177839123870207951985298\",\"status\":{\"groupId\":1,\"groupName\":\"PENDING\",\"id\":26,\"name\":\"PENDING_ACCEPTED\",\"description\":\"Message sent to next instance\"},\"destination\":\"21693981981\"}]}}', '2026-05-10 06:34:00');

-- --------------------------------------------------------

--
-- Structure de la table `sos_alerts`
--

CREATE TABLE `sos_alerts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `accuracy` int(11) DEFAULT NULL,
  `nb_contacts_alertes` int(11) DEFAULT 0,
  `statut` enum('en_cours','resolu','fausse_alerte') DEFAULT 'en_cours',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `traitement`
--

CREATE TABLE `traitement` (
  `id_traitement` int(11) NOT NULL,
  `id_sinistre` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `decision` enum('en_attente','refuse','rembourse') NOT NULL DEFAULT 'en_attente',
  `montant_indemnise` decimal(10,2) DEFAULT NULL,
  `statut` enum('accepte','refuse','en_cours') DEFAULT 'en_cours',
  `date_traitement` date NOT NULL,
  `message_agent` text DEFAULT NULL,
  `nom_agent` varchar(150) DEFAULT NULL,
  `valide_par` int(11) DEFAULT NULL,
  `date_validation` datetime DEFAULT NULL,
  `est_valide` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `traitement`
--

INSERT INTO `traitement` (`id_traitement`, `id_sinistre`, `id_user`, `decision`, `montant_indemnise`, `statut`, `date_traitement`, `message_agent`, `nom_agent`, `valide_par`, `date_validation`, `est_valide`) VALUES
(152, 184, 1, 'refuse', 1000.00, 'refuse', '2026-05-10', 'Refus automatique par l\'IA', 'csad', NULL, NULL, 0),
(301, 201, 103, 'refuse', 1000.00, 'refuse', '2026-05-10', 'Refus automatique par l\'IA', 'Agent Muledi', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `cin` varchar(20) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT 'default.png',
  `role` enum('superadmin','admin','agent','client') NOT NULL,
  `statut` enum('actif','bloque') DEFAULT 'actif',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  `face_registered` tinyint(1) NOT NULL DEFAULT 0,
  `face_encoding` text DEFAULT NULL,
  `google_id` varchar(50) DEFAULT NULL,
  `avatar_url` text DEFAULT NULL,
  `github_id` varchar(50) DEFAULT NULL,
  `points_parrainage` int(11) DEFAULT 0,
  `referral_code` varchar(20) DEFAULT NULL,
  `last_seen` datetime DEFAULT NULL,
  `id_agence` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id_user`, `nom`, `prenom`, `email`, `mot_de_passe`, `telephone`, `adresse`, `date_naissance`, `cin`, `avatar`, `role`, `statut`, `date_creation`, `last_login`, `face_registered`, `face_encoding`, `google_id`, `avatar_url`, `github_id`, `points_parrainage`, `referral_code`, `last_seen`, `id_agence`) VALUES
(4, 'Legacy', 'Client', 'legacy-user-4@example.invalid', '', NULL, NULL, NULL, NULL, 'default.png', 'client', 'actif', '2026-05-10 22:25:26', NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, NULL),
(101, 'Miledi', 'Karim', 'medkarimmiledi@gmail.com', '$2y$10$bQLujgpoVCSaqgtTX2u3MegKrTOLZgjd20QdOM.StnNzf8Ygz0uum', NULL, NULL, NULL, NULL, 'default.png', 'superadmin', 'actif', '2026-05-10 20:29:15', '2026-05-11 21:48:28', 0, NULL, NULL, NULL, NULL, 0, NULL, '2026-05-11 22:48:28', 1),
(102, 'Admin', 'Agent', 'medkarimmiledi123@gmail.com', '$2y$10$bQLujgpoVCSaqgtTX2u3MegKrTOLZgjd20QdOM.StnNzf8Ygz0uum', NULL, NULL, NULL, NULL, 'default.png', 'admin', 'actif', '2026-05-10 20:29:15', '2026-05-11 22:46:21', 0, NULL, NULL, NULL, NULL, 0, NULL, '2026-05-11 23:46:21', 1),
(103, 'Muledi', 'Agent', 'muledikarim@gmail.com', '$2y$10$bQLujgpoVCSaqgtTX2u3MegKrTOLZgjd20QdOM.StnNzf8Ygz0uum', NULL, NULL, NULL, NULL, 'default.png', 'agent', 'actif', '2026-05-10 20:29:15', NULL, 0, NULL, NULL, NULL, NULL, 0, NULL, NULL, 1),
(104, 'Sans', 'Bibi', 'sansbibi@gmail.com', '$2y$10$bQLujgpoVCSaqgtTX2u3MegKrTOLZgjd20QdOM.StnNzf8Ygz0uum', NULL, NULL, NULL, NULL, 'default.png', 'client', 'actif', '2026-05-10 20:29:15', '2026-05-10 21:09:12', 0, NULL, NULL, NULL, NULL, 0, NULL, '2026-05-10 22:39:46', 1),
(105, 'sabrine', 'mchabet', 'mchabetsabrine319@gmail.com', '$2y$10$ya3BuQ.5409Sb9OR5Z1OFuyZWHQ.mggGuNSp2QPnlRoyXke74W51u', '21 41 9625', 'tatouin', '2000-01-01', '14785236', 'default.png', 'client', 'actif', '2026-05-11 20:23:04', '2026-05-11 23:51:56', 0, NULL, NULL, NULL, NULL, 0, 'PRTX-6C0895', '2026-05-12 01:03:13', NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_user`),
  ADD KEY `fk_admin_agence` (`id_agence`);

--
-- Index pour la table `agence`
--
ALTER TABLE `agence`
  ADD PRIMARY KEY (`id_agence`);

--
-- Index pour la table `agent`
--
ALTER TABLE `agent`
  ADD PRIMARY KEY (`id_user`),
  ADD KEY `fk_agent_agence` (`id_agence`);

--
-- Index pour la table `audit_reclamation`
--
ALTER TABLE `audit_reclamation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rec` (`reclamation_id`),
  ADD KEY `idx_user` (`id_user`);

--
-- Index pour la table `avis_agence`
--
ALTER TABLE `avis_agence`
  ADD PRIMARY KEY (`id_avis`),
  ADD KEY `fk_avis_agence` (`id_agence`),
  ADD KEY `fk_avis_user` (`id_client`);

--
-- Index pour la table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id_categorie`),
  ADD UNIQUE KEY `nom_categorie` (`nom_categorie`);

--
-- Index pour la table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`id_user`),
  ADD KEY `id_agence` (`id_agence`);

--
-- Index pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD PRIMARY KEY (`id_commentaire`),
  ADD KEY `fk_commentaire_poste` (`id_poste`),
  ADD KEY `fk_commentaire_user` (`id_client`),
  ADD KEY `fk_commentaire_parent` (`id_commentaire_parent`);

--
-- Index pour la table `contrat`
--
ALTER TABLE `contrat`
  ADD PRIMARY KEY (`id_contrat`),
  ADD KEY `fk_contrat_categorie2` (`id_categorie`),
  ADD KEY `fk_contrat_client2` (`id_user`),
  ADD KEY `fk_contrat_formule` (`id_formule`);

--
-- Index pour la table `conversations`
--
ALTER TABLE `conversations`
  ADD PRIMARY KEY (`id_conversation`),
  ADD KEY `idx_derniere` (`derniere_activite`),
  ADD KEY `cree_par` (`cree_par`);

--
-- Index pour la table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  ADD PRIMARY KEY (`id_conversation`,`id_user`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `devis`
--
ALTER TABLE `devis`
  ADD PRIMARY KEY (`id_devis`),
  ADD KEY `idx_devis_offre` (`id_offre`),
  ADD KEY `idx_devis_statut` (`statut`),
  ADD KEY `idx_devis_type` (`type_assurance`),
  ADD KEY `idx_devis_date` (`date_demande`);

--
-- Index pour la table `devis_auto`
--
ALTER TABLE `devis_auto`
  ADD PRIMARY KEY (`id_auto`),
  ADD KEY `idx_auto_devis` (`id_devis`);

--
-- Index pour la table `devis_habitation`
--
ALTER TABLE `devis_habitation`
  ADD PRIMARY KEY (`id_habitation`),
  ADD KEY `idx_habitation_devis` (`id_devis`);

--
-- Index pour la table `devis_sante`
--
ALTER TABLE `devis_sante`
  ADD PRIMARY KEY (`id_sante`),
  ADD KEY `idx_sante_devis` (`id_devis`);

--
-- Index pour la table `formule`
--
ALTER TABLE `formule`
  ADD PRIMARY KEY (`id_formule`),
  ADD KEY `fk_formule_categorie` (`id_categorie`);

--
-- Index pour la table `formule_garantie`
--
ALTER TABLE `formule_garantie`
  ADD PRIMARY KEY (`id_formule`,`id_garantie`),
  ADD KEY `id_garantie` (`id_garantie`);

--
-- Index pour la table `fraud_analysis`
--
ALTER TABLE `fraud_analysis`
  ADD PRIMARY KEY (`id_fraud`),
  ADD UNIQUE KEY `uq_sinistre` (`id_sinistre`),
  ADD KEY `fk_fraud_user` (`id_user`),
  ADD KEY `idx_niveau_risque` (`niveau_risque`),
  ADD KEY `idx_score_global` (`score_global`);

--
-- Index pour la table `friendships`
--
ALTER TABLE `friendships`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_friendship` (`sender_id`,`receiver_id`),
  ADD KEY `receiver_id` (`receiver_id`);

--
-- Index pour la table `garantie`
--
ALTER TABLE `garantie`
  ADD PRIMARY KEY (`id_garantie`);

--
-- Index pour la table `like_post`
--
ALTER TABLE `like_post`
  ADD PRIMARY KEY (`id_like`),
  ADD UNIQUE KEY `unique_like` (`id_poste`,`id_client`),
  ADD KEY `fk_like_poste` (`id_poste`),
  ADD KEY `fk_like_user` (`id_client`);

--
-- Index pour la table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip_statut` (`ip`,`statut`,`created_at`),
  ADD KEY `idx_email_statut` (`email`,`statut`,`created_at`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_messages_sender_receiver` (`sender_id`,`receiver_id`),
  ADD KEY `idx_messages_receiver_read` (`receiver_id`,`is_read`);

--
-- Index pour la table `messages_admin`
--
ALTER TABLE `messages_admin`
  ADD PRIMARY KEY (`id_message`),
  ADD KEY `messages_admin_ibfk_1` (`id_conversation`),
  ADD KEY `messages_admin_ibfk_2` (`id_expediteur`);

--
-- Index pour la table `message_mentions`
--
ALTER TABLE `message_mentions`
  ADD PRIMARY KEY (`id_mention`),
  ADD KEY `message_mentions_ibfk_1` (`id_message`),
  ADD KEY `message_mentions_ibfk_2` (`id_user_mentionne`);

--
-- Index pour la table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id_notification`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `offre`
--
ALTER TABLE `offre`
  ADD PRIMARY KEY (`id_offre`);

--
-- Index pour la table `otp_codes`
--
ALTER TABLE `otp_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`id_user`);

--
-- Index pour la table `paiement`
--
ALTER TABLE `paiement`
  ADD PRIMARY KEY (`id_paiement`),
  ADD UNIQUE KEY `reference` (`reference`),
  ADD KEY `fk_paiement_offre` (`id_offre`),
  ADD KEY `idx_id_devis` (`id_devis`);

--
-- Index pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `poste`
--
ALTER TABLE `poste`
  ADD PRIMARY KEY (`id_poste`),
  ADD KEY `fk_poste_agence` (`id_agence`);

--
-- Index pour la table `reclamation`
--
ALTER TABLE `reclamation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_reclamation_user` (`id_user`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_priorite` (`priorite`),
  ADD KEY `idx_date` (`date_depot`),
  ADD KEY `idx_user_rel` (`id_user`);

--
-- Index pour la table `recommandation_historique`
--
ALTER TABLE `recommandation_historique`
  ADD PRIMARY KEY (`id_recommandation`);

--
-- Index pour la table `reponse`
--
ALTER TABLE `reponse`
  ADD PRIMARY KEY (`id_re`),
  ADD UNIQUE KEY `uq_reponse_reclamation` (`reclamation_id`),
  ADD KEY `fk_reponse_reclamation` (`reclamation_id`),
  ADD KEY `fk_reponse_user` (`id_user`),
  ADD KEY `idx_reclamation` (`reclamation_id`);

--
-- Index pour la table `reponse_history`
--
ALTER TABLE `reponse_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rep` (`reponse_id`);

--
-- Index pour la table `roulette_gains`
--
ALTER TABLE `roulette_gains`
  ADD PRIMARY KEY (`id_gain`);

--
-- Index pour la table `roulette_jeu`
--
ALTER TABLE `roulette_jeu`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code_promo` (`code_promo`);

--
-- Index pour la table `sinistre`
--
ALTER TABLE `sinistre`
  ADD PRIMARY KEY (`id_sinistre`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `fk_sinistre_contrat` (`id_contrat`),
  ADD KEY `fk_sinistre_agence` (`id_agence`),
  ADD KEY `fk_sinistre_agent` (`id_agent_assigne`);

--
-- Index pour la table `sms_alerts`
--
ALTER TABLE `sms_alerts`
  ADD PRIMARY KEY (`id_alert`),
  ADD UNIQUE KEY `unique_sms_expiration` (`id_contrat`,`type_alert`);

--
-- Index pour la table `sos_alerts`
--
ALTER TABLE `sos_alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `traitement`
--
ALTER TABLE `traitement`
  ADD PRIMARY KEY (`id_traitement`),
  ADD KEY `id_sinistre` (`id_sinistre`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `fk_traitement_valideur` (`valide_par`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `referral_code` (`referral_code`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `agence`
--
ALTER TABLE `agence`
  MODIFY `id_agence` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT pour la table `audit_reclamation`
--
ALTER TABLE `audit_reclamation`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `avis_agence`
--
ALTER TABLE `avis_agence`
  MODIFY `id_avis` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT pour la table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id_categorie` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `commentaire`
--
ALTER TABLE `commentaire`
  MODIFY `id_commentaire` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `contrat`
--
ALTER TABLE `contrat`
  MODIFY `id_contrat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=103;

--
-- AUTO_INCREMENT pour la table `conversations`
--
ALTER TABLE `conversations`
  MODIFY `id_conversation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `devis`
--
ALTER TABLE `devis`
  MODIFY `id_devis` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `devis_auto`
--
ALTER TABLE `devis_auto`
  MODIFY `id_auto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `devis_habitation`
--
ALTER TABLE `devis_habitation`
  MODIFY `id_habitation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `devis_sante`
--
ALTER TABLE `devis_sante`
  MODIFY `id_sante` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `formule`
--
ALTER TABLE `formule`
  MODIFY `id_formule` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `fraud_analysis`
--
ALTER TABLE `fraud_analysis`
  MODIFY `id_fraud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT pour la table `friendships`
--
ALTER TABLE `friendships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `garantie`
--
ALTER TABLE `garantie`
  MODIFY `id_garantie` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT pour la table `like_post`
--
ALTER TABLE `like_post`
  MODIFY `id_like` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `messages_admin`
--
ALTER TABLE `messages_admin`
  MODIFY `id_message` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `message_mentions`
--
ALTER TABLE `message_mentions`
  MODIFY `id_mention` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `notification`
--
ALTER TABLE `notification`
  MODIFY `id_notification` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `offre`
--
ALTER TABLE `offre`
  MODIFY `id_offre` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `otp_codes`
--
ALTER TABLE `otp_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT pour la table `paiement`
--
ALTER TABLE `paiement`
  MODIFY `id_paiement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pour la table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `poste`
--
ALTER TABLE `poste`
  MODIFY `id_poste` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT pour la table `reclamation`
--
ALTER TABLE `reclamation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT pour la table `recommandation_historique`
--
ALTER TABLE `recommandation_historique`
  MODIFY `id_recommandation` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `reponse`
--
ALTER TABLE `reponse`
  MODIFY `id_re` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT pour la table `reponse_history`
--
ALTER TABLE `reponse_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `roulette_gains`
--
ALTER TABLE `roulette_gains`
  MODIFY `id_gain` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `roulette_jeu`
--
ALTER TABLE `roulette_jeu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `sinistre`
--
ALTER TABLE `sinistre`
  MODIFY `id_sinistre` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=202;

--
-- AUTO_INCREMENT pour la table `sms_alerts`
--
ALTER TABLE `sms_alerts`
  MODIFY `id_alert` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `sos_alerts`
--
ALTER TABLE `sos_alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `traitement`
--
ALTER TABLE `traitement`
  MODIFY `id_traitement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=302;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_fk_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_admin_agence` FOREIGN KEY (`id_agence`) REFERENCES `agence` (`id_agence`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `agent`
--
ALTER TABLE `agent`
  ADD CONSTRAINT `agent_fk_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_agent_agence` FOREIGN KEY (`id_agence`) REFERENCES `agence` (`id_agence`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `avis_agence`
--
ALTER TABLE `avis_agence`
  ADD CONSTRAINT `fk_avis_agence` FOREIGN KEY (`id_agence`) REFERENCES `agence` (`id_agence`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_avis_user` FOREIGN KEY (`id_client`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `client`
--
ALTER TABLE `client`
  ADD CONSTRAINT `client_fk_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_ibfk_1` FOREIGN KEY (`id_agence`) REFERENCES `agence` (`id_agence`) ON DELETE SET NULL;

--
-- Contraintes pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD CONSTRAINT `fk_commentaire_parent` FOREIGN KEY (`id_commentaire_parent`) REFERENCES `commentaire` (`id_commentaire`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_commentaire_poste` FOREIGN KEY (`id_poste`) REFERENCES `poste` (`id_poste`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_commentaire_user` FOREIGN KEY (`id_client`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `contrat`
--
ALTER TABLE `contrat`
  ADD CONSTRAINT `fk_contrat_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `conversations`
--
ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`cree_par`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `conversation_participants`
--
ALTER TABLE `conversation_participants`
  ADD CONSTRAINT `conversation_participants_ibfk_1` FOREIGN KEY (`id_conversation`) REFERENCES `conversations` (`id_conversation`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversation_participants_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `devis`
--
ALTER TABLE `devis`
  ADD CONSTRAINT `fk_devis_offre` FOREIGN KEY (`id_offre`) REFERENCES `offre` (`id_offre`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `devis_auto`
--
ALTER TABLE `devis_auto`
  ADD CONSTRAINT `fk_auto_devis` FOREIGN KEY (`id_devis`) REFERENCES `devis` (`id_devis`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `devis_habitation`
--
ALTER TABLE `devis_habitation`
  ADD CONSTRAINT `fk_habitation_devis` FOREIGN KEY (`id_devis`) REFERENCES `devis` (`id_devis`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `devis_sante`
--
ALTER TABLE `devis_sante`
  ADD CONSTRAINT `fk_sante_devis` FOREIGN KEY (`id_devis`) REFERENCES `devis` (`id_devis`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `formule`
--
ALTER TABLE `formule`
  ADD CONSTRAINT `fk_formule_categorie` FOREIGN KEY (`id_categorie`) REFERENCES `categorie` (`id_categorie`) ON DELETE CASCADE;

--
-- Contraintes pour la table `formule_garantie`
--
ALTER TABLE `formule_garantie`
  ADD CONSTRAINT `formule_garantie_ibfk_1` FOREIGN KEY (`id_formule`) REFERENCES `formule` (`id_formule`) ON DELETE CASCADE,
  ADD CONSTRAINT `formule_garantie_ibfk_2` FOREIGN KEY (`id_garantie`) REFERENCES `garantie` (`id_garantie`) ON DELETE CASCADE;

--
-- Contraintes pour la table `fraud_analysis`
--
ALTER TABLE `fraud_analysis`
  ADD CONSTRAINT `fk_fraud_sinistre` FOREIGN KEY (`id_sinistre`) REFERENCES `sinistre` (`id_sinistre`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `friendships`
--
ALTER TABLE `friendships`
  ADD CONSTRAINT `friendships_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `friendships_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `like_post`
--
ALTER TABLE `like_post`
  ADD CONSTRAINT `fk_like_poste` FOREIGN KEY (`id_poste`) REFERENCES `poste` (`id_poste`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_like_user` FOREIGN KEY (`id_client`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `messages_admin`
--
ALTER TABLE `messages_admin`
  ADD CONSTRAINT `messages_admin_ibfk_1` FOREIGN KEY (`id_conversation`) REFERENCES `conversations` (`id_conversation`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_admin_ibfk_2` FOREIGN KEY (`id_expediteur`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `message_mentions`
--
ALTER TABLE `message_mentions`
  ADD CONSTRAINT `message_mentions_ibfk_1` FOREIGN KEY (`id_message`) REFERENCES `messages_admin` (`id_message`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_mentions_ibfk_2` FOREIGN KEY (`id_user_mentionne`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `notification`
--
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `otp_codes`
--
ALTER TABLE `otp_codes`
  ADD CONSTRAINT `otp_codes_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `paiement`
--
ALTER TABLE `paiement`
  ADD CONSTRAINT `fk_paiement_offre` FOREIGN KEY (`id_offre`) REFERENCES `offre` (`id_offre`) ON UPDATE CASCADE;

--
-- Contraintes pour la table `poste`
--
ALTER TABLE `poste`
  ADD CONSTRAINT `fk_poste_agence` FOREIGN KEY (`id_agence`) REFERENCES `agence` (`id_agence`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `reclamation`
--
ALTER TABLE `reclamation`
  ADD CONSTRAINT `fk_reclamation_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`);

--
-- Contraintes pour la table `reponse`
--
ALTER TABLE `reponse`
  ADD CONSTRAINT `fk_reponse_reclamation` FOREIGN KEY (`reclamation_id`) REFERENCES `reclamation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reponse_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `reponse_ibfk_1` FOREIGN KEY (`reclamation_id`) REFERENCES `reclamation` (`id`);

--
-- Contraintes pour la table `sinistre`
--
ALTER TABLE `sinistre`
  ADD CONSTRAINT `fk_sinistre_agence` FOREIGN KEY (`id_agence`) REFERENCES `agence` (`id_agence`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sinistre_agent` FOREIGN KEY (`id_agent_assigne`) REFERENCES `user` (`id_user`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sinistre_contrat` FOREIGN KEY (`id_contrat`) REFERENCES `contrat` (`id_contrat`) ON DELETE SET NULL;

--
-- Contraintes pour la table `sos_alerts`
--
ALTER TABLE `sos_alerts`
  ADD CONSTRAINT `sos_alerts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

--
-- Contraintes pour la table `traitement`
--
ALTER TABLE `traitement`
  ADD CONSTRAINT `fk_traitement_valideur` FOREIGN KEY (`valide_par`) REFERENCES `user` (`id_user`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
