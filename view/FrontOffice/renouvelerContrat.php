<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controller/ContratController.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

$idUser = (int) $_SESSION['user_id'];
$idContrat = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($idContrat <= 0) {
    header('Location: contrat.php?error=id_invalide');
    exit();
}

$controller = new ContratController();
$contrat = $controller->getById($idContrat);

if (!$contrat) {
    header('Location: contrat.php?error=contrat_introuvable');
    exit();
}

// Compatible avec l'ancienne colonne id_client et la nouvelle colonne id_user.
$ownerId = (int)($contrat['id_user'] ?? $contrat['id_client'] ?? 0);

if ($ownerId !== $idUser) {
    header('Location: contrat.php?error=acces_refuse');
    exit();
}

$newId = $controller->renewContrat($idContrat);

if (!$newId) {
    header('Location: contrat.php?error=renouvellement_impossible');
    exit();
}

header('Location: contratshow.php?id=' . (int)$newId . '&success=renewal');
exit();
