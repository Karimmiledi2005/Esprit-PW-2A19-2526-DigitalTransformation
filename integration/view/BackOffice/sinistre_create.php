<?php
require_once __DIR__ . '/../../controller/SinistreController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Methode non autorisee.']);
    exit;
}

$controller = new SinistreController();
$uid = (int)($_POST['id_user'] ?? 1); // default for dev
$result = $controller->create($_POST, $uid, $_FILES['photo'] ?? null);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($result);
?>