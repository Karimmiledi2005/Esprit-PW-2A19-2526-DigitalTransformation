-- ============================================================
--  MIGRATION : Ajout de la colonne `email` dans la table reclamation
--  Exécuter une seule fois dans votre base de données MySQL
-- ============================================================

ALTER TABLE reclamation
  ADD COLUMN email VARCHAR(150) NOT NULL DEFAULT '' AFTER description;

-- Vérification (optionnel)
-- DESCRIBE reclamation;
