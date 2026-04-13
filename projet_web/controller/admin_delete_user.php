<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../connexion.php';
require_once __DIR__ . '/Client_Con.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Méthode non autorisée"]);
    exit;
}

$id_user = isset($_POST['id_user']) ? (int)$_POST['id_user'] : 0;

if (!$id_user) {
    echo json_encode(["success" => false, "message" => "ID manquant"]);
    exit;
}

try {
    $controller = new UserController();
    $controller->deleteUser($id_user);
    echo json_encode(["success" => true, "message" => "Utilisateur supprimé"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}