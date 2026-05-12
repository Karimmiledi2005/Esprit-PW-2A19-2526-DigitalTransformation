-- =============================================================
-- assurance_complete.sql
-- Base de données unifiée Protex — Projet intégration Esprit
-- Généré le 2026-05-12
-- Sources :
--   • dossier_camarades/assurance (12).sql  (schéma de référence)
--   • migrations/001..005                   (messagerie, contrats, jeux)
--   • migrations/20260511_fix_reclamation_status.sql
--   • scripts/migrate_roles_agence.sql, migrate_sinistre_contrat.sql
-- Utilisation : importer via phpMyAdmin ou mysql -u root assurance < assurance_complete.sql
-- =============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;
SET time_zone = "+00:00";
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- -----------------------------------------------------------------
-- Base de données
-- -----------------------------------------------------------------
CREATE DATABASE IF NOT EXISTS `assurance`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
USE `assurance`;

-- =================================================================
-- TABLES (ordre respectant les FK : parents avant enfants)
-- =================================================================

-- -----------------------------------------------------------------
-- agence  (aucune FK entrante de tables supérieures)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `agence` (
  `id_agence` int(11) NOT NULL AUTO_INCREMENT,
  `nom_agence` varchar(100) NOT NULL,
  `pays` varchar(50) DEFAULT NULL,
  `tel` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `avis_json` longtext DEFAULT NULL,
  `statut` enum('active','inactive') NOT NULL DEFAULT 'active',
  `adresse` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_agence`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- user  (référence agence)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
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
  `id_agence` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `referral_code` (`referral_code`),
  KEY `fk_user_agence` (`id_agence`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- admin  (référence user, agence)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admin` (
  `id_user` int(11) NOT NULL,
  `niveau_acces` enum('superadmin','admin_agence') NOT NULL DEFAULT 'admin_agence',
  `id_agence` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  KEY `fk_admin_agence` (`id_agence`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- agent  (référence user, agence)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `agent` (
  `id_user` int(11) NOT NULL,
  `agence` varchar(100) DEFAULT NULL,
  `salaire` decimal(10,2) DEFAULT NULL,
  `id_agence` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  KEY `fk_agent_agence` (`id_agence`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- client  (référence user, agence)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `client` (
  `id_user` int(11) NOT NULL,
  `numero_client` varchar(50) DEFAULT NULL,
  `id_agence` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  KEY `id_agence` (`id_agence`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- categorie
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categorie` (
  `id_categorie` int(11) NOT NULL AUTO_INCREMENT,
  `nom_categorie` varchar(100) NOT NULL,
  `description_categorie` text DEFAULT NULL,
  `slug_categorie` varchar(100) DEFAULT NULL,
  `icone_categorie` varchar(100) DEFAULT NULL,
  `couleur_categorie` varchar(50) DEFAULT NULL,
  `description_front` varchar(255) DEFAULT NULL,
  `lien_front` varchar(255) DEFAULT NULL,
  `page_formule` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_categorie`),
  UNIQUE KEY `nom_categorie` (`nom_categorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- garantie  (référence categorie)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `garantie` (
  `id_garantie` int(11) NOT NULL AUTO_INCREMENT,
  `nom_garantie` varchar(100) DEFAULT NULL,
  `description_garantie` text DEFAULT NULL,
  `plafond_couvert_garantie` decimal(10,2) DEFAULT NULL,
  `id_categorie` int(11) NOT NULL,
  PRIMARY KEY (`id_garantie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- formule  (référence categorie)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `formule` (
  `id_formule` int(11) NOT NULL AUTO_INCREMENT,
  `nom_formule` varchar(100) NOT NULL,
  `description_formule` text DEFAULT NULL,
  `prix_formule` decimal(10,2) DEFAULT 0.00,
  `franchise_formule` decimal(10,2) NOT NULL DEFAULT 0.00,
  `id_categorie` int(11) NOT NULL,
  `niveau_formule` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_formule`),
  KEY `fk_formule_categorie` (`id_categorie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- formule_garantie  (référence formule, garantie)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `formule_garantie` (
  `id_formule` int(11) NOT NULL,
  `id_garantie` int(11) NOT NULL,
  `niveau_couvert_garantie` enum('basique','option','non disponible') NOT NULL DEFAULT 'basique',
  PRIMARY KEY (`id_formule`,`id_garantie`),
  KEY `id_garantie` (`id_garantie`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- offre
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `offre` (
  `id_offre` int(11) NOT NULL AUTO_INCREMENT,
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
  `date_modification` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_offre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- devis  (référence offre)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `devis` (
  `id_devis` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(30) NOT NULL,
  `type_assurance` enum('auto','sante','habitation') NOT NULL,
  `id_offre` int(11) DEFAULT NULL,
  `montant_estime` decimal(10,3) DEFAULT NULL,
  `statut` enum('en_attente','accepte','refuse','traite') NOT NULL DEFAULT 'en_attente',
  `reponse_admin` text DEFAULT NULL,
  `date_demande` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_devis`),
  KEY `idx_devis_offre` (`id_offre`),
  KEY `idx_devis_statut` (`statut`),
  KEY `idx_devis_type` (`type_assurance`),
  KEY `idx_devis_date` (`date_demande`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- devis_auto  (référence devis)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `devis_auto` (
  `id_auto` int(11) NOT NULL AUTO_INCREMENT,
  `id_devis` int(11) NOT NULL,
  `marque` varchar(100) NOT NULL,
  `modele` varchar(100) NOT NULL,
  `annee` int(11) DEFAULT NULL,
  `carburant` varchar(50) DEFAULT NULL,
  `puissance` int(11) DEFAULT NULL,
  `immatriculation` varchar(50) DEFAULT NULL,
  `usage_vehicule` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id_auto`),
  KEY `idx_auto_devis` (`id_devis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- devis_habitation  (référence devis)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `devis_habitation` (
  `id_habitation` int(11) NOT NULL AUTO_INCREMENT,
  `id_devis` int(11) NOT NULL,
  `type_logement` varchar(100) NOT NULL,
  `surface` int(11) DEFAULT NULL,
  `valeur_bien` decimal(12,3) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `proprietaire_locataire` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_habitation`),
  KEY `idx_habitation_devis` (`id_devis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- devis_sante  (référence devis)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `devis_sante` (
  `id_sante` int(11) NOT NULL AUTO_INCREMENT,
  `id_devis` int(11) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `situation_familiale` varchar(100) DEFAULT NULL,
  `nombre_enfants` int(11) DEFAULT 0,
  `maladies` text DEFAULT NULL,
  `couverture_souhaitee` varchar(150) DEFAULT NULL,
  PRIMARY KEY (`id_sante`),
  KEY `idx_sante_devis` (`id_devis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- paiement  (référence offre)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `paiement` (
  `id_paiement` int(11) NOT NULL AUTO_INCREMENT,
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
  `code_promo` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_paiement`),
  UNIQUE KEY `reference` (`reference`),
  KEY `fk_paiement_offre` (`id_offre`),
  KEY `idx_id_devis` (`id_devis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- contrat  (référence user, categorie, formule)
-- NOTE : cette table utilise le schéma camarades (id_user, id_categorie)
--        Le modèle Contrat.php détecte automatiquement la colonne présente.
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contrat` (
  `id_contrat` int(11) NOT NULL AUTO_INCREMENT,
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
  `id_formule` int(11) DEFAULT NULL,
  `id_devis` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_contrat`),
  KEY `fk_contrat_categorie` (`id_categorie`),
  KEY `fk_contrat_user` (`id_user`),
  KEY `fk_contrat_formule` (`id_formule`),
  KEY `fk_contrat_devis` (`id_devis`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- sinistre  (référence contrat, user, agence)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `sinistre` (
  `id_sinistre` int(11) NOT NULL AUTO_INCREMENT,
  `id_contrat` int(11) DEFAULT NULL,
  `id_user` int(11) NOT NULL,
  `type` enum('Accident auto','Incendie','Vol','Degat des eaux','Dégât des eaux') NOT NULL,
  `description` text DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `date_declaration` date NOT NULL,
  `statut` enum('en_attente','rembourse','refuse') DEFAULT 'en_attente',
  `is_read` tinyint(1) DEFAULT 0,
  `id_agence` int(11) DEFAULT NULL,
  `id_agent_assigne` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_sinistre`),
  KEY `id_user` (`id_user`),
  KEY `fk_sinistre_contrat` (`id_contrat`),
  KEY `fk_sinistre_agence` (`id_agence`),
  KEY `fk_sinistre_agent` (`id_agent_assigne`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- traitement  (référence sinistre, user)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `traitement` (
  `id_traitement` int(11) NOT NULL AUTO_INCREMENT,
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
  `est_valide` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id_traitement`),
  KEY `id_sinistre` (`id_sinistre`),
  KEY `id_user` (`id_user`),
  KEY `fk_traitement_valideur` (`valide_par`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- fraud_analysis  (référence sinistre, user)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `fraud_analysis` (
  `id_fraud` int(11) NOT NULL AUTO_INCREMENT,
  `id_sinistre` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `score_global` tinyint(3) NOT NULL DEFAULT 0,
  `niveau_risque` enum('faible','normal','fraude') DEFAULT 'faible',
  `suggestion_ia` enum('accepter','investiguer','refuser') DEFAULT 'accepter',
  `score_texte` tinyint(3) DEFAULT 0,
  `score_comportement` tinyint(3) DEFAULT 0,
  `score_contrat` tinyint(3) DEFAULT 0,
  `score_image` tinyint(3) DEFAULT 0,
  `flag_description_vague` tinyint(1) DEFAULT 0,
  `flag_sinistres_multiples` tinyint(1) DEFAULT 0,
  `flag_contrat_recent` tinyint(1) DEFAULT 0,
  `flag_montant_eleve` tinyint(1) DEFAULT 0,
  `flag_image_suspecte` tinyint(1) DEFAULT 0,
  `analyse_texte` text DEFAULT NULL,
  `analyse_comportement` text DEFAULT NULL,
  `analyse_image` text DEFAULT NULL,
  `recommandation_ia` text DEFAULT NULL,
  `date_analyse` datetime NOT NULL DEFAULT current_timestamp(),
  `version_modele` varchar(50) DEFAULT 'claude-sonnet-4-20250514',
  PRIMARY KEY (`id_fraud`),
  UNIQUE KEY `uq_sinistre` (`id_sinistre`),
  KEY `fk_fraud_user` (`id_user`),
  KEY `idx_niveau_risque` (`niveau_risque`),
  KEY `idx_score_global` (`score_global`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- reclamation  (référence user)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reclamation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `objet` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `ref_contrat` varchar(100) DEFAULT NULL,
  `priorite` varchar(50) DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL,
  `date_depot` date DEFAULT NULL,
  `rec_ref` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `email` varchar(150) NOT NULL DEFAULT '',
  `id_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_reclamation_user` (`id_user`),
  KEY `idx_statut` (`statut`),
  KEY `idx_priorite` (`priorite`),
  KEY `idx_date` (`date_depot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- reponse  (référence reclamation, user)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reponse` (
  `id_re` int(11) NOT NULL AUTO_INCREMENT,
  `date_reponse` date DEFAULT NULL,
  `contenu` text NOT NULL,
  `statut` varchar(50) DEFAULT NULL,
  `reclamation_id` int(11) DEFAULT NULL,
  `id_user` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_re`),
  UNIQUE KEY `uq_reponse_reclamation` (`reclamation_id`),
  KEY `fk_reponse_reclamation` (`reclamation_id`),
  KEY `fk_reponse_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- audit_reclamation  (référence reclamation, user)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_reclamation` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `action` varchar(50) NOT NULL,
  `reclamation_id` int(10) UNSIGNED NOT NULL,
  `reponse_id` int(10) UNSIGNED DEFAULT NULL,
  `id_user` int(10) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_rec` (`reclamation_id`),
  KEY `idx_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- reponse_history  (référence reponse)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `reponse_history` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `reponse_id` int(10) UNSIGNED NOT NULL,
  `ancien_contenu` text NOT NULL,
  `modifie_par` int(10) UNSIGNED DEFAULT NULL,
  `modifie_le` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_rep` (`reponse_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- conversations / messagerie interne  (référence user)
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `conversations` (
  `id_conversation` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(200) DEFAULT NULL COMMENT 'NULL = conversation privée',
  `type` enum('prive','groupe') DEFAULT 'prive',
  `cree_par` int(11) NOT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `derniere_activite` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_conversation`),
  KEY `idx_derniere` (`derniere_activite`),
  KEY `cree_par` (`cree_par`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `conversation_participants` (
  `id_conversation` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `dernier_message_lu` datetime DEFAULT NULL,
  PRIMARY KEY (`id_conversation`,`id_user`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `messages_admin` (
  `id_message` int(11) NOT NULL AUTO_INCREMENT,
  `id_conversation` int(11) NOT NULL,
  `id_expediteur` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `type_message` enum('texte','mention','image','audio','systeme') DEFAULT 'texte',
  `fichier_url` varchar(500) DEFAULT NULL,
  `duree_audio` int(11) DEFAULT NULL,
  `est_lu` tinyint(1) DEFAULT 0,
  `date_envoi` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_message`),
  KEY `messages_admin_ibfk_1` (`id_conversation`),
  KEY `messages_admin_ibfk_2` (`id_expediteur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `message_mentions` (
  `id_mention` int(11) NOT NULL AUTO_INCREMENT,
  `id_message` int(11) NOT NULL,
  `id_user_mentionne` int(11) NOT NULL,
  `est_resolu` tinyint(1) DEFAULT 0,
  `date_mention` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_mention`),
  KEY `message_mentions_ibfk_1` (`id_message`),
  KEY `message_mentions_ibfk_2` (`id_user_mentionne`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- Roulette
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `roulette_gains` (
  `id_gain` int(11) NOT NULL AUTO_INCREMENT,
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
  `date_utilisation` datetime DEFAULT NULL,
  PRIMARY KEY (`id_gain`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `roulette_jeu` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email_client` varchar(150) NOT NULL,
  `palier` enum('bronze','argent','or') NOT NULL,
  `cadeau_label` varchar(100) DEFAULT NULL,
  `code_promo` varchar(30) DEFAULT NULL,
  `valeur_reduction` decimal(10,3) DEFAULT NULL,
  `date_jeu` datetime DEFAULT current_timestamp(),
  `utilise` tinyint(1) DEFAULT 0,
  `date_utilisation` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_promo` (`code_promo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- Jeux
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `jeu_snake` (
  `id_jeu` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `vitesse` varchar(20) NOT NULL DEFAULT 'Normal',
  `duree_sec` int(11) NOT NULL DEFAULT 0,
  `serpents_manges` int(11) NOT NULL DEFAULT 0,
  `date_jeu` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_jeu`),
  KEY `idx_user` (`id_user`),
  KEY `idx_score` (`score`),
  KEY `idx_date` (`date_jeu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `jeu_memory` (
  `id_jeu` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `temps` int(11) NOT NULL DEFAULT 0 COMMENT 'Temps en secondes',
  `coups` int(11) NOT NULL DEFAULT 0,
  `difficulte` varchar(20) NOT NULL DEFAULT 'Facile',
  `nb_paires` int(11) NOT NULL DEFAULT 6,
  `date_jeu` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_jeu`),
  KEY `idx_user` (`id_user`),
  KEY `idx_temps` (`temps`),
  KEY `idx_date` (`date_jeu`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- -----------------------------------------------------------------
-- Autres tables
-- -----------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `poste` (
  `id_poste` int(11) NOT NULL AUTO_INCREMENT,
  `contenu` text NOT NULL,
  `date_publication` date DEFAULT NULL,
  `note` int(11) DEFAULT NULL CHECK (`note` between 1 and 5),
  `auteur` varchar(100) DEFAULT NULL,
  `nb_likes` int(11) DEFAULT 0,
  `nb_commentaires` int(11) DEFAULT 0,
  `id_agence` int(11) DEFAULT NULL,
  `commentaires_json` longtext DEFAULT NULL,
  `likes_json` longtext DEFAULT NULL,
  PRIMARY KEY (`id_poste`),
  KEY `fk_poste_agence` (`id_agence`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `commentaire` (
  `id_commentaire` int(11) NOT NULL AUTO_INCREMENT,
  `contenu` text NOT NULL,
  `date_commentaire` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_poste` int(11) NOT NULL,
  `id_client` int(11) NOT NULL,
  `id_commentaire_parent` int(11) DEFAULT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_commentaire`),
  KEY `fk_commentaire_poste` (`id_poste`),
  KEY `fk_commentaire_user` (`id_client`),
  KEY `fk_commentaire_parent` (`id_commentaire_parent`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `avis_agence` (
  `id_avis` int(11) NOT NULL AUTO_INCREMENT,
  `note` int(11) NOT NULL CHECK (`note` between 1 and 5),
  `commentaire` text NOT NULL,
  `date_avis` datetime NOT NULL DEFAULT current_timestamp(),
  `id_agence` int(11) NOT NULL,
  `id_client` int(11) NOT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_avis`),
  KEY `fk_avis_agence` (`id_agence`),
  KEY `fk_avis_user` (`id_client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `like_post` (
  `id_like` int(11) NOT NULL AUTO_INCREMENT,
  `id_poste` int(11) NOT NULL,
  `id_client` int(11) NOT NULL,
  `date_like` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_like`),
  UNIQUE KEY `unique_like` (`id_poste`,`id_client`),
  KEY `fk_like_poste` (`id_poste`),
  KEY `fk_like_user` (`id_client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `notification` (
  `id_notification` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'info',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id_notification`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `statut` enum('success','failed') DEFAULT 'failed',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_ip_statut` (`ip`,`statut`,`created_at`),
  KEY `idx_email_statut` (`email`,`statut`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `otp_codes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `code` varchar(6) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `id_user` (`id_user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `sms_alerts` (
  `id_alert` int(11) NOT NULL AUTO_INCREMENT,
  `id_contrat` int(11) NOT NULL,
  `id_client` int(11) DEFAULT NULL,
  `telephone` varchar(30) NOT NULL,
  `message` text NOT NULL,
  `type_alert` varchar(100) NOT NULL DEFAULT 'expiration_contrat',
  `statut` varchar(50) NOT NULL,
  `infobip_message_id` varchar(100) DEFAULT NULL,
  `infobip_bulk_id` varchar(100) DEFAULT NULL,
  `response_json` text DEFAULT NULL,
  `date_envoi` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_alert`),
  UNIQUE KEY `unique_sms_expiration` (`id_contrat`,`type_alert`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `sos_alerts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `accuracy` int(11) DEFAULT NULL,
  `nb_contacts_alertes` int(11) DEFAULT 0,
  `statut` enum('en_cours','resolu','fausse_alerte') DEFAULT 'en_cours',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `friendships` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `status` enum('pending','accepted','refused') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_trusted` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_friendship` (`sender_id`,`receiver_id`),
  KEY `receiver_id` (`receiver_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sender_id` int(11) NOT NULL,
  `receiver_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_messages_sender_receiver` (`sender_id`,`receiver_id`),
  KEY `idx_messages_receiver_read` (`receiver_id`,`is_read`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS `recommandation_historique` (
  `id_recommandation` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) DEFAULT NULL,
  `besoin` varchar(100) DEFAULT NULL,
  `budget` varchar(50) DEFAULT NULL,
  `profil_risque` varchar(50) DEFAULT NULL,
  `id_formule_recommandee` int(11) DEFAULT NULL,
  `date_recommandation` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id_recommandation`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- =================================================================
-- CONTRAINTES DE CLÉ ÉTRANGÈRE
-- =================================================================

ALTER TABLE `user`
  ADD CONSTRAINT `fk_user_agence` FOREIGN KEY (`id_agence`) REFERENCES `agence` (`id_agence`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `admin`
  ADD CONSTRAINT `admin_fk_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_admin_agence` FOREIGN KEY (`id_agence`) REFERENCES `agence` (`id_agence`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `agent`
  ADD CONSTRAINT `agent_fk_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_agent_agence` FOREIGN KEY (`id_agence`) REFERENCES `agence` (`id_agence`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `client`
  ADD CONSTRAINT `client_fk_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `client_ibfk_1` FOREIGN KEY (`id_agence`) REFERENCES `agence` (`id_agence`) ON DELETE SET NULL;

ALTER TABLE `garantie`
  ADD CONSTRAINT `fk_garantie_categorie` FOREIGN KEY (`id_categorie`) REFERENCES `categorie` (`id_categorie`) ON DELETE CASCADE;

ALTER TABLE `formule`
  ADD CONSTRAINT `fk_formule_categorie` FOREIGN KEY (`id_categorie`) REFERENCES `categorie` (`id_categorie`) ON DELETE CASCADE;

ALTER TABLE `formule_garantie`
  ADD CONSTRAINT `formule_garantie_ibfk_1` FOREIGN KEY (`id_formule`) REFERENCES `formule` (`id_formule`) ON DELETE CASCADE,
  ADD CONSTRAINT `formule_garantie_ibfk_2` FOREIGN KEY (`id_garantie`) REFERENCES `garantie` (`id_garantie`) ON DELETE CASCADE;

ALTER TABLE `devis`
  ADD CONSTRAINT `fk_devis_offre` FOREIGN KEY (`id_offre`) REFERENCES `offre` (`id_offre`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `devis_auto`
  ADD CONSTRAINT `fk_auto_devis` FOREIGN KEY (`id_devis`) REFERENCES `devis` (`id_devis`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `devis_habitation`
  ADD CONSTRAINT `fk_habitation_devis` FOREIGN KEY (`id_devis`) REFERENCES `devis` (`id_devis`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `devis_sante`
  ADD CONSTRAINT `fk_sante_devis` FOREIGN KEY (`id_devis`) REFERENCES `devis` (`id_devis`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `paiement`
  ADD CONSTRAINT `fk_paiement_offre` FOREIGN KEY (`id_offre`) REFERENCES `offre` (`id_offre`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_paiement_devis` FOREIGN KEY (`id_devis`) REFERENCES `devis` (`id_devis`) ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `contrat`
  ADD CONSTRAINT `fk_contrat_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_contrat_categorie` FOREIGN KEY (`id_categorie`) REFERENCES `categorie` (`id_categorie`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_contrat_formule` FOREIGN KEY (`id_formule`) REFERENCES `formule` (`id_formule`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_contrat_devis` FOREIGN KEY (`id_devis`) REFERENCES `devis` (`id_devis`) ON DELETE SET NULL;

ALTER TABLE `sinistre`
  ADD CONSTRAINT `fk_sinistre_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_sinistre_contrat` FOREIGN KEY (`id_contrat`) REFERENCES `contrat` (`id_contrat`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sinistre_agence` FOREIGN KEY (`id_agence`) REFERENCES `agence` (`id_agence`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_sinistre_agent` FOREIGN KEY (`id_agent_assigne`) REFERENCES `user` (`id_user`) ON DELETE SET NULL;

ALTER TABLE `traitement`
  ADD CONSTRAINT `fk_traitement_sinistre` FOREIGN KEY (`id_sinistre`) REFERENCES `sinistre` (`id_sinistre`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_traitement_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_traitement_valideur` FOREIGN KEY (`valide_par`) REFERENCES `user` (`id_user`) ON DELETE SET NULL;

ALTER TABLE `fraud_analysis`
  ADD CONSTRAINT `fk_fraud_sinistre` FOREIGN KEY (`id_sinistre`) REFERENCES `sinistre` (`id_sinistre`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_fraud_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

ALTER TABLE `reclamation`
  ADD CONSTRAINT `fk_reclamation_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL;

ALTER TABLE `reponse`
  ADD CONSTRAINT `fk_reponse_reclamation` FOREIGN KEY (`reclamation_id`) REFERENCES `reclamation` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reponse_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE SET NULL;

ALTER TABLE `poste`
  ADD CONSTRAINT `fk_poste_agence` FOREIGN KEY (`id_agence`) REFERENCES `agence` (`id_agence`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `commentaire`
  ADD CONSTRAINT `fk_commentaire_parent` FOREIGN KEY (`id_commentaire_parent`) REFERENCES `commentaire` (`id_commentaire`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_commentaire_poste` FOREIGN KEY (`id_poste`) REFERENCES `poste` (`id_poste`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_commentaire_user` FOREIGN KEY (`id_client`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `avis_agence`
  ADD CONSTRAINT `fk_avis_agence` FOREIGN KEY (`id_agence`) REFERENCES `agence` (`id_agence`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_avis_user` FOREIGN KEY (`id_client`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `like_post`
  ADD CONSTRAINT `fk_like_poste` FOREIGN KEY (`id_poste`) REFERENCES `poste` (`id_poste`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_like_user` FOREIGN KEY (`id_client`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

ALTER TABLE `otp_codes`
  ADD CONSTRAINT `otp_codes_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

ALTER TABLE `conversations`
  ADD CONSTRAINT `conversations_ibfk_1` FOREIGN KEY (`cree_par`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

ALTER TABLE `conversation_participants`
  ADD CONSTRAINT `conversation_participants_ibfk_1` FOREIGN KEY (`id_conversation`) REFERENCES `conversations` (`id_conversation`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversation_participants_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

ALTER TABLE `messages_admin`
  ADD CONSTRAINT `messages_admin_ibfk_1` FOREIGN KEY (`id_conversation`) REFERENCES `conversations` (`id_conversation`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_admin_ibfk_2` FOREIGN KEY (`id_expediteur`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

ALTER TABLE `message_mentions`
  ADD CONSTRAINT `message_mentions_ibfk_1` FOREIGN KEY (`id_message`) REFERENCES `messages_admin` (`id_message`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_mentions_ibfk_2` FOREIGN KEY (`id_user_mentionne`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

ALTER TABLE `friendships`
  ADD CONSTRAINT `friendships_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `friendships_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

ALTER TABLE `sos_alerts`
  ADD CONSTRAINT `sos_alerts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

ALTER TABLE `jeu_snake`
  ADD CONSTRAINT `fk_snake_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

ALTER TABLE `jeu_memory`
  ADD CONSTRAINT `fk_memory_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;

-- =================================================================
-- DONNÉES DE TEST (depuis assurance (12).sql)
-- Mot de passe bcrypt pour tous les comptes test : Azerty123!
-- =================================================================

-- agences
INSERT IGNORE INTO `agence` (`id_agence`, `nom_agence`, `pays`, `tel`, `email`, `statut`) VALUES
(1, 'Protex Tunis',   'Tunisie', '71111222', 'tunisia@protex.tn', 'active'),
(2, 'Protex Sfax',    'Tunisie', '74444670', 'sfax@protex.tn',    'active'),
(3, 'Protex Sousse',  'Tunisie', '73333444', 'sousse@protex.tn',  'active'),
(9, 'Protex Nabeul',  'Tunisie', '44554541', 'nabeul@protex.tn',  'active');

-- catégories
INSERT IGNORE INTO `categorie` (`id_categorie`, `nom_categorie`) VALUES
(2,  'Auto'),
(3,  'Habitation'),
(4,  'Santé'),
(5,  'Protection');

-- utilisateurs de test
INSERT IGNORE INTO `user` (`id_user`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`, `statut`, `id_agence`) VALUES
(101, 'Miledi',   'Karim',   'medkarimmiledi@gmail.com',     '$2y$10$bQLujgpoVCSaqgtTX2u3MegKrTOLZgjd20QdOM.StnNzf8Ygz0uum', 'superadmin', 'actif', 1),
(102, 'Admin',    'Agent',   'medkarimmiledi123@gmail.com',  '$2y$10$bQLujgpoVCSaqgtTX2u3MegKrTOLZgjd20QdOM.StnNzf8Ygz0uum', 'admin',      'actif', 1),
(103, 'Muledi',   'Agent',   'muledikarim@gmail.com',        '$2y$10$bQLujgpoVCSaqgtTX2u3MegKrTOLZgjd20QdOM.StnNzf8Ygz0uum', 'agent',      'actif', 1),
(104, 'Sans',     'Bibi',    'sansbibi@gmail.com',           '$2y$10$bQLujgpoVCSaqgtTX2u3MegKrTOLZgjd20QdOM.StnNzf8Ygz0uum', 'client',     'actif', 1),
(105, 'sabrine',  'mchabet', 'mchabetsabrine319@gmail.com',  '$2y$10$ya3BuQ.5409Sb9OR5Z1OFuyZWHQ.mggGuNSp2QPnlRoyXke74W51u', 'client',     'actif', NULL);

INSERT IGNORE INTO `admin`  (`id_user`, `niveau_acces`, `id_agence`) VALUES (101, 'superadmin', NULL), (102, 'admin_agence', 1);
INSERT IGNORE INTO `agent`  (`id_user`, `id_agence`) VALUES (103, 1);
INSERT IGNORE INTO `client` (`id_user`, `numero_client`, `id_agence`) VALUES (104, 'CL-TEST-01', 1), (105, 'CL-10771', NULL);

-- offres
INSERT IGNORE INTO `offre` (`id_offre`, `nom_offre`, `type_offre`, `prix_mensuel`, `prix_annuel`, `statut`) VALUES
(1, 'Auto Premium Plus', 'auto',       186.000, 15963.000, 'active'),
(2, 'Auto Standard',     'auto',       123.000, 12345.000, 'active'),
(4, 'Habitation Eco',    'habitation',  66.000,   600.000, 'suspendue'),
(5, 'Auto Validation',   'auto',       123.000,  1234.000, 'archivee');

-- devis de test
INSERT IGNORE INTO `devis` (`id_devis`, `nom`, `prenom`, `email`, `telephone`, `type_assurance`, `id_offre`, `statut`) VALUES
(10, 'sadok',   'berrzouga', 'sadokberrzouga123@gmail.com', '29527030', 'auto',       1, 'refuse'),
(12, 'ben salah','ali',      'sadokberrzouga123@gmail.com', '12345678', 'habitation', 4, 'accepte'),
(14, 'sami',    'mabrouki',  'samimabrouki@gg.com',         '58984568', 'auto',       1, 'accepte');

-- contrats de test
INSERT IGNORE INTO `contrat` (`id_contrat`, `numero_contrat`, `type_contrat`, `date_debut_contrat`, `date_fin_contrat`, `prime_contrat`, `statut_contrat`, `id_user`, `id_categorie`, `formule_contrat`) VALUES
(100, 'CNT-TEST-001',    NULL,         '2025-01-01', NULL,         NULL,   'actif',      104, NULL, NULL),
(101, 'CTR-2026-251001', 'Auto',       '2026-05-10', '2027-05-10', 55.00,  'en attente', 104, 2,    'Tous risques'),
(102, 'CTR-2026-250727', 'Protection', '2026-05-11', '2027-05-11', 120.00, 'en attente', 105, 5,    'Premium plus');

-- sinistres de test
INSERT IGNORE INTO `sinistre` (`id_sinistre`, `id_contrat`, `id_user`, `type`, `description`, `date_declaration`, `statut`) VALUES
(184, NULL, 24,  'Accident auto', 'Sinistre test', '2026-05-10', 'refuse'),
(201, 101,  104, 'Accident auto', 'bhgfwuejif',    '2026-05-10', 'refuse');

-- roulette gains de test
INSERT IGNORE INTO `roulette_gains` (`id_gain`, `email`, `nom`, `prenom`, `cadeau_label`, `code_promo`, `valeur_reduction`, `type_recompense`) VALUES
(1, 'sadokberrzouga123@gmail.com', 'test', 'mail', '-10% sur votre prochaine cotisation', 'PROTEX-SPIN-F8387C', 10.000, 'reduction_pct'),
(2, 'sadokberrzouga580@gmail.com', 'test', 'mail', '-10% sur votre prochaine cotisation', 'PROTEX-SPIN-46EE3F', 10.000, 'reduction_pct');

-- =================================================================
-- NORMALISATION STATUTS (migration 20260511)
-- =================================================================
UPDATE `reclamation` SET `statut` = 'closed' WHERE `statut` IN ('resolue', 'résolue');
UPDATE `reclamation` SET `statut` = 'open'
  WHERE `statut` IS NULL OR `statut` = '' OR `statut` NOT IN ('open', 'closed', 'rejected');
UPDATE `reponse` SET `statut` = 'envoyee' WHERE `statut` IS NULL OR `statut` = '';
UPDATE `reponse` SET `statut` = 'rejetee' WHERE `statut` IN ('rejected', 'rejetee', 'rejetée');
UPDATE `reponse` SET `statut` = 'envoyee' WHERE `statut` NOT IN ('envoyee', 'rejetee');

-- =================================================================
-- FIN
-- =================================================================
SET FOREIGN_KEY_CHECKS = 1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- =================================================================
-- COMPTES DE CONNEXION (rappel)
-- -----------------------------------------------------------------
-- SuperAdmin : medkarimmiledi@gmail.com     / Azerty123!
-- Admin      : medkarimmiledi123@gmail.com  / Azerty123!
-- Agent      : muledikarim@gmail.com        / Azerty123!
-- Client 1   : sansbibi@gmail.com           / Azerty123!
-- Client 2   : mchabetsabrine319@gmail.com  / (mot de passe original)
-- =================================================================

-- =================================================================
-- CATÉGORIES (assurance) — ajoutées pour fixer "Aucune catégorie trouvée"
-- =================================================================
INSERT IGNORE INTO categorie (nom_categorie, description_categorie) VALUES
  ('Auto',       'Assurance véhicule (responsabilité civile, tous risques, vol, incendie)'),
  ('Habitation', 'Assurance multirisque habitation (logement, mobilier, responsabilité)'),
  ('Santé',      'Assurance santé complémentaire et hospitalisation'),
  ('Protection', 'Assurance protection juridique et garantie accidents de la vie');

-- =================================================================
-- CONTRATS DE DÉMONSTRATION (à adapter aux IDs offres présentes)
-- ATTENTION : contrat.id_categorie référence en réalité offre.id_offre (FK historique)
-- =================================================================
-- Exemple générique commenté (à activer après vérification des id_offre/id_user présents) :
-- INSERT INTO contrat (numero_contrat, type_contrat, date_debut_contrat, date_fin_contrat,
--   prime_contrat, franchise_contrat, statut_contrat, id_client, id_categorie, notes) VALUES
--   ('CTR-DEMO-001','mensuel','2026-01-01','2027-01-01',850.00,200.00,'actif', 1, 1, 'Démo');
