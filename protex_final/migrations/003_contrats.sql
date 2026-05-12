-- ============================================================
-- Migration 003: Table des contrats
-- ============================================================

CREATE TABLE IF NOT EXISTS contrat (
    id_contrat          INT AUTO_INCREMENT PRIMARY KEY,
    reference           VARCHAR(50) NOT NULL UNIQUE,
    id_devis            INT NOT NULL,
    id_offre            INT NOT NULL,
    id_client           INT NULL COMMENT 'Lien vers user si client existe',
    periodicite         ENUM('mensuel', 'annuel') DEFAULT 'mensuel',
    montant             DECIMAL(10,3) NOT NULL,
    date_signature      DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_debut          DATE NOT NULL,
    date_fin            DATE NOT NULL,
    statut              ENUM('actif', 'expire', 'resilie', 'en_attente') DEFAULT 'actif',
    notes               TEXT NULL,
    date_creation       DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reference (reference),
    INDEX idx_devis (id_devis),
    INDEX idx_statut (statut),
    INDEX idx_date_fin (date_fin),
    FOREIGN KEY (id_devis) REFERENCES devis(id_devis) ON DELETE CASCADE,
    FOREIGN KEY (id_offre) REFERENCES offre(id_offre) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
