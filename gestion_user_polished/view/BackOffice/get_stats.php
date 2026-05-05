<?php
session_start();

if (!isset($_SESSION['role'])) {
    http_response_code(401);
    echo json_encode(["success"=>false,"message"=>"Non connecté"]); exit;
}
$role = strtolower($_SESSION['role']);
if (!in_array($role, ['superadmin','admin','agent'])) {
    http_response_code(403);
    echo json_encode(["success"=>false,"message"=>"Accès refusé (" . $role . ")"]); exit;
}

header('Content-Type: application/json');
require_once __DIR__ . '/../../connexion.php';
require_once __DIR__ . '/../../controller/Client_Con.php';

try {
    $controller = new UserController();
    $stats = $controller->getStats();
    
    // Nouvelles métriques temps réel
    $db = config::getConnexion();
    $stats['online_now'] = (int)$db->query("SELECT COUNT(*) FROM user WHERE last_seen > DATE_SUB(NOW(), INTERVAL 5 MINUTE)")->fetchColumn();
    $stats['new_today']  = (int)$db->query("SELECT COUNT(*) FROM user WHERE DATE(date_creation) = CURDATE()")->fetchColumn();
    
    // SOS 24h (si la table existe)
    try {
        $stats['sos_today'] = (int)$db->query("SELECT COUNT(*) FROM sos_alerts WHERE created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)")->fetchColumn();
    } catch(Exception $e) {
        $stats['sos_today'] = 0;
    }
    
    echo json_encode(array_merge(['success' => true], $stats));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
