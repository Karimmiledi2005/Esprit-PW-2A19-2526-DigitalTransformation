<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'] ?? '', ['admin','superadmin','agent'])) {
    echo json_encode(['success'=>false]); exit;
}
require_once '../../connexion.php';
require_once '../../controller/Client_Con.php';

$ctrl = new UserController();
$id = (int)($_GET['id'] ?? 0);
if (!$id) { echo json_encode(['success'=>false]); exit; }

$user = $ctrl->getUserById($id);
if (!$user) { echo json_encode(['success'=>false]); exit; }

// Agency isolation: admin_agence can only see users in their agency
if ($_SESSION['role'] === 'admin') {
    $sessionAgence = $_SESSION['id_agence'] ?? null;
    $userAgence = $user['client_id_agence'] ?? $user['id_agence'] ?? $user['admin_id_agence'] ?? null;
    if (!$sessionAgence || ($userAgence && (int)$userAgence !== (int)$sessionAgence)) {
        echo json_encode(['success'=>false,'message'=>'Accès refusé: utilisateur d\'une autre agence']); exit;
    }
}

// Agent can only see client details from same agency
if ($_SESSION['role'] === 'agent') {
    $sessionAgence = $_SESSION['id_agence'] ?? null;
    $userAgence = $user['client_id_agence'] ?? $user['id_agence'] ?? $user['admin_id_agence'] ?? null;
    if ($user['role'] !== 'client' || !$sessionAgence || ($userAgence && (int)$userAgence !== (int)$sessionAgence)) {
        echo json_encode(['success'=>false,'message'=>'Accès refusé: client hors de votre agence']); exit;
    }
}

$db = config::getConnexion();

$friends = $db->prepare("SELECT COUNT(*) FROM friendships WHERE (sender_id=? OR receiver_id=?) AND status='accepted'");
$friends->execute([$id,$id]);

$sos = $db->prepare("SELECT COUNT(*) FROM sos_alerts WHERE user_id=?");
$sos->execute([$id]);

$logins = $db->prepare("SELECT ip, statut, created_at FROM login_attempts WHERE email=? ORDER BY created_at DESC LIMIT 5");
$logins->execute([$user['email']]);

echo json_encode([
    'success'    => true,
    'user'       => $user,
    'nb_friends' => (int)$friends->fetchColumn(),
    'nb_sos'     => (int)$sos->fetchColumn(),
    'logins'     => $logins->fetchAll(PDO::FETCH_ASSOC)
]);
