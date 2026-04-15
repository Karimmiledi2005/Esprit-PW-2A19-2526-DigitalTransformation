<?php
require_once __DIR__ . '/admin_guard.php'; // ✅ FIX: protection admin
header('Content-Type: application/json');

require_once __DIR__ . '/../../connexion.php';
require_once __DIR__ . '/../../controller/Client_Con.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Méthode non autorisée"]);
    exit;
}

$id_user = isset($_POST['id_user']) ? (int)$_POST['id_user'] : 0;

if (!$id_user) {
    echo json_encode(["success" => false, "message" => "ID manquant"]);
    exit;
}

// ✅ Empêcher l'admin de se supprimer lui-même
if ($id_user === (int)$_SESSION['user_id']) {
    echo json_encode(["success" => false, "message" => "Impossible de supprimer votre propre compte"]);
    exit;
}

try {
    $controller = new UserController();
    $controller->deleteUser($id_user);
    echo json_encode(["success" => true, "message" => "Utilisateur supprimé"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
