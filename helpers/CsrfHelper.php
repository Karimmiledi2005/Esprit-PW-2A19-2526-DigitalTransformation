<?php
/**
 * CsrfHelper.php
 * Gestion simple des jetons CSRF pour les actions BackOffice
 */
class CsrfHelper
{
    /**
     * Retourne le jeton CSRF stocké en session.
     */
    public static function getToken(): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return $_SESSION['csrf_token'] ?? '';
    }

    /**
     * Valide le jeton CSRF reçu via POST ou Header.
     * Arrête le script avec une erreur 403 en cas d'échec.
     */
    public static function validate(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        $stored = $_SESSION['csrf_token'] ?? '';

        if (empty($stored) || !hash_equals($stored, $token)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'Validation de sécurité (CSRF) échouée. Veuillez rafraîchir la page.'
            ]);
            exit;
        }
    }
}
