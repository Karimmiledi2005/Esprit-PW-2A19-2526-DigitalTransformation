<?php
session_start();

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['superadmin','admin','agent'])) {
    http_response_code(403);
    echo json_encode(["success"=>false,"message"=>"Accès refusé"]);
    exit;
}

header('Content-Type: application/json');
require_once __DIR__ . '/../../connexion.php';
require_once __DIR__ . '/../../controller/Client_Con.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success"=>false,"message"=>"Méthode non autorisée"]); exit;
}

$id_user = isset($_POST['id_user']) ? (int)$_POST['id_user'] : 0;
if (!$id_user) { echo json_encode(["success"=>false,"message"=>"ID manquant"]); exit; }

try {
    // Sécurité supplémentaire : l'agent ne peut pas bloquer/débloquer
    if (($_SESSION['role']??'') === 'agent') {
        throw new Exception("Les agents n'ont pas la permission de modifier le statut.");
    }
    $controller = new UserController();
    $newStatut  = $controller->toggleStatutUser($id_user, $_POST['csrf_token'] ?? '');
    echo json_encode(["success"=>true,"statut"=>$newStatut]);
} catch (Exception $e) {
    echo json_encode(["success"=>false,"message"=>$e->getMessage()]);
}
