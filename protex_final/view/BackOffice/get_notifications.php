<?php
require_once __DIR__ . '/../../controller/SinistreController.php';

$controller = new SinistreController();
$recent = $controller->getRecentSinistres();
$unread = $controller->getUnreadCount();

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
    'success' => true,
    'notifications' => $recent,
    'unread_count' => $unread
]);
?>
