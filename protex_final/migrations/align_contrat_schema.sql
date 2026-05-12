-- =========================================================================
-- ALIGN SCHEMA: aligner notre table `contrat` avec celle des camarades
-- pour que le CRUD FrontOffice/BackOffice fonctionne sans modification.
-- =========================================================================
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Ajouter les colonnes manquantes du schéma camarades
ALTER TABLE `contrat`
  ADD COLUMN IF NOT EXISTS `id_user`         INT(11)      DEFAULT NULL AFTER `id_client`,
  ADD COLUMN IF NOT EXISTS `formule_contrat` VARCHAR(100) DEFAULT NULL AFTER `id_categorie`,
  ADD COLUMN IF NOT EXISTS `details_contrat` TEXT         DEFAULT NULL AFTER `formule_contrat`,
  ADD COLUMN IF NOT EXISTS `id_formule`      INT(11)      DEFAULT NULL AFTER `details_contrat`;

-- 2. Recopier id_client → id_user (compatibilité)
UPDATE `contrat` SET `id_user` = `id_client` WHERE `id_user` IS NULL AND `id_client` IS NOT NULL;

-- 3. Supprimer la FK foireuse (id_categorie pointe vers offre — INCORRECT)
ALTER TABLE `contrat` DROP FOREIGN KEY `fk_contrat_offre`;

-- 4. Ajouter la BONNE FK (id_categorie → categorie.id_categorie)
ALTER TABLE `contrat`
  ADD CONSTRAINT `fk_contrat_categorie`
  FOREIGN KEY (`id_categorie`) REFERENCES `categorie` (`id_categorie`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- 5. Ajouter FK id_user → user
ALTER TABLE `contrat`
  ADD CONSTRAINT `fk_contrat_user`
  FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`)
  ON DELETE CASCADE ON UPDATE CASCADE;

-- 6. Ajouter FK id_formule → formule
ALTER TABLE `contrat`
  ADD CONSTRAINT `fk_contrat_formule`
  FOREIGN KEY (`id_formule`) REFERENCES `formule` (`id_formule`)
  ON DELETE SET NULL ON UPDATE CASCADE;

-- 7. Réaligner les contrats déjà créés (id_categorie pointait vers offre,
--    on remappe vers la catégorie correspondante par nom)
UPDATE `contrat` c
JOIN `offre` o ON o.id_offre = c.id_categorie
JOIN `categorie` cat ON LOWER(cat.nom_categorie) = LOWER(o.type_offre)
SET c.id_categorie = cat.id_categorie
WHERE EXISTS (SELECT 1 FROM offre WHERE id_offre = c.id_categorie);

SET FOREIGN_KEY_CHECKS = 1;

SELECT 'Migration terminée. Structure contrat :' AS msg;
SHOW CREATE TABLE contrat\G

-- =========================================================================
-- ALIGN: table reclamation — schéma exact des camarades
-- =========================================================================
DROP TABLE IF EXISTS reclamation;
CREATE TABLE reclamation (
  id int(11) NOT NULL AUTO_INCREMENT,
  objet varchar(100) DEFAULT NULL,
  type varchar(50) DEFAULT NULL,
  ref_contrat varchar(100) DEFAULT NULL,
  priorite varchar(50) DEFAULT NULL,
  statut varchar(50) DEFAULT NULL,
  date_depot date DEFAULT NULL,
  rec_ref varchar(100) DEFAULT NULL,
  description text DEFAULT NULL,
  email varchar(150) NOT NULL DEFAULT '',
  id_user int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_statut (statut),
  KEY idx_priorite (priorite),
  KEY idx_date (date_depot),
  KEY idx_user_rel (id_user),
  CONSTRAINT fk_reclamation_user FOREIGN KEY (id_user) REFERENCES user (id_user) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
