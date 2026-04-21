<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../connexion.php';
require_once __DIR__ . '/../../controller/Client_Con.php';

try {
    $controller = new UserController();
    $stats = $controller->getStats();
    echo json_encode(['success' => true, 'stats' => $stats]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}