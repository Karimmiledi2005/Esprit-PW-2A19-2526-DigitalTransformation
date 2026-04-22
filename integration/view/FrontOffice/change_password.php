<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Non connecté"]);
    exit;
}

require_once __DIR__ . '/../../controller/Client_Con.php';

$raw  = file_get_contents("php://input");
$data = json_decode($raw, true);
if (empty($data)) $data = $_POST;

$ancien    = $data['ancien_mdp']   ?? $data['currentPassword'] ?? '';
$nouveau   = $data['nouveau_mdp'] ?? $data['newPassword']     ?? '';

try {
    $controller = new UserController();
    $result = $controller->changePassword($_SESSION['user_id'], $ancien, $nouveau);
    echo json_encode($result);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
