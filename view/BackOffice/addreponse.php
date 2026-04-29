<?php
/**
 * addreponse.php
 * POST reclamation_id + contenu          → add response (marks reclamation closed)
 * POST action=rejeter + reclamation_id + motif → reject reclamation
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../controller/ReponseController.php';

try {
    $ctrl   = new ReponseController();
    $action = trim($_POST['action'] ?? '');

    // ── REJET ─────────────────────────────────────────────────────────────────
    if ($action === 'rejeter') {
        $reclamation_id = (int)($_POST['reclamation_id'] ?? 0);
        $motif          = trim($_POST['motif'] ?? '');

        if (!$reclamation_id) throw new Exception('ID réclamation manquant.');
        if (!$motif)          throw new Exception('Le motif du rejet est requis.');

        $ctrl->rejeterReclamation($reclamation_id, $motif);

        echo json_encode(['success' => true, 'message' => 'Réclamation rejetée avec succès.']);
        exit;
    }

    // ── AJOUTER UNE RÉPONSE ────────────────────────────────────────────────────
    $reclamation_id = (int)($_POST['reclamation_id'] ?? 0);
    $contenu        = trim($_POST['contenu'] ?? '');

    if (!$reclamation_id) throw new Exception('ID réclamation manquant.');
    if (!$contenu)        throw new Exception('Le contenu de la réponse est requis.');

    $reponse = new Reponse(
        null,
        date('Y-m-d'),
        $contenu,
        'envoyee',
        $reclamation_id
    );
    $ctrl->addReponse($reponse);

    echo json_encode(['success' => true, 'message' => 'Réponse envoyée avec succès.']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
