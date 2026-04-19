<?php
/**
 * deletereclamation.php
 * POST rec_id → supprime la réclamation et toutes ses réponses
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../controller/ReclamationController.php';

try {
    $ctrl   = new ReclamationController();
    $rec_id = (int)($_POST['rec_id'] ?? 0);

    if (!$rec_id) throw new Exception('ID réclamation manquant.');

    // Supprimer d'abord les réponses liées (contrainte FK)
    $db = config::getConnexion();
    $db->prepare("DELETE FROM reponse WHERE reclamation_id = ?")->execute([$rec_id]);

    // Supprimer la réclamation
    $ctrl->deleteReclamation($rec_id);

    echo json_encode(['success' => true, 'message' => 'Réclamation supprimée avec succès.']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
