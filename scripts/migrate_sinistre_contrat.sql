-- Migration script to add id_contrat to sinistre table
ALTER TABLE sinistre 
  ADD COLUMN IF NOT EXISTS id_contrat INT NULL,
  ADD CONSTRAINT fk_sinistre_contrat 
    FOREIGN KEY (id_contrat) REFERENCES contrat(id_contrat) ON DELETE SET NULL;
