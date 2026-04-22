<?php
require_once __DIR__ . '/../../controller/TraitementController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Methode non autorisee.']);
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if (!$id) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'ID manquant.']);
    exit;
}

$controller = new TraitementController();
$result = $controller->update($id, $_POST);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result);
?>