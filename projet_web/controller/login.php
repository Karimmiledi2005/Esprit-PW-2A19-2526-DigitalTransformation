<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

require_once __DIR__ . '/../connexion.php';
include (__DIR__ . '/../model/User.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Champs requis']);
    exit;
}

try {
    $db = config::getConnexion();

    $sql = "SELECT id_user, nom, prenom, email, mot_de_passe, role, statut
            FROM user
            WHERE email = :email
            LIMIT 1";

    $stmt = $db->prepare($sql);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Email incorrect']);
        exit;
    }

    if (!password_verify($password, $user['mot_de_passe'])) {
        echo json_encode(['success' => false, 'message' => 'Mot de passe incorrect']);
        exit;
    }

    if ($user['statut'] === 'bloque') {
        echo json_encode(['success' => false, 'message' => 'Compte bloqué']);
        exit;
    }

    $_SESSION['user_id'] = $user['id_user'];
    $_SESSION['nom'] = $user['nom'];
    $_SESSION['role'] = $user['role'];

    echo json_encode([
        'success' => true,
        'role' => $user['role']
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur serveur'
    ]);
}