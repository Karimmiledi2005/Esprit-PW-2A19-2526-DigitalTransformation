<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

require_once __DIR__ . '/../../controller/Client_Con.php'; // ✅ FIX: utilise le contrôleur (MVC)

$email    = trim($_POST['email']    ?? '');
$password =      $_POST['password'] ?? '';

$controller = new UserController();
$result     = $controller->login($email, $password);
echo json_encode($result);
