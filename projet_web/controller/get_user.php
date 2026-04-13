<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Non connecté"]);
    exit;
}

require_once __DIR__ . '/Client_Con.php';

try {
    $db = config::getConnexion();
    $sql = "SELECT nom, prenom, email, telephone, adresse, cin, date_naissance
            FROM user WHERE id_user = :id_user";
    $query = $db->prepare($sql);
    $query->execute(['id_user' => $_SESSION['user_id']]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Utilisateur introuvable"]);
        exit;
    }

    echo json_encode($user);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}
