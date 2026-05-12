<?php
require_once __DIR__ . '/db.php';

$data = getJsonInput();
$idAgence = (int)($data['id_agence'] ?? 0);

if ($idAgence <= 0) {
    jsonResponse([
        'success' => false,
        'message' => 'Identifiant de l\'agence invalide.'
    ], 422);
}

try {
    $stmt = $pdo->prepare("DELETE FROM agence WHERE id_agence = ?");
    $stmt->execute([$idAgence]);

    jsonResponse([
        'success' => true,
        'message' => 'Agence supprimée.'
    ]);
} catch (PDOException $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Erreur SQL : ' . $e->getMessage()
    ], 500);
}