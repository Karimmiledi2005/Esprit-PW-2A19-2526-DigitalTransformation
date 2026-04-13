<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../connexion.php';
require_once __DIR__ . '/Client_Con.php';

try {
    $controller = new UserController();
    $users = $controller->getAllUsers();

    echo json_encode(["success" => true, "users" => $users]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}