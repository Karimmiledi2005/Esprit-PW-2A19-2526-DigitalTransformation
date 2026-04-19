<?php
/**
 * deletereponse.php
 * POST rep_id → delete response & reset reclamation to open
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../controller/ReponseController.php';

try {
    $ctrl   = new ReponseController();
    $rep_id = (int)($_POST['rep_id'] ?? 0);

    if (!$rep_id) throw new Exception('ID réponse manquant.');

    $ctrl->deleteReponse($rep_id);

    echo json_encode(['success' => true, 'message' => 'Réponse supprimée avec succès.']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
