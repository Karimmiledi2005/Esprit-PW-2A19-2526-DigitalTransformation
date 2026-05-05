<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        "logged"       => true,
        "nom"          => $_SESSION['nom'],
        "role"         => $_SESSION['role'],
        "id_agence"    => $_SESSION['id_agence'] ?? null,
    ]);
} else {
    echo json_encode(["logged"=>false]);
}
