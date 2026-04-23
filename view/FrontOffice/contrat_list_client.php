<?php
require_once __DIR__ . '/../../controller/ContratController.php';

$uid = (int)($_GET['id_user'] ?? $_POST['id_user'] ?? 1); // default for dev

$controller = new ContratController();
$contrats = $controller->getByClient($uid);

$data = [];
foreach ($contrats as $c) {
    $data[] = [
        'id_contrat' => $c->getIdContrat(),
        'numero_contrat' => $c->getNumeroContrat(),
        'type_contrat' => $c->getTypeContrat(),
        'date_debut_contrat' => $c->getDateDebutContrat(),
        'date_fin_contrat' => $c->getDateFinContrat(),
        'prime_contrat' => $c->getPrimeContrat(),
        'franchise_contrat' => $c->getFranchiseContrat(),
        'statut_contrat' => $c->getStatutContrat(),
    ];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['success' => true, 'data' => $data]);
?>