<?php
session_start();

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['superadmin','admin','agent'])) {
    http_response_code(403);
    echo json_encode(["success"=>false,"message"=>"Accès refusé"]); exit;
}

header('Content-Type: application/json');
require_once __DIR__ . '/../../connexion.php';
require_once __DIR__ . '/../../controller/Client_Con.php';

try {
    $controller = new UserController();
    $page    = max(1,(int)($_GET['page']??1));
    $perPage = max(1,min(100,(int)($_GET['per_page']??20)));
    $users   = $controller->getAllUsers($page,$perPage);
    $total   = $controller->countAllUsers();
    echo json_encode(["success"=>true,"users"=>$users,"total"=>$total,"page"=>$page,"per_page"=>$perPage,"total_pages"=>ceil($total/$perPage)]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success"=>false,"message"=>$e->getMessage()]);
}
