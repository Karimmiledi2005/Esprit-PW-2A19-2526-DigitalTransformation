-- ============================================================
-- Migration 002: Système de messagerie interne BackOffice
-- Adapté à la structure existante (table user)
-- ============================================================

-- 1. Table des conversations (groupes ou 1-on-1)
CREATE TABLE IF NOT EXISTS conversations (
    id_conversation INT AUTO_INCREMENT PRIMARY KEY,
    nom             VARCHAR(200) NULL COMMENT 'NULL = conversation privée',
    type            ENUM('prive', 'groupe') DEFAULT 'prive',
    cree_par        INT NOT NULL,
    date_creation   DATETIME DEFAULT CURRENT_TIMESTAMP,
    derniere_activite DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_derniere (derniere_activite DESC),
    FOREIGN KEY (cree_par) REFERENCES user(id_user) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Participants d'une conversation
CREATE TABLE IF NOT EXISTS conversation_participants (
    id_conversation INT NOT NULL,
    id_user         INT NOT NULL,
    dernier_message_lu DATETIME NULL,
    PRIMARY KEY (id_conversation, id_user),
    FOREIGN KEY (id_conversation) REFERENCES conversations(id_conversation) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES user(id_user) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Table des messages
CREATE TABLE IF NOT EXISTS messages_admin (
    id_message      INT AUTO_INCREMENT PRIMARY KEY,
    id_conversation INT NOT NULL,
    id_expediteur   INT NOT NULL,
    contenu         TEXT NOT NULL,
    type_message    ENUM('texte', 'mention', 'fichier', 'systeme') DEFAULT 'texte',
    est_lu          TINYINT(1) DEFAULT 0,
    date_envoi      DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_conv_date (id_conversation, date_envoi DESC),
    INDEX idx_expediteur (id_expediteur),
    FOREIGN KEY (id_conversation) REFERENCES conversations(id_conversation) ON DELETE CASCADE,
    FOREIGN KEY (id_expediteur) REFERENCES user(id_user) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Mentions (appels) — un admin "appelle" un autre dans un message
CREATE TABLE IF NOT EXISTS message_mentions (
    id_mention      INT AUTO_INCREMENT PRIMARY KEY,
    id_message      INT NOT NULL,
    id_user_mentionne INT NOT NULL,
    est_resolu      TINYINT(1) DEFAULT 0 COMMENT '0 = appel en cours, 1 = traité',
    date_mention    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_message) REFERENCES messages_admin(id_message) ON DELETE CASCADE,
    FOREIGN KEY (id_user_mentionne) REFERENCES user(id_user) ON DELETE CASCADE,
    INDEX idx_user_non_resolu (id_user_mentionne, est_resolu)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
