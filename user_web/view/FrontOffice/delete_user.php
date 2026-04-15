<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../controller/Client_Con.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Non connecté"]);
    exit;
}

// ✅ Protection CSRF : vérifier le token
$raw  = file_get_contents("php://input");
$data = json_decode($raw, true) ?: $_POST;

if (empty($data['csrf_token']) || $data['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Token invalide (CSRF)"]);
    exit;
}

$id_user = $_SESSION['user_id'];

try {
    $controller = new UserController();
    $controller->deleteUser($id_user);

    session_destroy();

    echo json_encode(["success" => true, "message" => "Compte supprimé"]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
