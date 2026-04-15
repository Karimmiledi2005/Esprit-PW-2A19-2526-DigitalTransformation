<?php
require_once __DIR__ . '/admin_guard.php'; // ✅ FIX: protection admin manquante
header('Content-Type: application/json');

require_once __DIR__ . '/../../connexion.php';
require_once __DIR__ . '/../../controller/Client_Con.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Méthode non autorisée"]);
    exit;
}

try {
    $controller = new UserController();

    $nom           = $_POST['nom']        ?? '';
    $prenom        = $_POST['prenom']     ?? '';
    $email         = $_POST['email']      ?? '';
    $telephone     = $_POST['telephone']  ?? null;
    $cin           = $_POST['cin']        ?? null;
    $role          = strtolower($_POST['role']   ?? 'client');
    $statut        = strtolower($_POST['statut'] ?? 'actif');
    $password      = $_POST['password']   ?? '';

    $niveau_acces  = isset($_POST['niveau_acces'])  ? (int)$_POST['niveau_acces'] : null;
    $agence        = $_POST['agence']       ?? null;
    $salaire       = isset($_POST['salaire']) && $_POST['salaire'] !== '' ? (float)$_POST['salaire'] : null;
    $numero_client = $_POST['numero_client'] ?? null;

    $controller->addUserAdmin(
        $nom, $prenom, $email, $password,
        $telephone, $cin, $role, $statut,
        $niveau_acces, $agence, $salaire, $numero_client
    );

    echo json_encode(["success" => true, "message" => "Utilisateur ajouté avec succès"]);

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
