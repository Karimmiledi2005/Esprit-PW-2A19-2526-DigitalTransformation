<?php
$host = "localhost";
$dbname = "assurance"; 
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // 🔥 IMPORTANT (affiche les erreurs SQL)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Erreur connexion DB : " . $e->getMessage());
}
?>