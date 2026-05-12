<?php
require_once __DIR__ . '/../../bootstrap.php';

require_once __DIR__ . '/../../helpers/RoleHelper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Methode non autorisee.']);
    exit;
}

// Sécurité : Seuls Admin et SuperAdmin peuvent créer un traitement
if (!RoleHelper::canValiderTraitement()) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Accès refusé : Seul un administrateur peut créer un traitement.']);
    exit;
}

$controller = new TraitementController();
$userId = RoleHelper::getUserId();
$result = $controller->create($_POST, $userId);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result);
?>
