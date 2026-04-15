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
$data = json_decode($raw, true) ?: $_POST;

$ancienMdp  = $data['ancien_mdp']  ?? '';
$nouveauMdp = $data['nouveau_mdp'] ?? '';

if (empty($ancienMdp) || empty($nouveauMdp)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Champs requis"]);
    exit;
}

$controller = new UserController();
$result     = $controller->changePassword((int)$_SESSION['user_id'], $ancienMdp, $nouveauMdp);

echo json_encode($result);
