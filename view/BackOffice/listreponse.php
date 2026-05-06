<?php
/**
 * listreponse.php
 * GET  → JSON { success, rows[], stats }
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../controller/ReponseController.php';

try {
    $ctrl = new ReponseController();
    $rows = $ctrl->listAllReclamations();

    echo json_encode([
        'success' => true,
        'rows'    => $rows,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
