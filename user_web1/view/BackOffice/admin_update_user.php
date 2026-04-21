<?php
// CORRECTION : Added session_start() and role verification for admin access
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Accès refusé - administrateur requis"]);
    exit;
}

header('Content-Type: application/json');

require_once __DIR__ . '/../../connexion.php';
require_once __DIR__ . '/../../controller/Client_Con.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Méthode non autorisée"]);
    exit;
}

$id_user = isset($_POST['id_user']) ? (int)$_POST['id_user'] : 0;
if (!$id_user) {
    echo json_encode(["success" => false, "message" => "ID utilisateur manquant"]);
    exit;
}

$nom       = trim($_POST['nom']      ?? '');
$prenom    = trim($_POST['prenom']   ?? '');
$email     = trim($_POST['email']    ?? '');
$telephone = trim($_POST['telephone']?? '');
$cin       = trim($_POST['cin']      ?? '');
$role      = strtolower(trim($_POST['role']   ?? 'client'));
$statut    = strtolower(trim($_POST['statut'] ?? 'actif'));

// Validation PHP - rejeter les chiffres dans le nom et prénom
if (empty($nom) || empty($prenom)) {
    echo json_encode(["success" => false, "message" => "Le nom et prénom sont obligatoires"]);
    exit;
}
if (strlen($nom) < 2 || strlen($prenom) < 2) {
    echo json_encode(["success" => false, "message" => "Le nom et prénom doivent contenir au moins 2 lettres"]);
    exit;
}
if (preg_match('/[0-9]/', $nom) || preg_match('/[0-9]/', $prenom)) {
    echo json_encode(["success" => false, "message" => "Le nom et prénom ne doivent pas contenir de chiffres"]);
    exit;
}
if (!preg_match('/^[a-zA-ZÀ-ÿ\s\'\-]+$/', $nom) || !preg_match('/^[a-zA-ZÀ-ÿ\s\'\-]+$/', $prenom)) {
    echo json_encode(["success" => false, "message" => "Le nom et prénom ne doivent contenir que des lettres"]);
    exit;
}

try {
    $controller     = new UserController();
    $niveau_acces = isset($_POST['niveau_acces']) ? (int)$_POST['niveau_acces'] : null;
    $agence       = trim($_POST['agence']       ?? '');
    $salaire      = isset($_POST['salaire']) && $_POST['salaire'] !== '' ? (float)$_POST['salaire'] : null;
    $num_client   = trim($_POST['numero_client'] ?? '');

    $controller->updateUserAdmin(
        $id_user, $nom, $prenom, $email,
        $telephone ?: null, $cin ?: null, $role, $statut,
        $niveau_acces, $agence ?: null, $salaire, $num_client ?: null
    );

    echo json_encode(["success" => true, "message" => "Utilisateur mis à jour"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
