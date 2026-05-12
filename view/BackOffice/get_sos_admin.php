<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin','superadmin'])) {
    echo json_encode(['success'=>false]); exit;
}
require_once '../../connexion.php';
require_once '../../controller/Client_Con.php';

$db = config::getConnexion();

// POST : action resolve
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);
    if (($body['action'] ?? '') === 'resolve') {
        $db->prepare("UPDATE sos_alerts SET statut = 'resolu' WHERE id = ?")->execute([(int)$body['id']]);
        echo json_encode(['success' => true]); exit;
    }
}

// GET : liste des alertes
$stmt = $db->query("SELECT sa.*, u.nom, u.prenom, u.avatar_url 
                    FROM sos_alerts sa 
                    JOIN user u ON sa.user_id = u.id_user 
                    ORDER BY sa.created_at DESC 
                    LIMIT 20");
echo json_encode(['success'=>true, 'alerts'=>$stmt->fetchAll(PDO::FETCH_ASSOC)]);
