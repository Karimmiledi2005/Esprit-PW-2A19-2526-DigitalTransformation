-- =========================================================================
-- PATCH: Tables manquantes (formule, garanties, notifs, etc.) — pour ton SQL
-- Exécuter APRÈS import de assurance (9).sql
-- =========================================================================
SET FOREIGN_KEY_CHECKS = 0;

-- ============ CATÉGORIES (IDs alignés avec les formules camarades) ============
-- Auto=2, Habitation=3, Santé=4, Protection=5
DELETE FROM categorie WHERE id_categorie IN (1,2,3,4,5);
INSERT INTO categorie (id_categorie, nom_categorie, description_categorie) VALUES
  (2, 'Auto',       'Assurance automobile et mobilité'),
  (3, 'Habitation', 'Protection du logement et du patrimoine'),
  (4, 'Santé',      'Couverture santé et assistance médicale'),
  (5, 'Protection', 'Prévoyance, sécurité et assistance');

-- ---- TABLE: poste ----
DROP TABLE IF EXISTS `poste`;
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

-- ---- TABLE: avis_agence ----
DROP TABLE IF EXISTS `avis_agence`;
CREATE TABLE `avis_agence` (
  `id_avis` int(11) NOT NULL,
  `note` int(11) NOT NULL CHECK (`note` between 1 and 5),
  `commentaire` text NOT NULL,
  `date_avis` datetime NOT NULL DEFAULT current_timestamp(),
  `id_agence` int(11) NOT NULL,
  `id_client` int(11) NOT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---- TABLE: commentaire ----
DROP TABLE IF EXISTS `commentaire`;
CREATE TABLE `commentaire` (
  `id_commentaire` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `date_commentaire` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_poste` int(11) NOT NULL,
  `id_client` int(11) NOT NULL,
  `id_commentaire_parent` int(11) DEFAULT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---- TABLE: like_post ----
DROP TABLE IF EXISTS `like_post`;
CREATE TABLE `like_post` (
  `id_like` int(11) NOT NULL,
  `id_poste` int(11) NOT NULL,
  `id_client` int(11) NOT NULL,
  `date_like` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---- TABLE: notification ----
DROP TABLE IF EXISTS `notification`;
CREATE TABLE `notification` (
  `id_notification` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) NOT NULL DEFAULT 'info',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_read` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---- TABLE: password_resets ----
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---- TABLE: sms_alerts ----
DROP TABLE IF EXISTS `sms_alerts`;
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

-- ---- TABLE: formule ----
DROP TABLE IF EXISTS `formule`;
CREATE TABLE `formule` (
  `id_formule` int(11) NOT NULL,
  `nom_formule` varchar(100) NOT NULL,
  `description_formule` text DEFAULT NULL,
  `prix_formule` decimal(10,2) DEFAULT 0.00,
  `franchise_formule` decimal(10,2) NOT NULL DEFAULT 0.00,
  `id_categorie` int(11) NOT NULL,
  `niveau_formule` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---- TABLE: formule_garantie ----
DROP TABLE IF EXISTS `formule_garantie`;
CREATE TABLE `formule_garantie` (
  `id_formule` int(11) NOT NULL,
  `id_garantie` int(11) NOT NULL,
  `niveau_couvert_garantie` enum('basique','option','non disponible') NOT NULL DEFAULT 'basique'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---- TABLE: audit_reclamation ----
DROP TABLE IF EXISTS `audit_reclamation`;
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

-- ---- TABLE: reponse_history ----
DROP TABLE IF EXISTS `reponse_history`;
CREATE TABLE `reponse_history` (
  `id` int(10) UNSIGNED NOT NULL,
  `reponse_id` int(10) UNSIGNED NOT NULL,
  `ancien_contenu` text NOT NULL,
  `modifie_par` int(10) UNSIGNED DEFAULT NULL,
  `modifie_le` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ---- TABLE: recommandation_historique ----
DROP TABLE IF EXISTS `recommandation_historique`;
CREATE TABLE `recommandation_historique` (
  `id_recommandation` int(11) NOT NULL,
  `id_user` int(11) DEFAULT NULL,
  `besoin` varchar(100) DEFAULT NULL,
  `budget` varchar(50) DEFAULT NULL,
  `profil_risque` varchar(50) DEFAULT NULL,
  `id_formule_recommandee` int(11) DEFAULT NULL,
  `date_recommandation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============ INDEX + AUTO_INCREMENT ============
ALTER TABLE `poste`
  ADD PRIMARY KEY (`id_poste`),
  ADD KEY `fk_poste_agence` (`id_agence`);
ALTER TABLE `poste`
  MODIFY `id_poste` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
ALTER TABLE `poste`
  ADD CONSTRAINT `fk_poste_agence` FOREIGN KEY (`id_agence`) REFERENCES `agence` (`id_agence`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `avis_agence`
  ADD PRIMARY KEY (`id_avis`),
  ADD KEY `fk_avis_agence` (`id_agence`),
  ADD KEY `fk_avis_user` (`id_client`);
ALTER TABLE `avis_agence`
  MODIFY `id_avis` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;
ALTER TABLE `avis_agence`
  ADD CONSTRAINT `fk_avis_agence` FOREIGN KEY (`id_agence`) REFERENCES `agence` (`id_agence`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_avis_user` FOREIGN KEY (`id_client`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `commentaire`
  ADD PRIMARY KEY (`id_commentaire`),
  ADD KEY `fk_commentaire_poste` (`id_poste`),
  ADD KEY `fk_commentaire_user` (`id_client`),
  ADD KEY `fk_commentaire_parent` (`id_commentaire_parent`);
ALTER TABLE `commentaire`
  MODIFY `id_commentaire` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
ALTER TABLE `commentaire`
  ADD CONSTRAINT `fk_commentaire_parent` FOREIGN KEY (`id_commentaire_parent`) REFERENCES `commentaire` (`id_commentaire`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_commentaire_poste` FOREIGN KEY (`id_poste`) REFERENCES `poste` (`id_poste`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_commentaire_user` FOREIGN KEY (`id_client`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `like_post`
  ADD PRIMARY KEY (`id_like`),
  ADD UNIQUE KEY `unique_like` (`id_poste`,`id_client`),
  ADD KEY `fk_like_poste` (`id_poste`),
  ADD KEY `fk_like_user` (`id_client`);
ALTER TABLE `like_post`
  MODIFY `id_like` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
ALTER TABLE `like_post`
  ADD CONSTRAINT `fk_like_poste` FOREIGN KEY (`id_poste`) REFERENCES `poste` (`id_poste`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_like_user` FOREIGN KEY (`id_client`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id_notification`),
  ADD KEY `id_user` (`id_user`);
ALTER TABLE `notification`
  MODIFY `id_notification` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
ALTER TABLE `notification`
  ADD CONSTRAINT `notification_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE;
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `sms_alerts`
  ADD PRIMARY KEY (`id_alert`),
  ADD UNIQUE KEY `unique_sms_expiration` (`id_contrat`,`type_alert`);
ALTER TABLE `sms_alerts`
  MODIFY `id_alert` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
ALTER TABLE `formule`
  ADD PRIMARY KEY (`id_formule`),
  ADD KEY `fk_formule_categorie` (`id_categorie`);
ALTER TABLE `formule`
  MODIFY `id_formule` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
ALTER TABLE `formule`
  ADD CONSTRAINT `fk_formule_categorie` FOREIGN KEY (`id_categorie`) REFERENCES `categorie` (`id_categorie`) ON DELETE CASCADE;
ALTER TABLE `formule_garantie`
  ADD PRIMARY KEY (`id_formule`,`id_garantie`),
  ADD KEY `id_garantie` (`id_garantie`);
ALTER TABLE `formule_garantie`
  ADD CONSTRAINT `formule_garantie_ibfk_1` FOREIGN KEY (`id_formule`) REFERENCES `formule` (`id_formule`) ON DELETE CASCADE,
  ADD CONSTRAINT `formule_garantie_ibfk_2` FOREIGN KEY (`id_garantie`) REFERENCES `garantie` (`id_garantie`) ON DELETE CASCADE;
ALTER TABLE `audit_reclamation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rec` (`reclamation_id`),
  ADD KEY `idx_user` (`id_user`);
ALTER TABLE `audit_reclamation`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `reponse_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_rep` (`reponse_id`);
ALTER TABLE `reponse_history`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `recommandation_historique`
  ADD PRIMARY KEY (`id_recommandation`);
ALTER TABLE `recommandation_historique`
  MODIFY `id_recommandation` int(11) NOT NULL AUTO_INCREMENT;


-- ============ DATA INSERT ============

-- formule
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

-- formule_garantie
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

-- notification
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

SET FOREIGN_KEY_CHECKS = 1;
