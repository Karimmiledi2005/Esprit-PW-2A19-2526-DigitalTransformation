<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['otp_user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Session expirée']);
    exit;
}

require_once __DIR__ . '/../../controller/Client_Con.php';

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);
$code = trim($data['code'] ?? '');

if (empty($code)) {
    echo json_encode(['success' => false, 'message' => 'Code requis']);
    exit;
}

$controller = new UserController();
echo json_encode($controller->verifyOTP($code));