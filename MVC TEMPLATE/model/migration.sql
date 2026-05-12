-- Migration: add relational tables for likes, comments, reviews
-- Run this ONCE after importing the main SQL dump

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

CREATE TABLE IF NOT EXISTS `commentaire` (
  `id_commentaire` int(11) NOT NULL AUTO_INCREMENT,
  `contenu` text NOT NULL,
  `date_commentaire` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_poste` int(11) NOT NULL,
  `id_client` int(11) NOT NULL,
  `id_commentaire_parent` int(11) DEFAULT NULL,
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
  PRIMARY KEY (`id_avis`),
  KEY `fk_avis_agence` (`id_agence`),
  KEY `fk_avis_user` (`id_client`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `like_post`
  ADD CONSTRAINT `fk_like_poste` FOREIGN KEY (`id_poste`) REFERENCES `poste` (`id_poste`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_like_user` FOREIGN KEY (`id_client`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `commentaire`
  ADD CONSTRAINT `fk_commentaire_poste` FOREIGN KEY (`id_poste`) REFERENCES `poste` (`id_poste`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_commentaire_user` FOREIGN KEY (`id_client`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_commentaire_parent` FOREIGN KEY (`id_commentaire_parent`) REFERENCES `commentaire` (`id_commentaire`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `avis_agence`
  ADD CONSTRAINT `fk_avis_agence` FOREIGN KEY (`id_agence`) REFERENCES `agence` (`id_agence`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_avis_user` FOREIGN KEY (`id_client`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;
