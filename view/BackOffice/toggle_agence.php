<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'superadmin') {
    http_response_code(403);
    echo json_encode(["success"=>false,"message"=>"Réservé au SuperAdmin"]); exit;
}
header('Content-Type: application/json');
require_once __DIR__ . '/../../connexion.php';
require_once __DIR__ . '/../../controller/Client_Con.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success"=>false,"message"=>"Méthode non autorisée"]); exit;
}
$id = isset($_POST['id_agence']) ? (int)$_POST['id_agence'] : 0;
if (!$id) { echo json_encode(["success"=>false,"message"=>"ID manquant"]); exit; }
try {
    $nouveau = (new UserController())->toggleStatutAgence($id);
    echo json_encode(["success"=>true,"statut"=>$nouveau]);
} catch (Exception $e) {
    echo json_encode(["success"=>false,"message"=>$e->getMessage()]);
}
