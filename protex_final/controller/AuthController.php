<?php
/**
 * controller/AuthController.php — Authentification unifiée Protex
 *
 * FIX P-12 : Écrit $_SESSION['role'] ET $_SESSION['user_role'] (compatibilité croisée)
 * FIX P-13 : Écrit $_SESSION['user_id'] ET $_SESSION['id_user'] (compatibilité croisée)
 * FIX P-14 : Utilise email + bcrypt (plus de connexion par nom/prénom sans mot de passe)
 *
 * Ce contrôleur remplace AuthController.php (mon dossier) et unifie avec Client_Con.php (camarades).
 * L'OTP reste optionnel (si la table otp_codes existe).
 */

if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../helpers/RoleHelper.php';

class AuthController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    // ----------------------------------------------------------------
    // LOGIN — POST email + password
    // ----------------------------------------------------------------
    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require_once __DIR__ . '/../view/BackOffice/connexion.php';
            return;
        }

        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($email) || empty($password)) {
            $err = 'Email et mot de passe requis';
            require_once __DIR__ . '/../view/BackOffice/connexion.php';
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "SELECT id_user, nom, prenom, email, mot_de_passe, role, statut, avatar_url
                 FROM user WHERE email = :email LIMIT 1"
            );
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($password, $user['mot_de_passe'] ?? '')) {
                $err = 'Email ou mot de passe incorrect';
                require_once __DIR__ . '/../view/BackOffice/connexion.php';
                return;
            }

            if ($user['statut'] === 'bloque') {
                $err = 'Compte bloqué. Contactez l\'administrateur.';
                require_once __DIR__ . '/../view/BackOffice/connexion.php';
                return;
            }

            // Mise à jour last_login
            $this->db->prepare("UPDATE user SET last_login = NOW() WHERE id_user = ?")
                     ->execute([$user['id_user']]);

            // Récupérer id_agence selon le rôle
            $idAgence = null;
            if ($user['role'] === 'admin') {
                $r = $this->db->prepare("SELECT id_agence FROM admin WHERE id_user = ?");
                $r->execute([$user['id_user']]);
                $idAgence = $r->fetchColumn() ?: null;
            } elseif ($user['role'] === 'agent') {
                $r = $this->db->prepare("SELECT id_agence FROM agent WHERE id_user = ?");
                $r->execute([$user['id_user']]);
                $idAgence = $r->fetchColumn() ?: null;
            }

            session_regenerate_id(true);
            $this->writeSession($user, $idAgence);

            // Redirection selon le rôle
            $base = defined('BASE_URL') ? BASE_URL : '';
            if (in_array($user['role'], ['superadmin', 'admin', 'agent'])) {
                header('Location: ' . $base . '/controller/DashboardController.php');
            } else {
                header('Location: ' . $base . '/view/FrontOffice/client.html');
            }
            exit;

        } catch (Exception $e) {
            error_log('[AuthController] login error: ' . $e->getMessage());
            $err = 'Erreur serveur. Veuillez réessayer.';
            require_once __DIR__ . '/../view/BackOffice/connexion.php';
        }
    }

    // ----------------------------------------------------------------
    // LOGOUT
    // ----------------------------------------------------------------
    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        $base = defined('BASE_URL') ? BASE_URL : '';
        header('Location: ' . $base . '/view/FrontOffice/login.html');
        exit;
    }

    // ----------------------------------------------------------------
    // Écriture unifiée des variables de session
    // FIX P-12 + P-13 : double écriture pour compatibilité croisée
    // ----------------------------------------------------------------
    private function writeSession(array $user, ?int $idAgence): void
    {
        $id     = (int)$user['id_user'];
        $nom    = $user['nom']    ?? '';
        $prenom = $user['prenom'] ?? '';
        $email  = $user['email']  ?? '';
        $role   = $user['role']   ?? 'client';
        $avatar = $user['avatar_url'] ?? 'default.png';

        // Clés camarades
        $_SESSION['user_id']   = $id;
        $_SESSION['id_user']   = $id;
        $_SESSION['role']      = $role;
        $_SESSION['nom']       = $nom;
        $_SESSION['prenom']    = $prenom;
        $_SESSION['id_agence'] = $idAgence;

        // Clés mon dossier (compatibilité navbar, sidebar, controllers)
        $_SESSION['user_nom']    = $nom;
        $_SESSION['user_prenom'] = $prenom;
        $_SESSION['user_email']  = $email;
        $_SESSION['user_role']   = $role;
        $_SESSION['user_avatar'] = $avatar;
    }
}

// ----------------------------------------------------------------
// Point d'entrée
// ----------------------------------------------------------------
require_once __DIR__ . '/../config.php';

$controller = new AuthController();
$action = $_GET['action'] ?? 'login';

match ($action) {
    'logout' => $controller->logout(),
    default  => $controller->login(),
};

