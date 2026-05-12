<?php
session_start();
require_once __DIR__ . '/../../connexion.php';
require_once __DIR__ . '/../../helpers/RoleHelper.php';
header('Content-Type: application/json');

RoleHelper::requireRole(['superadmin', 'admin', 'admin_agence']);

$idSinistre = (int)($_POST['id_sinistre'] ?? 0);
$idAgent    = (int)($_POST['id_agent']    ?? 0);

if (!$idSinistre || !$idAgent) {
    echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
    exit;
}

$db = config::getConnexion();
$stmt = $db->prepare("UPDATE sinistre SET id_agent_assigne = :agent WHERE id_sinistre = :sin");
$success = $stmt->execute([':agent' => $idAgent, ':sin' => $idSinistre]);

echo json_encode(['success' => $success, 'message' => $success ? 'Agent assigné avec succès.' : 'Erreur lors de l\'assignation.']);
