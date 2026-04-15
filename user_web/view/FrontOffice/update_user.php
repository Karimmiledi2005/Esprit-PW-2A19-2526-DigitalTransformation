<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Non connecté"]);
    exit;
}

require_once __DIR__ . '/../../controller/Client_Con.php';

$raw  = file_get_contents("php://input");
$data = json_decode($raw, true);

if (empty($data)) {
    $data = $_POST;
}

// Validation des champs obligatoires
if (empty($data['nom']) || empty($data['prenom']) || empty($data['email'])) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "message" => "Champs obligatoires manquants",
        "debug"   => [
            "nom"    => $data['nom']    ?? '(vide)',
            "prenom" => $data['prenom'] ?? '(vide)',
            "email"  => $data['email']  ?? '(vide)',
        ]
    ]);
    exit;
}

if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Email invalide"]);
    exit;
}

try {
    $controller = new UserController();
    $controller->updateClient(
        $_SESSION['user_id'],
        $data['nom'],
        $data['prenom'],
        $data['email'],
        $data['telephone']     ?? null,
        $data['adresse']       ?? null,
        !empty($data['date_naissance']) ? $data['date_naissance'] : null
    );

    echo json_encode(["success" => true, "message" => "Profil mis à jour avec succès"]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Erreur : " . $e->getMessage()]);
}