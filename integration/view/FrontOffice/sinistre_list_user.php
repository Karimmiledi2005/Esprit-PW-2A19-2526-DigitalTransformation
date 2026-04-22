<?php
require_once __DIR__ . '/../../controller/SinistreController.php';

$uid = (int)($_GET['id_user'] ?? $_POST['id_user'] ?? 1); // default for dev

$controller = new SinistreController();
$sinistres = $controller->getByUser($uid);

$data = [];
foreach ($sinistres as $s) {
    $data[] = [
        'id_sinistre' => $s->getIdSinistre(),
        'type' => $s->getType(),
        'description' => $s->getDescription(),
        'date_declaration' => $s->getDateDeclaration(),
        'statut' => $s->getStatut(),
        'photo_url' => $s->getPhotoUrl(),
        'id_contrat' => $s->getIdContrat(),
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success' => true, 'data' => $data]);
?>