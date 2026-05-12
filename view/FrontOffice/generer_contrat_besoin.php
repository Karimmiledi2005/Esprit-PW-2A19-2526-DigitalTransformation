<?php
header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Méthode non autorisée.'
        ]);
        exit;
    }

    $pythonScript = realpath(__DIR__ . '/../../ai_contract/generate_need_contract.py');

    if (!$pythonScript || !file_exists($pythonScript)) {
        throw new Exception("Script Python introuvable : ai_contract/generate_need_contract.py");
    }

    $payload = [
        'categorie' => trim($_POST['categorie'] ?? ''),
        'budget' => trim($_POST['budget'] ?? ''),
        'priorite' => trim($_POST['priorite'] ?? ''),
        'risque' => trim($_POST['risque'] ?? ''),
        'franchise' => trim($_POST['franchise'] ?? ''),
        'duree' => trim($_POST['duree'] ?? ''),
        'besoin' => trim($_POST['besoin'] ?? ''),

        // Champs spécifiques éventuels
        'vehicule_usage' => trim($_POST['vehicule_usage'] ?? ''),
        'stationnement' => trim($_POST['stationnement'] ?? ''),
        'conducteurs' => trim($_POST['conducteurs'] ?? ''),

        'type_logement' => trim($_POST['type_logement'] ?? ''),
        'statut_occupation' => trim($_POST['statut_occupation'] ?? ''),
        'valeur_biens' => trim($_POST['valeur_biens'] ?? ''),

        'frequence_soins' => trim($_POST['frequence_soins'] ?? ''),
        'couverture_dentaire' => trim($_POST['couverture_dentaire'] ?? ''),
        'couverture_optique' => trim($_POST['couverture_optique'] ?? ''),

        'type_protection' => trim($_POST['type_protection'] ?? ''),
        'niveau_couverture' => trim($_POST['niveau_couverture'] ?? ''),
        'couvrir_famille' => trim($_POST['couvrir_famille'] ?? '')
    ];

    if ($payload['categorie'] === '') {
        echo json_encode([
            'success' => false,
            'message' => 'Veuillez choisir une catégorie.'
        ]);
        exit;
    }

    $jsonInput = json_encode($payload, JSON_UNESCAPED_UNICODE);

    $cmd = 'python ' . escapeshellarg($pythonScript);

    $descriptors = [
        0 => ['pipe', 'r'],
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w']
    ];

    $process = proc_open($cmd, $descriptors, $pipes);

    if (!is_resource($process)) {
        throw new Exception("Impossible d'exécuter Python.");
    }

    fwrite($pipes[0], $jsonInput);
    fclose($pipes[0]);

    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);

    $error = stream_get_contents($pipes[2]);
    fclose($pipes[2]);

    $exitCode = proc_close($process);

    if ($exitCode !== 0) {
        throw new Exception("Erreur Python : " . trim($error));
    }

    $result = json_decode($output, true);

    if (!is_array($result)) {
        throw new Exception("Réponse Python invalide : " . $output);
    }

    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
    exit;
}