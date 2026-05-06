-- ============================================================
-- Migration: Lien Devis → Paiement
-- Ajoute id_devis dans la table paiement
-- ============================================================

-- 1. Ajouter la colonne id_devis si elle n'existe pas
ALTER TABLE paiement
  ADD COLUMN id_devis INT NULL DEFAULT NULL AFTER id_offre,
  ADD INDEX idx_id_devis (id_devis);

-- 2. (Optionnel) Ajouter la clé étrangère si les tables sont InnoDB
-- ALTER TABLE paiement
--   ADD CONSTRAINT fk_paiement_devis
--   FOREIGN KEY (id_devis) REFERENCES devis(id_devis)
--   ON DELETE SET NULL ON UPDATE CASCADE;

-- 3. Ajouter colonne code_promo si absente (pour roulette)
ALTER TABLE paiement
  ADD COLUMN code_promo VARCHAR(50) NULL DEFAULT NULL;

-- 4. Ajouter colonne date_utilisation dans roulette_gains si absente
ALTER TABLE roulette_gains
  ADD COLUMN date_utilisation DATETIME NULL DEFAULT NULL;
