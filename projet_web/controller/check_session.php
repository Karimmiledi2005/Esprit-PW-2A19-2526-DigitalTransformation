<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['user_id'])) {
    echo json_encode([
        "logged" => true,
        "nom" => $_SESSION['nom']
    ]);
} else {
    echo json_encode([
        "logged" => false
    ]);
}
?>