<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
$nom   = trim($_POST['nom_agence'] ?? '');
$pays  = trim($_POST['pays']       ?? '');
$tel   = trim($_POST['tel']        ?? '');
$email = trim($_POST['email']      ?? '');
if (empty($nom)) { echo json_encode(["success"=>false,"message"=>"Nom d'agence obligatoire"]); exit; }
try {
    (new UserController())->addAgence($nom,$pays?:null,$tel?:null,$email?:null);
    echo json_encode(["success"=>true,"message"=>"Agence créée"]);
} catch (Exception $e) {
    echo json_encode(["success"=>false,"message"=>$e->getMessage()]);
}
