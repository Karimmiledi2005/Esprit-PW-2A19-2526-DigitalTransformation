-- ============================================================
-- Migration 004: Table des scores du jeu Snake
-- ============================================================

CREATE TABLE IF NOT EXISTS jeu_snake (
    id_jeu              INT AUTO_INCREMENT PRIMARY KEY,
    id_user             INT NOT NULL,
    score               INT NOT NULL DEFAULT 0,
    vitesse             VARCHAR(20) NOT NULL DEFAULT 'Normal',
    duree_sec           INT NOT NULL DEFAULT 0,
    serpents_manges     INT NOT NULL DEFAULT 0,
    date_jeu            DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (id_user),
    INDEX idx_score (score),
    INDEX idx_date (date_jeu),
    FOREIGN KEY (id_user) REFERENCES user(id_user) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
