<?php
require_once __DIR__ . '/../../controller/ContratController.php';

if (!isset($_GET['id'])) {
    header('Location: contrats_back.php');
    exit();
}

$id = (int)$_GET['id'];
$contratC = new ContratController();

$contratData = $contratC->getById($id);

if (!$contratData) {
    header('Location: contrats_back.php');
    exit();
}

// Supprimer le contrat
$contratC->deleteContrat($id);

header('Location: contrats_back.php?success=delete');
exit();
