<?php
class RoleHelper {

    public static function getRole(): string {
        return $_SESSION['role'] ?? 'client';
    }

    public static function getAgenceId(): ?int {
        return isset($_SESSION['id_agence']) ? (int)$_SESSION['id_agence'] : null;
    }

    public static function getUserId(): int {
        return (int)($_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 0);
    }

    public static function isSuperAdmin(): bool {
        return self::getRole() === 'superadmin';
    }

    public static function isAdminAgence(): bool {
        // Dans la base, c'est 'admin'
        return self::getRole() === 'admin';
    }

    public static function isAgent(): bool {
        return self::getRole() === 'agent';
    }

    // SINISTRE
    public static function canDeleteSinistre(): bool {
        return self::isSuperAdmin() || self::isAdminAgence();
    }

    public static function canModifySinistre(): bool {
        return self::isSuperAdmin() || self::isAdminAgence();
    }

    public static function canAssignSinistre(): bool {
        return self::isSuperAdmin() || self::isAdminAgence();
    }

    public static function canSeeFraudScore(): bool {
        return in_array(self::getRole(), ['superadmin', 'admin', 'agent']);
    }

    public static function canSeeFraudScoreGlobal(): bool {
        return self::isSuperAdmin();
    }

    public static function canExportSinistres(): bool {
        return self::isSuperAdmin() || self::isAdminAgence();
    }

    // TRAITEMENT
    public static function canDeleteTraitement(): bool {
        // Superadmin et Admin (Tableau mis à jour par demande utilisateur)
        return self::isSuperAdmin() || self::isAdminAgence();
    }

    public static function canValiderTraitement(): bool {
        // Superadmin et Admin (Tableau: Valider/approuver un traitement)
        return self::isSuperAdmin() || self::isAdminAgence();
    }

    public static function canOverrideDecision(): bool {
        // Uniquement Superadmin (Tableau: Override décision)
        return self::isSuperAdmin();
    }

    public static function canModifyTraitement(int $idAgentTraitement, bool $estValide = false): bool {
        // Superadmin et Admin peuvent toujours
        if (self::isSuperAdmin() || self::isAdminAgence()) return true;
        
        // Agent peut modifier SEULEMENT SI c'est le sien ET que ce n'est pas encore validé
        // (Tableau: Modifier un traitement -> agent check (si non validé))
        return self::isAgent() && $idAgentTraitement === self::getUserId() && !$estValide;
    }

    public static function canCreateTraitement(): bool {
        // Selon votre demande : L'agent ne peut pas ajouter
        return self::isSuperAdmin() || self::isAdminAgence();
    }

    public static function canSeeStatsAgence(): bool {
        // Superadmin et Admin (Tableau: Stats agence)
        return self::isSuperAdmin() || self::isAdminAgence();
    }

    public static function canSeeStatsGlobales(): bool {
        // Uniquement Superadmin (Tableau: Stats globales)
        return self::isSuperAdmin();
    }

    // RECLAMATION & REPONSE
    public static function canRepondreReclamation(): bool {
        return in_array(self::getRole(), ['superadmin', 'admin', 'agent']);
    }

    public static function canRejeterReclamation(): bool {
        return self::isSuperAdmin() || self::isAdminAgence();
    }

    public static function canModifierReponse(): bool {
        return self::isSuperAdmin() || self::isAdminAgence();
    }

    public static function canSupprimerReponse(): bool {
        return self::isSuperAdmin() || self::isAdminAgence();
    }

    public static function canVoirToutesAgences(): bool {
        return self::isSuperAdmin();
    }

    // Guard : bloque et retourne 403 JSON si rôle non autorisé
    public static function requireRole(array $rolesAutorises): void {
        if (!in_array(self::getRole(), $rolesAutorises)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
            exit;
        }
    }
}
