<?php
session_start();
header('Content-Type: application/json');
require_once '../../connexion.php';
$email = trim($_GET['email'] ?? '');
$db = config::getConnexion();
$stmt = $db->prepare("SELECT id_user FROM user WHERE email = ?");
$stmt->execute([$email]);
echo json_encode(['available' => !$stmt->fetch()]);
