-- ============================================================
-- Migration 005: Table des scores du jeu Memory
-- ============================================================

CREATE TABLE IF NOT EXISTS jeu_memory (
    id_jeu              INT AUTO_INCREMENT PRIMARY KEY,
    id_user             INT NOT NULL,
    temps               INT NOT NULL DEFAULT 0 COMMENT 'Temps en secondes',
    coups               INT NOT NULL DEFAULT 0,
    difficulte          VARCHAR(20) NOT NULL DEFAULT 'Facile',
    nb_paires           INT NOT NULL DEFAULT 6,
    date_jeu            DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (id_user),
    INDEX idx_temps (temps),
    INDEX idx_date (date_jeu),
    FOREIGN KEY (id_user) REFERENCES user(id_user) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
