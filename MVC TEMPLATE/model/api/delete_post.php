<?php
require_once __DIR__ . '/db.php';

$data = getJsonInput();
$idPoste = (int)($data['id_poste'] ?? 0);

if ($idPoste <= 0) {
    jsonResponse([
        'success' => false,
        'message' => 'Identifiant du poste invalide.'
    ], 422);
}

try {
    $stmt = $pdo->prepare("DELETE FROM poste WHERE id_poste = ?");
    $stmt->execute([$idPoste]);

    jsonResponse([
        'success' => true,
        'message' => 'Poste supprimé.'
    ]);
} catch (PDOException $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Erreur SQL : ' . $e->getMessage()
    ], 500);
}