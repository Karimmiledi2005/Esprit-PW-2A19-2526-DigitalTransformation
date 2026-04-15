-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 13, 2026 at 12:49 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `assurance`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_user` int(11) NOT NULL,
  `niveau_acces` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `agent`
--

CREATE TABLE `agent` (
  `id_user` int(11) NOT NULL,
  `agence` varchar(100) DEFAULT NULL,
  `salaire` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categorie`
--

CREATE TABLE `categorie` (
  `id_categorie` int(11) NOT NULL,
  `nom_categorie` varchar(100) NOT NULL,
  `description_categorie` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `id_user` int(11) NOT NULL,
  `numero_client` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contrat`
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
  `id_client` int(11) DEFAULT NULL,
  `id_categorie` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `garantie`
--

CREATE TABLE `garantie` (
  `id_garantie` int(11) NOT NULL,
  `nom_garantie` varchar(100) DEFAULT NULL,
  `description_garantie` text DEFAULT NULL,
  `plafond_couvert_garantie` decimal(10,2) DEFAULT NULL,
  `niveau_couvert_garantie` varchar(50) DEFAULT NULL,
  `contrat_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `offre`
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

-- --------------------------------------------------------

--
-- Table structure for table `paiement`
--

CREATE TABLE `paiement` (
  `id_paiement` int(11) NOT NULL,
  `id_offre` int(11) NOT NULL,
  `reference` varchar(20) NOT NULL,
  `montant` decimal(10,3) NOT NULL,
  `methode` enum('carte','virement','mobile') NOT NULL,
  `periodicite` enum('mensuel','annuel') NOT NULL,
  `statut` enum('en_attente','valide','refuse','rembourse') DEFAULT 'en_attente',
  `date_paiement` datetime DEFAULT current_timestamp(),
  `date_echeance` date DEFAULT NULL,
  `num_carte_masque` varchar(25) DEFAULT NULL,
  `recu_pdf` varchar(255) DEFAULT NULL,
  `motif_refus` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reclamation`
--

CREATE TABLE `reclamation` (
  `id_reclamation` int(11) NOT NULL,
  `titre` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `date_reclamation` date NOT NULL,
  `statut` enum('en_attente','en_cours','resolue') DEFAULT 'en_attente',
  `priorite` enum('faible','moyenne','haute') DEFAULT 'moyenne',
  `id_reponse` int(11) DEFAULT NULL,
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reponse`
--

CREATE TABLE `reponse` (
  `id_reponse` int(11) NOT NULL,
  `date_reponse` date NOT NULL,
  `contenu` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sinistre`
--

CREATE TABLE `sinistre` (
  `id_sinistre` int(11) NOT NULL,
  `id_contrat` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `type` enum('Accident auto','Incendie','Vol','Degat des eaux') NOT NULL,
  `description` text DEFAULT NULL,
  `photo_url` varchar(255) DEFAULT NULL,
  `date_declaration` date NOT NULL,
  `statut` enum('en_attente','rembourse','refuse') DEFAULT 'en_attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sinistre`
--

INSERT INTO `sinistre` (`id_sinistre`, `id_contrat`, `id_user`, `type`, `description`, `photo_url`, `date_declaration`, `statut`) VALUES
(7, 1199, 1, 'Accident auto', 'sadfvbqqaefd', NULL, '2026-04-13', 'refuse'),
(8, 1236, 1, 'Incendie', 'i got fucked', NULL, '2026-04-13', 'en_attente');

-- --------------------------------------------------------

--
-- Table structure for table `traitement`
--

CREATE TABLE `traitement` (
  `id_traitement` int(11) NOT NULL,
  `id_sinistre` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `decision` enum('en_attente','refuse','rembourse') NOT NULL DEFAULT 'en_attente',
  `montant_indemnise` decimal(10,2) DEFAULT NULL,
  `statut` enum('accepte','refuse','en_cours') DEFAULT 'en_cours',
  `date_traitement` date NOT NULL,
  `nom_agent` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `traitement`
--

INSERT INTO `traitement` (`id_traitement`, `id_sinistre`, `id_user`, `decision`, `montant_indemnise`, `statut`, `date_traitement`) VALUES
(2, 7868, 1, 'en_attente', 4676.00, 'en_cours', '2026-04-13'),
(3, 67857, 1, 'rembourse', 12344.00, 'en_cours', '2026-04-13');

-- --------------------------------------------------------

--
-- Table structure for table `user`
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
  `role` enum('admin','client','agent') NOT NULL,
  `statut` enum('actif','bloque') DEFAULT 'actif',
  `last_login` datetime DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_user`);

--
-- Indexes for table `agent`
--
ALTER TABLE `agent`
  ADD PRIMARY KEY (`id_user`);

--
-- Indexes for table `categorie`
--
ALTER TABLE `categorie`
  ADD PRIMARY KEY (`id_categorie`),
  ADD UNIQUE KEY `nom_categorie` (`nom_categorie`);

--
-- Indexes for table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`id_user`);

--
-- Indexes for table `contrat`
--
ALTER TABLE `contrat`
  ADD PRIMARY KEY (`id_contrat`),
  ADD KEY `fk_contrat_categorie2` (`id_categorie`),
  ADD KEY `fk_contrat_client2` (`id_client`);

--
-- Indexes for table `garantie`
--
ALTER TABLE `garantie`
  ADD PRIMARY KEY (`id_garantie`),
  ADD KEY `fk_garantie_contrat` (`contrat_id`);

--
-- Indexes for table `offre`
--
ALTER TABLE `offre`
  ADD PRIMARY KEY (`id_offre`);

--
-- Indexes for table `paiement`
--
ALTER TABLE `paiement`
  ADD PRIMARY KEY (`id_paiement`),
  ADD UNIQUE KEY `reference` (`reference`),
  ADD KEY `fk_paiement_offre` (`id_offre`);

--
-- Indexes for table `reclamation`
--
ALTER TABLE `reclamation`
  ADD PRIMARY KEY (`id_reclamation`),
  ADD KEY `id_reponse` (`id_reponse`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `reponse`
--
ALTER TABLE `reponse`
  ADD PRIMARY KEY (`id_reponse`);

--
-- Indexes for table `sinistre`
--
ALTER TABLE `sinistre`
  ADD PRIMARY KEY (`id_sinistre`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `traitement`
--
ALTER TABLE `traitement`
  ADD PRIMARY KEY (`id_traitement`),
  ADD KEY `id_sinistre` (`id_sinistre`),
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categorie`
--
ALTER TABLE `categorie`
  MODIFY `id_categorie` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contrat`
--
ALTER TABLE `contrat`
  MODIFY `id_contrat` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `garantie`
--
ALTER TABLE `garantie`
  MODIFY `id_garantie` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `offre`
--
ALTER TABLE `offre`
  MODIFY `id_offre` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `paiement`
--
ALTER TABLE `paiement`
  MODIFY `id_paiement` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reclamation`
--
ALTER TABLE `reclamation`
  MODIFY `id_reclamation` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reponse`
--
ALTER TABLE `reponse`
  MODIFY `id_reponse` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sinistre`
--
ALTER TABLE `sinistre`
  MODIFY `id_sinistre` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `traitement`
--
ALTER TABLE `traitement`
  MODIFY `id_traitement` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contrat`
--
ALTER TABLE `contrat`
  ADD CONSTRAINT `fk_contrat_categorie` FOREIGN KEY (`id_categorie`) REFERENCES `categorie` (`id_categorie`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_contrat_categorie2` FOREIGN KEY (`id_categorie`) REFERENCES `categorie` (`id_categorie`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_contrat_client2` FOREIGN KEY (`id_client`) REFERENCES `client` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `garantie`
--
ALTER TABLE `garantie`
  ADD CONSTRAINT `fk_garantie_contrat` FOREIGN KEY (`contrat_id`) REFERENCES `contrat` (`id_contrat`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `paiement`
--
ALTER TABLE `paiement`
  ADD CONSTRAINT `fk_paiement_offre` FOREIGN KEY (`id_offre`) REFERENCES `offre` (`id_offre`) ON UPDATE CASCADE;

--
-- Add nom_agent column to traitement (agent name from form)
--
ALTER TABLE `traitement` ADD COLUMN IF NOT EXISTS `nom_agent` varchar(150) DEFAULT NULL;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
