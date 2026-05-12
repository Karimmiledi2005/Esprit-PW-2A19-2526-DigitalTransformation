<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(["success"=>false,"message"=>"Non connecté"]); exit;
}
$role = strtolower($_SESSION['role']);
if (!in_array($role, ['superadmin','admin','agent'])) {
    http_response_code(403);
    echo json_encode(["success"=>false,"message"=>"Accès refusé (" . $role . ")"]); exit;
}

header('Content-Type: application/json');
require_once __DIR__ . '/../../connexion.php';
require_once __DIR__ . '/../../controller/Client_Con.php';

try {
    $controller = new UserController();
    $days = isset($_GET['days']) ? (int)$_GET['days'] : null;
    $stats = $controller->getAdvancedStats($days);
    echo json_encode(['success'=>true,'data'=>$stats]);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
