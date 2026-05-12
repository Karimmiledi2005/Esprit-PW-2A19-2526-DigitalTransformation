<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();
header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user_id']) && !isset($_SESSION['id_user'])) {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Non connecté"]);
        exit;
    }

    $id = $_SESSION['id_user'] ?? $_SESSION['user_id'];
    require_once __DIR__ . '/../../controller/Client_Con.php';

    $controller = new UserController();
    $user = $controller->getClientProfile((int)$id);

    if (!$user) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Utilisateur introuvable"]);
        exit;
    }

    echo json_encode(["success" => true, "user" => $user]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
