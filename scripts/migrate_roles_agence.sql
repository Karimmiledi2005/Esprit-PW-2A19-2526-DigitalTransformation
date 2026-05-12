ALTER TABLE user 
  ADD COLUMN IF NOT EXISTS id_agence INT NULL,
  ADD COLUMN IF NOT EXISTS role ENUM('superadmin','admin','admin_agence','agent','client') DEFAULT 'client';

ALTER TABLE sinistre
  ADD COLUMN IF NOT EXISTS id_agence INT NULL,
  ADD COLUMN IF NOT EXISTS id_agent_assigne INT NULL,
  ADD CONSTRAINT fk_sinistre_agence FOREIGN KEY (id_agence) REFERENCES agence(id_agence) ON DELETE SET NULL,
  ADD CONSTRAINT fk_sinistre_agent FOREIGN KEY (id_agent_assigne) REFERENCES user(id_user) ON DELETE SET NULL;

ALTER TABLE traitement
  ADD COLUMN IF NOT EXISTS valide_par INT NULL,
  ADD COLUMN IF NOT EXISTS date_validation DATETIME NULL,
  ADD COLUMN IF NOT EXISTS est_valide TINYINT(1) DEFAULT 0,
  ADD CONSTRAINT fk_traitement_valideur FOREIGN KEY (valide_par) REFERENCES user(id_user) ON DELETE SET NULL;
