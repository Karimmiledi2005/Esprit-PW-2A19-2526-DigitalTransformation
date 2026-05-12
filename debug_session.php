<?php
require_once 'bootstrap.php';
header('Content-Type: text/plain');

echo "--- DIAGNOSTIC SESSION ---\n";
echo "Session ID: " . session_id() . "\n";
echo "ID User (session): " . ($_SESSION['user_id'] ?? 'NON DÉFINI') . "\n";
echo "Role (session): " . ($_SESSION['role'] ?? 'NON DÉFINI') . "\n";
echo "Agence ID (session): " . ($_SESSION['id_agence'] ?? 'NON DÉFINI') . "\n";

if (isset($_SESSION['user_id'])) {
    require_once 'connexion.php';
    $db = config::getConnexion();
    $stmt = $db->prepare("SELECT id_user, nom, prenom, email, role FROM user WHERE id_user = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "\n--- DONNÉES BASE DE DONNÉES POUR CET ID ---\n";
    if ($user) {
        print_r($user);
    } else {
        echo "Aucun utilisateur trouvé en base avec cet ID !\n";
    }
} else {
    echo "\nVous n'êtes pas connecté.\n";
}
