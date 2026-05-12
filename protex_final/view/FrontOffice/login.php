<?php
// Bloquer toute sortie HTML avant le JSON
ini_set('display_errors', '0');
error_reporting(E_ALL);
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        exit;
    }

    require_once __DIR__ . '/../../controller/Client_Con.php';


    $email    = trim($_POST['email']    ?? '');
    $password =      $_POST['password'] ?? '';

    $controller = new UserController();
    $result     = $controller->login($email, $password);
    ob_clean(); // jeter toute sortie parasite (warnings/notices)
    echo json_encode($result);
} catch (Throwable $e) {
    ob_clean();
    http_response_code(500);
    error_log('login.php: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    echo json_encode(['success' => false, 'message' => 'Erreur serveur', 'debug' => $e->getMessage()]);
}
