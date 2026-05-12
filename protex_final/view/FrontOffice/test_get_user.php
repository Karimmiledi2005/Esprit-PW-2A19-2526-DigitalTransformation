<?php
session_start();
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../controller/Client_Con.php';

$id = $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 21; 

echo "Testing for ID: $id\n";

try {
    $db = config::getConnexion();
    
    // Check user table
    $stmt = $db->prepare("SELECT * FROM user WHERE id_user = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    echo "User in 'user' table: " . ($user ? "Yes (" . $user['nom'] . " " . $user['prenom'] . ")" : "No") . "\n";
    
    // Check client table
    $stmt = $db->prepare("SELECT * FROM client WHERE id_user = ?");
    $stmt->execute([$id]);
    $client = $stmt->fetch();
    echo "User in 'client' table: " . ($client ? "Yes" : "No") . "\n";

    $controller = new UserController();
    $profile = $controller->getClientProfile($id);
    echo "getClientProfile result: " . ($profile ? "Success" : "Failed (Null)") . "\n";
    if ($profile) {
        print_r($profile);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

