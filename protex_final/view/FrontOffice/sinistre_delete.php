<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id_user']) && !isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Utilisateur non connecté.']);
    exit;
}

require_once __DIR__ . '/../../bootstrap.php';

$userId = (int)($_SESSION['id_user'] ?? $_SESSION['user_id']);

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if (!$id) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'ID manquant.']);
    exit;
}

$controller = new SinistreController();
$result = $controller->delete($id);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result);
?>
