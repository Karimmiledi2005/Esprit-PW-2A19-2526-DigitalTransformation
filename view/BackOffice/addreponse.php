<?php
/**
 * addreponse.php — VERSION FINALE (architecture GaiaLumen)
 * =========================================================
 * Ce fichier est maintenant simple et délègue toute la logique
 * au ReponseController (addReponseAvecEmail / rejeterAvecEmail).
 *
 * La logique email est dans : services/EmailService.php
 * La config email est dans  : config_services.php
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../controller/ReponseController.php';

try {
    $ctrl   = new ReponseController();
    $action = trim($_POST['action'] ?? '');

    // ── REJET ─────────────────────────────────────────────────────────────────
    if ($action === 'rejeter') {
        $reclamation_id = (int)($_POST['reclamation_id'] ?? 0);
        $motif          = trim($_POST['motif'] ?? '');
        $emailClient    = trim($_POST['email_client'] ?? '');

        if (!$reclamation_id) throw new Exception('ID réclamation manquant.');
        if (!$motif)          throw new Exception('Le motif du rejet est requis.');

        // ← Une seule ligne : le controller gère tout (BD + email)
        $result = $ctrl->rejeterAvecEmail($reclamation_id, $motif, $emailClient);

        echo json_encode($result);
        exit;
    }

    // ── AJOUTER UNE RÉPONSE ───────────────────────────────────────────────────
    $reclamation_id = (int)($_POST['reclamation_id'] ?? 0);
    $contenu        = trim($_POST['contenu'] ?? '');
    $emailClient    = trim($_POST['email_client'] ?? '');

    if (!$reclamation_id) throw new Exception('ID réclamation manquant.');
    if (!$contenu)        throw new Exception('Le contenu de la réponse est requis.');

    // ← Une seule ligne : le controller gère tout (BD + email)
    $result = $ctrl->addReponseAvecEmail($reclamation_id, $contenu, $emailClient);

    echo json_encode($result);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
