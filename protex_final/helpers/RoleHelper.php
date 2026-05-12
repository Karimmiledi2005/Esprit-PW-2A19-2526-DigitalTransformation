<?php
/**
 * helpers/RoleHelper.php
 * FIX P-12 : Lit $_{SESSION['role'] ET $_SESSION['user_role'] (compatibilité des deux systèmes)
 * FIX P-13 : Lit $_SESSION['id_user'] ET $_SESSION['user_id'] (même pivôt)
 */
class RoleHelper {

    public static function getRole(): string {
        // Priorité : 'role' (camarades) ; fallback : 'user_role' (mon dossier)
        return $_SESSION['role'] ?? $_SESSION['user_role'] ?? 'client';
    }

    public static function getAgenceId(): ?int {
        return isset($_SESSION['id_agence']) ? (int)$_SESSION['id_agence'] : null;
    }

    public static function getUserId(): int {
        // Accepte les deux clés — $_SESSION['user_id'] écrite par Client_Con.php ET AuthController.php
        return (int)($_SESSION['user_id'] ?? $_SESSION['id_user'] ?? 0);
    }

    public static function isSuperAdmin(): bool {
        return self::getRole() === 'superadmin';
    }

    public static function isAdminAgence(): bool {
        return self::getRole() === 'admin';
    }

    public static function isAgent(): bool {
        return self::getRole() === 'agent';
    }

    public static function isClient(): bool {
        return self::getRole() === 'client';
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
        return self::isSuperAdmin() || self::isAdminAgence();
    }

    public static function canValiderTraitement(): bool {
        return self::isSuperAdmin() || self::isAdminAgence();
    }

    public static function canOverrideDecision(): bool {
        return self::isSuperAdmin();
    }

    public static function canModifyTraitement(int $idAgentTraitement, bool $estValide = false): bool {
        if (self::isSuperAdmin() || self::isAdminAgence()) return true;
        return self::isAgent() && $idAgentTraitement === self::getUserId() && !$estValide;
    }

    public static function canCreateTraitement(): bool {
        return self::isSuperAdmin() || self::isAdminAgence();
    }

    public static function canSeeStatsAgence(): bool {
        return self::isSuperAdmin() || self::isAdminAgence();
    }

    public static function canSeeStatsGlobales(): bool {
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

    // Guard : bloque avec 403 JSON si rôle non autorisé
    public static function requireRole(array $rolesAutorises): void {
        if (!in_array(self::getRole(), $rolesAutorises)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
            exit;
        }
    }

    // Guard : redirige si non connecté
    public static function requireAuth(string $loginUrl = null): void {
        if (!self::getUserId()) {
            if ($loginUrl === null) {
                $loginUrl = (defined('BASE_URL') ? BASE_URL : '') . '/view/FrontOffice/login.html';
            }
            header('Location: ' . $loginUrl);
            exit;
        }
    }
}
