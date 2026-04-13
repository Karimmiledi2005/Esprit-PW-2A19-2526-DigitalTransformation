<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/Client_Con.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $user = new User(
        $_POST['nom'],
        $_POST['prenom'],
        $_POST['email'],
        $_POST['password'],
        $_POST['telephone'] ?? '',
        $_POST['adresse']   ?? '',
        !empty($_POST['date_naissance']) ? new DateTime($_POST['date_naissance']) : new DateTime(),
        '',
        '',
        'client',
        'actif',
        new DateTime(),
        new DateTime()
    );

    $controller = new UserController();
    $controller->addclient($user);

} else {
    header("Location: ../view/FrontOffice/register.html");
    exit;
}