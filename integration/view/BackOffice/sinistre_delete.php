<?php
require_once __DIR__ . '/../../controller/SinistreController.php';

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