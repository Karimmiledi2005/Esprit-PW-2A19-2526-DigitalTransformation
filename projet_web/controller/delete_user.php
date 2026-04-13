<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . "/Client_Con.php";
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Non connecté"
    ]);
    exit;
}

$id_user = $_SESSION['user_id'];

$controller = new UserController();
$controller->deleteUser($id_user);

// option : supprimer session
session_destroy();

echo json_encode([
    "success" => true,
    "message" => "Compte supprimé"
]);