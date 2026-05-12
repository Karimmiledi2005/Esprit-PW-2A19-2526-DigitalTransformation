<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';

try {
    $db = config::getConnexion();
    $stmt = $db->query("SELECT id_agence, nom_agence FROM agence ORDER BY nom_agence ASC");
    $agences = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'data' => $agences]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}

