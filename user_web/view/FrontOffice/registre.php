<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controller/Client_Con.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // ✅ FIX: constructeur User corrigé (last_login ajouté, 13 params au lieu de 12 cassé)
    $user = new User(
        $_POST['nom'],
        $_POST['prenom'],
        $_POST['email'],
        $_POST['password'],
        $_POST['telephone']      ?? null,
        $_POST['adresse']        ?? null,
        !empty($_POST['date_naissance']) ? new DateTime($_POST['date_naissance']) : null,
        null,            // cin
        '',              // avatar
        'client',        // role
        'actif',         // statut
        null,            // last_login ✅ AJOUT
        new DateTime()   // date_creation
    );

    $controller = new UserController();
    $controller->addclient($user);

} else {
    header("Location: register.html");
    exit;
}
