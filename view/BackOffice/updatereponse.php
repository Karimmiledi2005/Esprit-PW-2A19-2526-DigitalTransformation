<?php
/**
 * updatereponse.php
 * POST rep_id + contenu → update response content
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../controller/ReponseController.php';

try {
    $ctrl    = new ReponseController();
    $rep_id  = (int)($_POST['rep_id']  ?? 0);
    $contenu = trim($_POST['contenu'] ?? '');

    if (!$rep_id)  throw new Exception('ID réponse manquant.');
    if (!$contenu) throw new Exception('Le contenu de la réponse est requis.');

    $ctrl->updateReponse($rep_id, $contenu);

    echo json_encode(['success' => true, 'message' => 'Réponse modifiée avec succès.']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
