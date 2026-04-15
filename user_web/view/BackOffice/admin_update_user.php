<?php
require_once __DIR__ . '/admin_guard.php'; // ✅ FIX: protection admin
header('Content-Type: application/json');

require_once __DIR__ . '/../../connexion.php';
require_once __DIR__ . '/../../controller/Client_Con.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Méthode non autorisée"]);
    exit;
}

$id_user = isset($_POST['id_user']) ? (int)$_POST['id_user'] : 0;

if (!$id_user) {
    echo json_encode(["success" => false, "message" => "ID manquant"]);
    exit;
}

try {
    $controller = new UserController();
    $role   = strtolower($_POST['role']   ?? 'client');
    $statut = strtolower($_POST['statut'] ?? 'actif');

    $controller->updateUserAdmin(
        $id_user,
        $_POST['nom']       ?? '',
        $_POST['prenom']    ?? '',
        $_POST['email']     ?? '',
        $_POST['telephone'] ?? null,
        $_POST['cin']       ?? null,
        $role,
        $statut,
        isset($_POST['niveau_acces'])  ? (int)$_POST['niveau_acces']      : null,
        $_POST['agence']        ?? null,
        isset($_POST['salaire']) && $_POST['salaire'] !== '' ? (float)$_POST['salaire'] : null,
        $_POST['numero_client'] ?? null
    );

    echo json_encode(["success" => true, "message" => "Utilisateur mis à jour"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
