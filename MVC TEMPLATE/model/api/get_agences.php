<?php
require_once __DIR__ . '/db.php';

try {
    $stmt = $pdo->query("
        SELECT 
            id_agence,
            nom_agence,
            pays,
            tel,
            email,
            avis_json
        FROM agence
        ORDER BY id_agence ASC
    ");

    $agences = $stmt->fetchAll();

    jsonResponse([
        'success' => true,
        'agences' => $agences
    ]);
} catch (PDOException $e) {
    jsonResponse([
        'success' => false,
        'message' => 'Erreur SQL : ' . $e->getMessage()
    ], 500);
}