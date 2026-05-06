-- ============================================================
--  MIGRATION ANTIFRAUD — Système Protex
--  À exécuter UNE SEULE FOIS dans votre base `assurance`
-- ============================================================

-- Table principale d'analyse de fraude
CREATE TABLE IF NOT EXISTS `fraud_analysis` (
  `id_fraud`            INT(11)        NOT NULL AUTO_INCREMENT,
  `id_sinistre`         INT(11)        NOT NULL,
  `id_user`             INT(11)        NOT NULL,
  `score_global`        TINYINT(3)     NOT NULL DEFAULT 0 COMMENT '0-100, plus haut = plus suspect',
  `niveau_risque`       ENUM('faible','moyen','eleve','critique') NOT NULL DEFAULT 'faible',
  `suggestion_ia`       ENUM('accepter','investiguer','refuser') NOT NULL DEFAULT 'investiguer',
  -- Scores détaillés par module
  `score_texte`         TINYINT(3)     DEFAULT 0  COMMENT 'Analyse sémantique description',
  `score_comportement`  TINYINT(3)     DEFAULT 0  COMMENT 'Historique sinistres utilisateur',
  `score_contrat`       TINYINT(3)     DEFAULT 0  COMMENT 'Ancienneté contrat / franchise',
  `score_image`         TINYINT(3)     DEFAULT 0  COMMENT 'Analyse photo (si disponible)',
  -- Flags binaires détectés
  `flag_description_vague`    TINYINT(1) DEFAULT 0,
  `flag_sinistres_multiples`  TINYINT(1) DEFAULT 0,
  `flag_contrat_recent`       TINYINT(1) DEFAULT 0,
  `flag_montant_eleve`        TINYINT(1) DEFAULT 0,
  `flag_image_suspecte`       TINYINT(1) DEFAULT 0,
  -- Détail texte IA
  `analyse_texte`       TEXT           DEFAULT NULL COMMENT 'Explication IA de l analyse textuelle',
  `analyse_comportement`TEXT           DEFAULT NULL,
  `analyse_image`       TEXT           DEFAULT NULL,
  `recommandation_ia`   TEXT           DEFAULT NULL COMMENT 'Recommandation complète pour l agent',
  -- Meta
  `date_analyse`        DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `version_modele`      VARCHAR(50)    DEFAULT 'claude-sonnet-4-20250514',
  PRIMARY KEY (`id_fraud`),
  UNIQUE KEY `uq_sinistre` (`id_sinistre`),
  KEY `fk_fraud_user` (`id_user`),
  KEY `idx_niveau_risque` (`niveau_risque`),
  KEY `idx_score_global` (`score_global`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
  COMMENT='Résultats d analyse antifraud IA par sinistre';

-- Contrainte FK vers sinistre
ALTER TABLE `fraud_analysis`
  ADD CONSTRAINT `fk_fraud_sinistre`
    FOREIGN KEY (`id_sinistre`) REFERENCES `sinistre` (`id_sinistre`)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- Vue pratique pour le backoffice (sinistre + analyse fusionnés)
CREATE OR REPLACE VIEW `v_sinistre_fraud` AS
  SELECT
    s.id_sinistre,
    s.id_contrat,
    s.id_user,
    s.type,
    s.description,
    s.photo_url,
    s.date_declaration,
    s.statut,
    CONCAT(u.prenom, ' ', u.nom) AS client_nom,
    fa.score_global,
    fa.niveau_risque,
    fa.suggestion_ia,
    fa.flag_description_vague,
    fa.flag_sinistres_multiples,
    fa.flag_contrat_recent,
    fa.flag_montant_eleve,
    fa.flag_image_suspecte,
    fa.recommandation_ia,
    fa.date_analyse
  FROM sinistre s
  LEFT JOIN user u          ON s.id_user    = u.id_user
  LEFT JOIN fraud_analysis fa ON s.id_sinistre = fa.id_sinistre;
