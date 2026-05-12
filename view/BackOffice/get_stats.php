<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
    $days = isset($_GET['days']) ? (int)$_GET['days'] : null;
    $stats = $controller->getStats($days);
    
    // Nouvelles métriques temps réel
    $db = config::getConnexion();
    $agencyFilter = "";
    $params = [];
    if (($_SESSION['role']??'') === 'admin' || ($_SESSION['role']??'') === 'agent') {
        if (isset($_SESSION['id_agence'])) {
            $join = " LEFT JOIN agent ag ON user.id_user=ag.id_user LEFT JOIN admin a ON user.id_user=a.id_user LEFT JOIN client c ON user.id_user=c.id_user ";
            $agencyFilter = " AND (ag.id_agence=:ag OR a.id_agence=:ag2 OR c.id_agence=:ag3) ";
            $params = ['ag'=>(int)$_SESSION['id_agence'], 'ag2'=>(int)$_SESSION['id_agence'], 'ag3'=>(int)$_SESSION['id_agence']];
        }
    }

    $onlineStmt = $db->prepare("SELECT COUNT(*) FROM user " . ($agencyFilter ? " LEFT JOIN agent ag ON user.id_user=ag.id_user LEFT JOIN admin a ON user.id_user=a.id_user LEFT JOIN client c ON user.id_user=c.id_user " : "") . " WHERE last_seen > DATE_SUB(NOW(), INTERVAL 5 MINUTE)" . $agencyFilter);
    $onlineStmt->execute($params);
    $stats['online_now'] = (int)$onlineStmt->fetchColumn();

    $todayStmt = $db->prepare("SELECT COUNT(*) FROM user " . ($agencyFilter ? " LEFT JOIN agent ag ON user.id_user=ag.id_user LEFT JOIN admin a ON user.id_user=a.id_user LEFT JOIN client c ON user.id_user=c.id_user " : "") . " WHERE DATE(date_creation) = CURDATE()" . $agencyFilter);
    $todayStmt->execute($params);
    $stats['new_today']  = (int)$todayStmt->fetchColumn();
    
    // SOS 24h
    try {
        $sosSql = "SELECT COUNT(*) FROM sos_alerts s ";
        if ($agencyFilter) {
            $sosSql .= " JOIN user u ON s.id_user = u.id_user LEFT JOIN agent ag ON u.id_user=ag.id_user LEFT JOIN admin a ON u.id_user=a.id_user LEFT JOIN client c ON u.id_user=c.id_user WHERE s.created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR) " . $agencyFilter;
        } else {
            $sosSql .= " WHERE s.created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
        }
        $sosStmt = $db->prepare($sosSql);
        $sosStmt->execute($params);
        $stats['sos_today'] = (int)$sosStmt->fetchColumn();
    } catch(Exception $e) {
        $stats['sos_today'] = 0;
    }
    
    echo json_encode(array_merge(['success' => true], $stats));
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
