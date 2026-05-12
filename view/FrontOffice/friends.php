<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Non connecté"]);
    exit;
}

require_once __DIR__ . '/../../controller/Client_Con.php';
$my_id = $_SESSION['user_id'];
$controller = new UserController();

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);
if (empty($data)) $data = $_GET;

$action = $data['action'] ?? '';

try {
    if ($action === 'add' || $action === 'accept') {
        $friend_id = (int)($data['friend_id'] ?? 0);
        $result = $controller->handleFriendAction($my_id, $friend_id, $action);
        echo json_encode($result);

    } elseif ($action === 'remove' || $action === 'reject') {
        $friend_id = (int)($data['friend_id'] ?? 0);
        $result = $controller->handleFriendAction($my_id, $friend_id, 'remove');
        echo json_encode($result);

    } elseif ($action === 'list') {
        $result = $controller->getSocialData($my_id);
        echo json_encode(array_merge(["success" => true], $result));

    } else {
        echo json_encode(["success" => false, "message" => "Action invalide"]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
