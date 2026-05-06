<?php
/**
 * controller/AuthController.php
 * Login simple — accepte n'importe quel nom/prénom pour tester
 * Crée l'utilisateur en BDD si inexistant pour que la messagerie marche
 */

if (session_status() === PHP_SESSION_NONE) session_start();

class AuthController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    public function login(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nom = trim($_POST['nom'] ?? '');
            $prenom = trim($_POST['prenom'] ?? '');
            $role = trim($_POST['role'] ?? 'admin');

            if (empty($nom) || empty($prenom)) {
                $err = 'Nom et prénom requis';
                require_once __DIR__ . '/../view/BackOffice/connexion.html';
                return;
            }

            $email = strtolower(preg_replace('/[^a-z0-9]/', '', $prenom)) . '.' . strtolower(preg_replace('/[^a-z0-9]/', '', $nom)) . '@protex.test';

            $stmt = $this->db->prepare("SELECT id_user, nom, prenom, email, role, statut, avatar FROM user WHERE email = :email");
            $stmt->execute([':email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                $insert = $this->db->prepare("
                    INSERT INTO user (nom, prenom, email, mot_de_passe, role, statut, last_login, date_creation)
                    VALUES (:nom, :prenom, :email, :pw, :role, 'actif', NOW(), NOW())
                ");
                $insert->execute([
                    ':nom' => $nom,
                    ':prenom' => $prenom,
                    ':email' => $email,
                    ':pw' => password_hash('test', PASSWORD_DEFAULT),
                    ':role' => $role,
                ]);
                $userId = (int)$this->db->lastInsertId();
                $user = ['id_user' => $userId, 'nom' => $nom, 'prenom' => $prenom, 'email' => $email, 'role' => $role, 'statut' => 'actif', 'avatar' => 'default.png'];
            } else {
                $update = $this->db->prepare("UPDATE user SET last_login = NOW() WHERE id_user = ?");
                $update->execute([(int)$user['id_user']]);
            }

            $_SESSION['user_id'] = (int)$user['id_user'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_prenom'] = $user['prenom'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_avatar'] = $user['avatar'] ?? 'default.png';

            header('Location: ../controller/DashboardController.php');
            exit;
        }

        require_once __DIR__ . '/../view/BackOffice/connexion.html';
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
        header('Location: ../view/BackOffice/connexion.html');
        exit;
    }
}

$action = $_GET['action'] ?? 'login';
$controller = new AuthController();

switch ($action) {
    case 'logout': $controller->logout(); break;
    default:       $controller->login();  break;
}
