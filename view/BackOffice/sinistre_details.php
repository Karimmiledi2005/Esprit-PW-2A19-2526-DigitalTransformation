<?php
require_once __DIR__ . '/../../controller/SinistreController.php';

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'ID sinistre manquant.']);
    exit;
}

$controller = new SinistreController();
$sinistre = $controller->getById($id);

header('Content-Type: application/json; charset=utf-8');
if ($sinistre) {
    echo json_encode([
        'success' => true,
        'data' => [
            'id_sinistre' => $sinistre->getIdSinistre(),
            'type' => $sinistre->getType(),
            'description' => $sinistre->getDescription(),
            'photo_url' => $sinistre->getPhotoUrl(),
            'date_declaration' => $sinistre->getDateDeclaration(),
            'statut' => $sinistre->getStatut(),
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Sinistre non trouvé.']);
}
?>
