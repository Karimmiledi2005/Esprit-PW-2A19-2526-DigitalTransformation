<?php
require 'connexion.php';
$db = config::getConnexion();
echo "--- USERS ---\n";
foreach($db->query('SELECT id_user, nom, prenom, role FROM user WHERE id_user IN (101,102,103,104)') as $r) print_r($r);
echo "--- ADMIN ---\n";
foreach($db->query('SELECT * FROM admin') as $r) print_r($r);
echo "--- AGENT ---\n";
foreach($db->query('SELECT * FROM agent') as $r) print_r($r);
