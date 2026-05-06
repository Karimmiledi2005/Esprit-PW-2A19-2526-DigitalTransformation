<?php
session_start();
require_once __DIR__ . '/../../controller/ContratController.php';

if (!isset($_SESSION['id_user'])) {
    $_SESSION['id_user'] = 1; // utilisateur test temporaire
}

$idContrat = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$controller = new ContratController();
$contrat = $controller->getById($idContrat);

if (!$contrat) {
    header('Location: contrat.php?error=contrat_introuvable');
    exit();
}

if ((int)$contrat['id_client'] !== (int)$_SESSION['id_user']) {
    header('Location: contrat.php?error=acces_refuse');
    exit();
}

$newId = $controller->renewContrat($idContrat);

if (!$newId) {
    header('Location: contrat.php?error=renouvellement_impossible');
    exit();
}

header('Location: contrat.php?success=renewal&new_id=' . $newId);
exit();
