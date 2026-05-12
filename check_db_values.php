<?php
require 'connexion.php';
$db = config::getConnexion();

echo "--- TABLE user (role) ---\n";
foreach($db->query("SELECT DISTINCT role FROM user") as $row) {
    echo "- " . $row['role'] . "\n";
}

echo "\n--- TABLE admin (niveau_acces) ---\n";
foreach($db->query("SELECT DISTINCT niveau_acces FROM admin") as $row) {
    echo "- " . $row['niveau_acces'] . "\n";
}
