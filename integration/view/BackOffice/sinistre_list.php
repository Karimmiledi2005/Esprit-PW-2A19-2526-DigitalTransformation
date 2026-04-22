<?php
require_once __DIR__ . '/../../controller/SinistreController.php';

$controller = new SinistreController();
$sinistres = $controller->getAll();

$data = [];
foreach ($sinistres as $s) {
    $data[] = [
        'id_sinistre'      => $s->getIdSinistre(),
        'type'             => $s->getType(),
        'description'      => $s->getDescription(),
        'date_declaration' => $s->getDateDeclaration(),
        'statut'           => $s->getStatut(),
        'photo_url'        => $s->getPhotoUrl(),
        'id_contrat'       => $s->getIdContrat(),
        'id_user'          => $s->getIdUser(),
        'client_nom'       => $s->getClientNom(),
        'numero_contrat'   => $s->getNumeroContrat(),
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success' => true, 'data' => $data]);
?>
