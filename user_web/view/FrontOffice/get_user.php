<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Non connecté"]);
    exit;
}

require_once __DIR__ . '/../../controller/Client_Con.php';

try {
    $controller = new UserController();
    $user = $controller->getClientProfile($_SESSION['user_id']);

    if (!$user) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Utilisateur introuvable"]);
        exit;
    }

    echo json_encode($user);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
