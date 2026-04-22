<?php
require_once __DIR__ . '/../../controller/TraitementController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Methode non autorisee.']);
    exit;
}

$controller = new TraitementController();
$userId = 1; // Default for dev, or from session
$result = $controller->create($_POST, $userId);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result);
?>